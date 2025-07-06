# The #[Accept] Pattern: Ontological Delegation for Undetermined States

> "The wisest person is not the one who knows everything, but the one who knows what they don't know and seeks appropriate expertise."

## Introduction

This document introduces the `#[Accept]` pattern, a natural extension of Ontological Programming that addresses a fundamental limitation in current object-oriented design: the forced binary decision problem. Born from a conversation with CodeRabbit AI, this pattern recognizes that responsible entities must sometimes acknowledge their limitations and delegate decisions to appropriate expertise.

## Background: The Problem of Forced Decisions

In Ontological Programming, objects are responsible for determining their own nature. However, real-world scenarios often present situations where an individual entity genuinely cannot make a determination due to:

- **Insufficient expertise**: The decision requires specialized knowledge
- **Limited authority**: The entity lacks the necessary permissions
- **Lack of necessary information**: Critical data is unavailable
- **Complexity beyond individual capacity**: The problem exceeds individual capabilities

This mirrors human social responsibility: we are accountable for decisions within our capability, but we delegate to experts when we reach our limits.

## The Philosophical Foundation

### Individual Responsibility vs. Social Delegation

In society, responsible individuals follow this pattern:

- **Medical decisions**: "I need to consult a specialist"
- **Legal matters**: "This requires legal expertise"
- **Technical issues**: "Let me ask someone who knows this field"
- **Organizational decisions**: "This is beyond my authority"

This represents ethical self-awareness and responsible delegation.

### The Current Technical Problem

```php
final class ValidationAttempt {
    public readonly Success|Failure $being;
    
    public function __construct(string $data, Validator $validator) {
        // What if the validator genuinely cannot determine validity?
        // What if the data requires domain expertise we don't have?
        // Currently: forced to choose Success or Failure
        // Reality: should be able to say "I don't know"
    }
}
```

> **Note**: This example shows traditional Ontological Programming before the #[Accept] pattern was introduced. The `readonly` modifier represents the standard approach where objects must make final decisions in their constructors.

**Current limitation**: Objects must always make binary decisions, even when inappropriate.

## The #[Accept] Solution

### Declaration Syntax

```php
#[Accept(DecisionInterface::class)]
final class ValidationAttempt {
    public Success|Failure|ThirdPartyDecision|Undetermined $being;

    public function __construct(string $data, Validator $validator) {
        if ($validator->canDetermine($data)) {
            // Within my capability - take responsibility
            $this->being = $validator->isValid($data) 
                ? new Success($data)
                : new Failure("Invalid format");
        } else {
            // Beyond my capability - honest delegation
            $this->being = new Undetermined(
                reason: "Requires domain expertise",
                context: $data
            );
            // Framework will use DecisionInterface to find appropriate expert
        }
    }
}
```

### How #[Accept] Works

#### 1. Individual Assessment Phase (Constructor)

```php
public function __construct(...) {
    // Honest self-assessment
    if ($this->canDecideMyself($context)) {
        $this->being = $this->makeDecision($context);
    } else {
        $this->being = new Undetermined($this->explainLimitation($context));
    }
}
```

#### 2. Social Resolution Phase (Framework)

```php
// Framework detects #[Accept] attribute
if ($object->being instanceof Undetermined) {
    $expertDecision = $di->get(DecisionInterface::class);
    $object->being = $expertDecision->resolve($object->being);
}
```

#### 3. Final State

The object's `$being` property contains either:
- **Success/Failure**: Self-determined decisions
- **ThirdPartyDecision**: Expert-delegated decisions
- **Undetermined**: If no expert can be found (rare)

## Core Components

### The Undetermined State

```php
final class Undetermined
{
    public function __construct(
        public readonly string $reason,
        public readonly mixed $context,
        public readonly array $requirements = []
    ) {}
    
    public function requiresExpertise(string $domain): self
    {
        return new self(
            $this->reason,
            $this->context,
            [...$this->requirements, $domain]
        );
    }
}
```

### The Decision Interface

```php
interface DecisionInterface
{
    public function canHandle(Undetermined $case): bool;
    public function resolve(Undetermined $case): Success|Failure|ThirdPartyDecision;
    public function getExpertiseDomain(): string;
}
```

### Expert Registry

```php
class ExpertRegistry
{
    private array $experts = [];
    
    public function register(string $domain, DecisionInterface $expert): void
    {
        $this->experts[$domain] = $expert;
    }
    
    public function findExpert(Undetermined $case): ?DecisionInterface
    {
        foreach ($this->experts as $domain => $expert) {
            if ($expert->canHandle($case)) {
                return $expert;
            }
        }
        return null;
    }
}
```

## Real-World Examples

### Medical Diagnosis System

