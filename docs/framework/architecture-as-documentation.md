# Architecture as Documentation

> "The code is the documentation." — Martin Fowler  
> "The architecture **is** the documentation." — Be Framework

## Introduction

Martin Fowler introduced the concept of "Code as Documentation"—the idea that well-written code should be self-documenting. Be Framework takes this concept to its logical conclusion: **Architecture as Documentation**. In an ontological programming system, the architecture doesn't just document itself—it *is* the documentation.

## Beyond Code as Documentation

### Traditional Code as Documentation
```php
// Traditional: Code explains what it does
class UserValidator {
    /**
     * Validates user email format
     * 
     * @param  string $email Email to validate
     * @return bool          True if valid
     */
    public function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
```

### Architecture as Documentation  
```php
// Be Framework: Architecture explains what exists
#[Be([Success::class, Failure::class])]
final class ValidationAttempt {
    public readonly Success|Failure $being;
}
```

The architecture itself declares:
- **What can exist** (`Success`, `Failure`)
- **What relationships exist** (`#[Be]` attribute)  
- **What data flows** (`Success|Failure $being`)
- **What contracts govern** (union types)

## The `be-tree` Command: Architecture Visualization

Be Framework's ontological structure enables the automatic architecture documentation through the `be-tree` command:

### Basic Structure Visualization
```bash
be-tree src/UserRegistration/
# Outputs architectural structure visualization
UserRegistration/
├── RegistrationInput (#[Be] → ValidatedRegistration)
│   ├── 📥 email: string (validates/ValidEmail)
│   └── 📥 password: string (validates/StrongPassword)
├── ValidatedRegistration (#[Be] → Success|Conflict)
│   ├── 📤 being: NewUser|ConflictingUser
│   └── 🔄 Self-determines destiny via UserRepository
└── Outcomes/
    ├── UnverifiedUser (🦋 Success path)
    │   ├── userId: string
    │   └── verificationToken: string
    └── UserConflict (🦋 Conflict path)
        └── message: string
```

### Semantic Analysis Mode
```bash
$ be-tree --semantic src/
📋 Semantic Variable Registry
├── email (validates/ValidEmail.php)
│   ├── 📖 ALPS: RFC 5322 compliant email address
│   ├── 🔍 Used in: RegistrationInput, UserProfile, EmailValidation
│   └── ✅ Validation: Email format, domain check
├── password (validates/StrongPassword.php)  
│   ├── 📖 ALPS: Secure password meeting complexity requirements
│   └── ✅ Validation: Length ≥8, complexity rules
└── age (validates/NonNegativeAge.php)
    ├── 📖 ALPS: Person's age in years, non-negative integer
    └── ✅ Validation: >= 0, integer type
```

### Flow Visualization Mode
```bash
$ be-tree --flow UserRegistration
🌊 Metamorphosis Flow for UserRegistration

RegistrationInput
    ↓ #[Be]
ValidatedRegistration  
    ↓ #[Be] (Self-discovery)
┌─ Success → UnverifiedUser → VerificationEmailSent
└─ Conflict → UserConflict

💡 Decision Points:
- ValidatedRegistration.being: NewUser|ConflictingUser
  ├── NewUser → Success path (email available)  
  └── ConflictingUser → Conflict path (email exists)
```

## The Three Pillars of Architecture as Documentation

### 1. Structural Documentation (Automatic)
The `#[Be]` attributes create a complete map of data flow:

```php
#[Be(ProcessedData::class)]          // "I become ProcessedData"
#[Be([Success::class, Failure::class])] // "I can be Success or Failure"
```

**Result**: Complete architectural diagrams generated automatically from code.

### 2. Semantic Documentation (ALPS Integration)
```json
// alps/email.json - Single source of meaning
{
    "alps": {
        "descriptor": [{
            "id": "email",
            "type": "semantic",
            "doc": {"value": "Valid email address conforming to RFC 5322"}
        }]
    }
}
```

**Result**: Every variable name has defined, discoverable meaning.

### 3. Validation Documentation (Convention-based)
```
validates/
├── ValidEmail.php      → Defines what makes email valid
├── NonNegativeAge.php  → Defines age constraints
└── PositivePrice.php   → Defines price requirements
```

**Result**: All business rules are explicit, testable, and documented.

## Mermaid Diagram Generation

The `be-tree` command can generate Mermaid diagrams for complete visual documentation:

```bash
$ be-tree --mermaid src/UserRegistration/ > registration-flow.md
```