```php
#[Accept(MedicalExpertInterface::class)]
final class SymptomAnalysis
{
    public function __construct(
        string $symptoms, 
        BasicChecker $checker,
        PatientHistory $history
    ) {
        if ($checker->isObviousCase($symptoms)) {
            $this->being = new BasicDiagnosis($symptoms);
        } elseif ($history->hasComplications()) {
            $this->being = new Undetermined("Complex medical history requires specialist review");
        } else {
            $this->being = new Undetermined("Symptoms require medical professional evaluation");
        }
    }
    
    public BasicDiagnosis|Undetermined $being;
}

class PhysicianExpert implements MedicalExpertInterface
{
    public function resolve(Undetermined $case): Success|Failure|ThirdPartyDecision
    {
        // Apply medical expertise
        $diagnosis = $this->analyzeSymptoms($case->context);
        
        return new ThirdPartyDecision(
            decision: $diagnosis,
            authority: "Licensed Physician",
            confidence: $this->getConfidenceLevel($diagnosis)
        );
    }
}
```

### Legal Document Review

```php
#[Accept(LegalExpertInterface::class)]
final class ContractValidation
{
    public function __construct(
        string $contract, 
        BasicValidator $validator,
        ContractComplexityAnalyzer $analyzer
    ) {
        $complexity = $analyzer->analyze($contract);
        
        if ($complexity->isStandardTemplate()) {
            $this->being = new ValidContract($contract);
        } elseif ($complexity->hasUnusualClauses()) {
            $this->being = new Undetermined("Non-standard clauses require legal review");
        } else {
            $this->being = new Undetermined("Complex legal language requires attorney review");
        }
    }
    
    public ValidContract|InvalidContract|Undetermined $being;
}
```

### Financial Risk Assessment

```php
#[Accept(RiskExpertInterface::class)]
final class InvestmentEvaluation
{
    public function __construct(
        InvestmentProposal $proposal,
        BasicRiskCalculator $calculator,
        MarketAnalyzer $market
    ) {
        $basicRisk = $calculator->calculate($proposal);
        $marketConditions = $market->getCurrentConditions();
        
        if ($basicRisk->isLowRisk() && $marketConditions->isStable()) {
            $this->being = new ApprovedInvestment($proposal);
        } elseif ($proposal->amount > 1000000) {
            $this->being = new Undetermined("High-value investments require senior approval");
        } else {
            $this->being = new Undetermined("Market volatility requires expert analysis");
        }
    }
    
    public ApprovedInvestment|RejectedInvestment|Undetermined $being;
}
```

## Implementation Requirements

### 1. Framework Support

The framework must:
- Detect `#[Accept]` attributes during object construction
- Resolve `Undetermined` states post-construction
- Maintain expert registry through dependency injection
- Handle circular delegation (Expert A delegates to Expert B)

### 2. Property Mutability

```php
// Properties must be mutable to allow post-construction resolution
public Success|Failure|ThirdPartyDecision|Undetermined $being;
// NOT readonly - framework needs to update after delegation
```

**Note**: This is the only exception to the "readonly properties" principle in Ontological Programming, and only for delegation purposes.

### 3. Expert Implementation

```php
class TechnicalExpert implements DecisionInterface
{
    public function canHandle(Undetermined $case): bool
    {
        return str_contains($case->reason, 'technical') ||
               str_contains($case->reason, 'engineering');
    }
    
    public function resolve(Undetermined $case): Success|Failure|ThirdPartyDecision
    {
        $analysis = $this->performTechnicalAnalysis($case->context);
        
        if ($analysis->isApproved()) {
            return new ThirdPartyDecision(
                decision: new TechnicalApproval($analysis),
                authority: "Senior Technical Lead",
                timestamp: new DateTimeImmutable()
            );
        }
        
        return new ThirdPartyDecision(
            decision: new TechnicalRejection($analysis->getIssues()),
            authority: "Senior Technical Lead",
            timestamp: new DateTimeImmutable()
        );
    }
}
```

## Benefits

### 1. Realistic Modeling

- Objects can honestly express limitations
- Mirrors real-world decision-making processes  
- Eliminates forced binary choices
- Acknowledges the limits of individual knowledge

### 2. Distributed Expertise

- Complex decisions routed to qualified experts
- Maintains individual responsibility within capabilities
- Leverages collective organizational knowledge
- Prevents unqualified decisions

### 3. Ethical Framework

- Promotes honest self-assessment
- Prevents decisions beyond qualification
- Embodies responsible delegation
- Reflects real-world accountability structures

### 4. System Flexibility

- New experts can be added without modifying existing objects
- Decision criteria can evolve over time
- Multiple experts can collaborate on complex cases
- Fallback mechanisms for unknown scenarios

## Advanced Patterns

### Multi-Expert Collaboration

```php
#[Accept([PrimaryExpert::class, SecondaryExpert::class])]
final class ComplexValidation
{
    public function __construct($data, BasicValidator $validator) {
        if ($validator->isSimple($data)) {
            $this->being = $validator->validate($data);
        } else {
            $this->being = new Undetermined(
                "Requires multi-disciplinary review",
                $data,
                requirements: ['primary-expertise', 'secondary-validation']
            );
        }
    }
}
```

### Escalation Chains

```php
class JuniorExpert implements DecisionInterface
{
    public function resolve(Undetermined $case): Success|Failure|ThirdPartyDecision
    {
        if ($this->isWithinMyCapability($case)) {
            return $this->makeDecision($case);
        }
        
        // Escalate to senior expert
        return new Undetermined(
            "Requires senior expert review",
            $case->context,
            requirements: ['senior-level-expertise']
        );
    }
}
```

### Time-Bounded Decisions

```php
final class TimeConstrainedValidation
{
    public function __construct($data, $validator, DateTimeInterface $deadline) {
        if ($deadline < new DateTimeImmutable('+1 hour')) {
            // Time pressure - make best effort decision
            $this->being = $validator->quickValidate($data);
        } else {
            // Time available - seek expert input
            $this->being = new Undetermined("Allow time for expert review");
        }
    }
}
```

## Testing Strategies

### Testing Self-Assessment

```php
public function testObjectRecognizesLimitations(): void
{
    $complexData = new ComplexDataRequiringExpertise();
    $basicValidator = new BasicValidator();
    
    $object = new ValidationAttempt($complexData, $basicValidator);
    
    $this->assertInstanceOf(Undetermined::class, $object->being);
    $this->assertStringContains('expertise', $object->being->reason);
}
```

### Testing Expert Resolution

```php
public function testExpertResolvesUndetermined(): void
{
    $undetermined = new Undetermined("Requires technical expertise", $complexData);
    $expert = new TechnicalExpert();
    
    $resolution = $expert->resolve($undetermined);
    
    $this->assertInstanceOf(ThirdPartyDecision::class, $resolution);
    $this->assertEquals("Senior Technical Lead", $resolution->authority);
}
```

### Testing Framework Integration

```php
public function testFrameworkDelegatesAutomatically(): void
{
    $di = new DependencyInjector();
    $di->bind(TechnicalExpertInterface::class, new TechnicalExpert());
    
    $object = new ValidationAttempt($complexData, $basicValidator);
    $framework = new AcceptFramework($di);
    
    $framework->resolveUndetermined($object);
    
    $this->assertInstanceOf(ThirdPartyDecision::class, $object->being);
}
```

## Philosophical Implications

### 1. Honest Self-Assessment

Objects (like people) must honestly assess their capabilities:
- "Am I qualified to make this decision?"
- "Do I have sufficient information?"
- "Is this within my authority?"

### 2. Responsible Delegation

When limits are reached, delegate to appropriate expertise:
- `new Undetermined("Medical decision - requires physician");`
- `new Undetermined("Legal matter - requires attorney");`
- `new Undetermined("Technical issue - requires specialist");`

### 3. Social Knowledge Integration

The DI container represents collective societal knowledge:
- Knows which experts handle which problems
- Maintains registry of qualified decision-makers
- Routes complex decisions to appropriate authorities

### 4. Ethical Programming

The pattern embodies ethical principles:
- **Humility**: Acknowledging limitations
- **Responsibility**: Making decisions within capability
- **Trust**: Relying on qualified expertise
- **Accountability**: Clear audit trails for decisions

## Relationship to Other Patterns

This complements existing Ontological Programming patterns:

- **#[Be] attributes**: Declaring what something is destined to be
- **Type-Driven Metamorphosis**: Objects discovering their nature
- **Semantic Variable Names**: Names carrying contracts
- **The Unchanged Name Principle**: Continuity through transformation

Together, these create a complete Ontological Programming ecosystem where individual responsibility meets social expertise.

## Implementation Timeline

This pattern should be implemented after:

1. **Type-Driven Metamorphosis documentation** (completed)
2. **Semantic Variable Names** (completed)
3. **#[To] → #[Be] evolution** (completed)

This ensures proper philosophical foundation before adding delegation complexity.

## Conclusion

The `#[Accept]` pattern represents a mature evolution in Ontological Programming, acknowledging that true wisdom lies not in knowing everything, but in knowing one's limitations and seeking appropriate expertise. By formalizing delegation as a first-class concept, we create systems that mirror the best aspects of human social responsibility and collective intelligence.

This pattern transforms the impossible choice between forced decisions and system failure into a third option: honest acknowledgment of limitations coupled with responsible delegation to qualified expertise.

---

## References

- Originated from conversation with CodeRabbit AI: [GitHub Issue #4](https://github.com/koriym/Ray.Framework/issues/4)
- [Ontological Programming: A New Paradigm](ontological-programming-paper.md)
- [The Metamorphosis Architecture Manifesto](metamorphosis-architecture-manifesto.md)
- [Type-Driven Metamorphosis patterns](metamorphosis-architecture-manifesto.md#type-driven-metamorphosis)

---

*"In programming, as in life, the highest intelligence is knowing when to seek the wisdom of others."*