```mermaid
flowchart TD
    A[RegistrationInput] -->|#[Be]| B[ValidatedRegistration]
    B -->|#[Be]| C{being: NewUser|ConflictingUser}
    C -->|NewUser| D[UnverifiedUser]
    C -->|ConflictingUser| E[UserConflict]
    D -->|#[Be]| F[VerificationEmailSent]
    E -->|#[Be]| G[JsonResponse 409]
    F -->|#[Be]| H[JsonResponse 201]
    
    subgraph "Semantic Variables"
        I[email: ValidEmail.php]
        J[password: StrongPassword.php]
    end
    
    subgraph "ALPS Definitions"
        K[alps/email.json]
        L[alps/password.json]
    end
    
    I -.-> K
    J -.-> L
```

## Implementation Concept

```php
class BeTreeAnalyzer
{
    public function analyze(string $path): ArchitectureMap
    {
        $classes = $this->discoverClasses($path);
        $flows = $this->extractFlows($classes);        // #[Be] attributes
        $semantics = $this->loadALPS();               // alps/*.json files
        $validations = $this->discoverValidators();   // validates/*.php
        
        return new ArchitectureMap($classes, $flows, $semantics, $validations);
    }
    
    private function extractFlows(array $classes): array
    {
        $flows = [];
        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            
            // Extract #[Be] destinations and possibilities
            $beAttributes = $reflection->getAttributes(Be::class);
            
            // Analyze union types in $being property
            $beingProperty = $reflection->getProperty('being');
            $unionTypes = $this->parseUnionTypes($beingProperty);
            
            $flows[$class] = new FlowDefinition($beAttributes, $unionTypes);
        }
        return $flows;
    }
}
```

## The Revolutionary Impact

### Traditional Documentation Problems
- **Outdated**: Documentation lags behind code changes
- **Incomplete**: Partial coverage of system behavior  
- **Scattered**: Information spread across multiple sources
- **Manual**: Requires human effort to maintain

### Architecture as Documentation Solutions
- **Always Current**: Generated from live code structure
- **Complete**: Covers all flows, semantics, and validations
- **Centralized**: Single command reveals entire architecture
- **Automatic**: Updates with every code change

## Benefits

### For Developers
- **Instant Understanding**: New team members see complete architecture instantly
- **Design Validation**: Architecture problems visible immediately
- **Refactoring Safety**: Changes show impact across entire system

### For Product Managers
- **Business Flow Clarity**: See how data moves through business processes
- **Decision Points**: Understand where business rules apply
- **Feature Impact**: Visualize how changes affect existing flows

### For Architects
- **System Overview**: Complete architectural landscape in seconds
- **Dependency Analysis**: Clear view of coupling and cohesion
- **Pattern Compliance**: Verify adherence to architectural principles

## Comparison with Traditional Approaches

| Aspect | Traditional Docs | Code as Documentation | **Architecture as Documentation** |
|--------|------------------|----------------------|-----------------------------------|
| **Accuracy** | Often outdated | Current but incomplete | Always current and complete |
| **Scope** | Manual coverage | Function/class level | **System-wide architecture** |
| **Generation** | Manual effort | Automated comments | **Fully automated** |
| **Business Context** | Separate documents | Limited | **Integrated with semantics** |
| **Visual** | Static diagrams | Code only | **Dynamic architecture diagrams** |

## Future Possibilities

### IDE Integration
```typescript
// VS Code extension
be.framework.generateArchitecture({
    path: './src',
    format: 'mermaid',
    includeSemantics: true
});
```

### CI/CD Integration
```yaml
# GitHub Actions
- name: Generate Architecture Documentation
  run: be-tree --mermaid src/ > docs/architecture.md
  
- name: Validate Semantic Consistency  
  run: be-tree --validate-semantics src/
```

### Real-time Documentation
```php
// Live documentation server
$server = new ArchitectureDocServer();
$server->watch('./src')->generateOnChange();
// Documentation updates in real-time as code changes
```

## Conclusion

Architecture as Documentation represents the natural evolution of self-documenting systems. By embedding architectural intent directly into code structure through attributes, union types, and naming conventions, Be Framework creates a system where:

- **The architecture documents itself**
- **Documentation is always current**  
- **Visual diagrams generate automatically**
- **Business semantics are explicit**
- **System understanding is instant**

This is not just better documentation—it's a fundamental shift in how we think about system design. When architecture becomes documentation, and documentation becomes architecture, we achieve perfect alignment between intent and implementation.

**The future of software documentation is not writing about the architecture—it's making the architecture speak for itself.**

---

*Try it yourself: Install Be Framework and run `be-tree src/` to see your architecture come alive.*
