# Understanding "What You See Is What It Does": A Be Framework Perspective on Orchestrated Transformation

## Abstract

The recent paper "What You See Is What It Does" (WYSIWD) presents a groundbreaking approach to software legibility through Concepts and Synchronizations. This paper introduces WYSIWD's innovations to the Being-Oriented Programming community, revealing surprising parallels with Be Framework's metamorphosis paradigm. While WYSIWD maintains orchestration through external coordination and Be Framework embodies transformation through type-driven flow, both converge on the same fundamental insight: legible software emerges from independent state machines with explicit transitions. This analysis demonstrates how different philosophical starting points—Western orchestration versus Eastern dependent origination—lead to the same computational truth.

> **Original Paper**: "What You See Is What It Does: A Structural Pattern for Legible Software" (2024)  
> Available at: https://arxiv.org/html/2508.14511v2

## 1. Introduction: A Remarkable Discovery

In 2024, a paper emerged proposing a radical solution to software's illegibility crisis [1]. "What You See Is What It Does" introduces Concepts—completely independent service modules—orchestrated by Synchronizations—declarative rules for coordination. The paper demonstrates this approach through a complete implementation of the RealWorld benchmark, achieving what traditional architectures struggle with: true modularity where LLMs can generate components with minimal context.

For those of us working on Be Framework's Being-Oriented Programming, reading this paper was both a validation and a revelation. Despite starting from opposite philosophical foundations—WYSIWD from Western orchestration traditions, Be Framework from Eastern concepts of dependent origination (縁起)—both systems converge on remarkably similar solutions. This convergence isn't coincidental but suggests fundamental truths about computation itself.

This paper serves three purposes: to introduce WYSIWD's innovations to readers unfamiliar with it, to analyze it through the lens of Be Framework's philosophy, and to explore what this convergence means for the future of programming. The analysis reveals that both frameworks solve the same critical problem: how to structure software so that both humans and AIs can understand, modify, and extend it without fear of cascading failures.

## 2. WYSIWD's Revolutionary Structure

### 2.1 Concepts: Radical Independence

At WYSIWD's heart lies a simple but powerful idea: every piece of functionality should be completely independent. A Concept is simultaneously a user-facing feature and an isolated service:

```
concept Password [U]
  purpose: to securely store and validate credentials
  state:
    password: U -> string
    salt: U -> string
  actions:
    set [user: U, password: string] => [user: U]
    check [user: U, password: string] => [valid: boolean]
```

The type parameter `U` is crucial—it's fully polymorphic, meaning the Password concept knows nothing about what a "user" actually is. This radical independence means Concepts can be developed, tested, and understood in complete isolation.

### 2.2 Synchronizations: Orchestration Without Coupling

If Concepts can't know about each other, how do they work together? WYSIWD's answer is Synchronizations—external rules that observe action completions and trigger subsequent actions:

```
sync UserRegistration
  when {
    Web/request: [method: "register", email: ?e] => []
    User/register: [] => [user: ?u]
  }
  then {
    Password/set: [user: ?u, password: ?p]
    Profile/create: [user: ?u]
    Email/send: [to: ?e, type: "welcome"]
  }
```

Synchronizations act as conductors, orchestrating independent musicians without the musicians knowing about each other. This separation makes coordination logic visible, modifiable, and testable.

### 2.3 Achieving True Legibility

The paper's title—"What You See Is What It Does"—isn't marketing. Every action is recorded, every synchronization leaves a trace, and causality is completely transparent:

```
Request[001] --[sync:Registration]--> User/register[002]
             --[sync:NewPassword]--> Password/set[003]
             --[sync:Welcome]--> Email/send[004]
```

When something goes wrong, the provenance graph shows exactly which synchronization fired and why, making debugging a matter of following traces rather than excavating code.

**Visual Comparison of Flow Models:**

```
WYSIWD: Orchestrated Flow
    [Web Request]
         ↓
    [Synchronization Engine] ← "The Conductor"
     ↙   ↓   ↘
[User]  [Pass]  [Email]  ← Independent Concepts
     ↘   ↓   ↙
    [Response Assembly]
         ↓
    [Web Response]

Be Framework: Natural Flow  
    RawRequest
        ↓ (type transformation)
    ValidatedRequest
        ↓ (type transformation)
    ProcessedRequest
        ↓ (type transformation)  
    ResponseReady
        
No conductor - just water flowing downhill
```

## 3. The View from Be Framework

### 3.1 Immediate Recognition

Reading WYSIWD triggered a startling recognition: this is dependent origination (縁起) expressed through Western engineering. Where Be Framework declares transformations through types:

```php
#[Be(ValidatedEmail::class)]
final class RawEmail {
    public function __construct(
        #[Input] public readonly string $value
    ) {}
}
```

WYSIWD achieves the same through synchronization rules. Both systems recognize that robust software emerges not from control but from clear relationships between independent states.

### 3.2 Orchestration versus Flow

The philosophical difference is profound yet the result is identical. WYSIWD maintains a conductor—the Synchronization engine—that orchestrates performances. Be Framework has no conductor; transformations flow like water finding its level:

```
WYSIWD: Synchronization observes → decides → commands
Be: Type declares destiny → transformation occurs naturally
```

This is not merely a technical choice but a choice of worldview:
- **WYSIWD**: The world is controllable and should be controlled
- **Be Framework**: The world is flow and we should align with that flow

Yet despite these opposite philosophical foundations, both create the same structure: independent modules connected by explicit, atomic transitions. This convergence suggests that perhaps both worldviews capture essential aspects of computational reality—control and flow are complementary perspectives on transformation.

### 3.3 Complementary Causality

Where the approaches beautifully complement each other is in recording causality. Consider a failed user registration:

**WYSIWD's Provenance Graph:**
```
Request[001] --[sync:Registration]--> User/register[002] SUCCESS
             --[sync:NewPassword]--> Password/set[003] FAILED
             --[sync:Rollback]--> User/delete[002]
```
Shows exactly which synchronization fired and in what order.

**Be Framework's Semantic Log:**
```json
{
  "attempted_metamorphosis": "RegistrationInput → RegisteredUser",
  "failed_at": "PasswordValidation",
  "validations": {
    "email.format": "passed",
    "email.uniqueness": "passed", 
    "password.strength": "failed"
  },
  "failure_reason": "Password requires special character",
  "suggested_fix": "Add at least one of: !@#$%^&*"
}
```
Explains why the transformation failed and how to fix it.

**Combined Understanding:**
```
WYSIWD: Registration sync triggered at 10:00:03.234 (WHEN)
Be Log: Password validation failed - needs special char (WHY)
Together: Complete causality - mechanical + intentional
```

This dual perspective provides unparalleled debugging capability. WYSIWD tells you the exact execution path, Be Framework tells you the semantic reason. Together, they eliminate the guesswork that plagues traditional debugging.

## 4. Solving the Same Problem

### 4.1 The Complexity Wall

Both frameworks address what WYSIWD aptly terms "vibe coding"—the practice where developers iteratively prompt AI for code, quickly hitting a wall where each addition breaks existing features. The paper provides striking evidence: in analyzing the SWE-Bench benchmark, researchers found that when accounting for test accuracy and training data contamination, LLM success rates dropped from apparent 30-40% to less than 5% on real-world tasks [1].

Traditional systems fail because hidden dependencies create exponential complexity growth. When modifying a class method, developers must understand not just that method but all related methods, subclasses, callers, and implicit dependencies—a context requirement that grows as O(n²) with system size.

WYSIWD solves this through radical independence: an AI can generate a complete Password concept knowing nothing about User or Profile concepts. Be Framework achieves the same through type-driven transformations: generating a transformation from `RawEmail` to `ValidatedEmail` requires knowing only those two types. Both reduce complexity from O(n²) to O(n).

### 4.2 Empirical Validation

WYSIWD provides compelling evidence through its RealWorld benchmark implementation—a Medium-clone comprising ~2,300 GitHub issues. The paper reports remarkable success rates:

- **Concept Generation**: 90%+ generated correctly in single LLM prompt
- **Concept Implementation**: Generated from specifications with one-shot success
- **Synchronization Rules**: Required average of 2-3 iterations to perfect
- **Debugging Time**: Reduced from hours to minutes using provenance graphs

The paper's debugging case study (Section 7.4) demonstrates the power of transparent causality: a registration bug that would traditionally require stepping through multiple classes was diagnosed and fixed by examining just the relevant synchronizations—the LLM identified the issue immediately when given only the problematic sync rules.

Be Framework's semantic logging approach offers complementary benefits in debugging and understanding transformation failures. The rich contextual information in semantic logs enables developers to understand not just the execution path but the semantic meaning of failures. However, systematic empirical validation of these benefits remains an important area for future research.

Both frameworks point toward a transformation in how AI systems can participate in software development—moving beyond code generation to become partners that can reason about system behavior.

## 5. The Deeper Convergence

### 5.1 Temporal Decomposition

The profound insight both frameworks share is that traditional OOP inappropriately compresses time into space. A User object that can be new, registered, active, and suspended is really four different entities crammed into one class with conditional logic everywhere:

```java
// Traditional OOP: Temporal compression
class User {
    private String state;  // "new", "registered", "active", "suspended"
    
    public void activate() {
        if (state.equals("registered")) {  // Conditional complexity
            // activation logic
            state = "active";
        } else {
            throw new InvalidStateException();
        }
    }
}
```

WYSIWD decomposes this into explicit states within Concepts, while Be Framework creates separate classes for each temporal stage. Both recognize that time should flow through space, not be compressed into it:

```
Traditional: User { state: string; if (state == "active") {...} }

WYSIWD: User concept with explicit state transitions
        state: users: set U
               status: U -> Status
        actions: register, activate, suspend (each atomic)

Be: NewUser → RegisteredUser → ActiveUser (separate immutable classes)
    Each transformation is type-safe and irreversible
```

### 5.2 Mathematical Isomorphism

Despite philosophical differences, both implement identical mathematical structures. Here's the precise correspondence:

| Mathematical Element | WYSIWD | Be Framework |
|---------------------|---------|--------------|
| **State Space Q** | Concept state components | Being class instances |
| **Input Alphabet Σ** | Action parameters | Constructor inputs |
| **Transition Function δ** | Synchronization rules | `#[Be]` declarations |
| **Initial State q₀** | Bootstrap concept state | Initial object creation |
| **Accepting States F** | Successful action completions | Valid type transformations |
| **Transition Atomicity** | Transaction semantics | Immutable objects |
| **Error States** | `action => [error: string]` | `Success\|Failure` types |

Both systems can be formally described as:
```
M = (Q, Σ, δ, q₀, F) where:
- Q is finite (bounded state space)
- δ: Q × Σ → Q is deterministic
- Transitions are atomic and logged
```

This isn't mere similarity—it's mathematical equivalence. Any WYSIWD system can be mechanically translated to Be Framework and vice versa, preserving behavior exactly:

```
// WYSIWD to Be translation
sync Rule                    →  #[Be(NextState::class)]
when {action: [] => [...]}   →  constructor parameters
then {action: [...]}         →  transformation result

// Be to WYSIWD translation  
#[Be(Target::class)]         →  sync Transformation
#[Input] params              →  when {trigger: params}
transformation logic         →  then {create: Target}
```

This isomorphism proves both systems are discovering the same computational truth from different angles.

## 6. Implications and Synthesis

### 6.1 Complementary Strengths

WYSIWD and Be Framework excel in different scenarios, suggesting a natural division of labor:

**WYSIWD's Orchestration Excels At:**
- Cross-service coordination in microservices
- Business workflow with complex rules
- Audit-critical systems requiring visible coordination
- Multi-tenant systems with varying sync rules

**Be Framework's Metamorphosis Excels At:**
- Data transformation pipelines
- Type-safe domain modeling
- Functional core business logic
- Immutable event sourcing

A hybrid approach emerges naturally for modern architectures:

```php
// Bounded Context 1: Internal transformations (Be Framework)
#[Be(ValidatedOrder::class)]
final class RawOrder {
    public function __construct(
        #[Input] public readonly array $items,
        #[Inject] private readonly Validator $validator
    ) {
        // Type-driven validation
    }
}

// Bounded Context 2: Processing pipeline (Be Framework)
#[Be(FulfilledOrder::class)]
final class PaymentComplete {
    // Natural flow through types
}

// Cross-Context Coordination (WYSIWD style)
sync OrderFulfillmentWorkflow
  when {
    OrderContext/validated: [order: ?o] => [success: true]
    PaymentContext/charged: [order: ?o] => [success: true]
  }
  then {
    InventoryContext/reserve: [items: ?items]
    ShippingContext/schedule: [order: ?o]
    NotificationContext/send: [type: "confirmation", order: ?o]
  }
```

This hybrid leverages each approach's strengths: Be for internal type-safe transformations, WYSIWD for explicit cross-boundary orchestration.

### 6.2 The AI-Native Future

Both frameworks point toward a profound shift in how software will be developed. The paper's vision of Concept Catalogs—reviewed, verified libraries of concepts—parallels Be Framework's vision of standardized semantic transformations. Together, they suggest a future where:

1. **Standard Components**: Industry-standard Concepts/Transformations for common patterns (authentication, payment, notification) that are formally verified and AI-understood

2. **Generative Composition**: AI systems that can:
   - Suggest missing synchronizations from execution patterns
   - Identify common transformation sequences for abstraction
   - Generate test cases from formal specifications
   - Propose optimizations from performance logs

3. **Self-Healing Systems**: Combining WYSIWD's provenance with Be's semantic logs enables:
   - Automatic error recovery paths
   - Performance degradation prediction
   - Security vulnerability detection from anomalous patterns

The convergence suggests this isn't just tooling evolution but a fundamental change in what programming means: from instructing machines to describing transformations.

## 7. Conclusion: Different Paths, Same Summit

WYSIWD and Be Framework represent different philosophical approaches to the same fundamental truth: software is about transformation, not control. One maintains the conductor-orchestra metaphor of Western thought, the other embodies the flowing-water metaphor of Eastern philosophy. Yet both arrive at the same summit—systems built from independent components with explicit, traceable transformations.

The convergence has immediate practical implications:

**For WYSIWD Practitioners:**
- Be Framework demonstrates how type systems can enforce transformation correctness
- Semantic logging provides richer debugging information than mechanical traces alone
- The metamorphosis pattern offers elegant solutions for pure data transformations

**For Be Framework Practitioners:**
- WYSIWD's synchronizations show how to make coordination logic visible and modifiable
- Provenance graphs provide audit trails that semantic logs alone cannot
- The Concept pattern offers superior isolation for multi-tenant scenarios

**For the Broader Community:**
- The convergence validates transformation-oriented programming as a paradigm
- Combined approaches can leverage both philosophies' strengths
- Standards for Concepts and Transformations could enable true component reuse

### The Path Forward

We propose three concrete steps:

1. **Joint Research**: Explore formal equivalence proofs and automated translation between approaches
2. **Hybrid Frameworks**: Develop tooling that supports both orchestration and metamorphosis patterns
3. **Shared Standards**: Create common specifications for transformations that both communities can adopt

The revolution isn't in choosing orchestration or metamorphosis. It's in recognizing that both paths lead away from the tangled jungle of traditional OOP toward the clarity of transformation-oriented programming. When independent projects discover the same truth, that truth transcends implementation.

As we stand at this convergence, we invite both communities to see not competition but confirmation. The mountain has multiple paths, but the summit view is spectacular from any approach. The future of software isn't in controlling complexity but in embracing transformation as the fundamental nature of computation.

---

*"When independent projects discover the same truth, that truth transcends implementation."*

## Call for Collaboration

Interested in exploring these connections further? 
- WYSIWD community: [Link to WYSIWD resources]
- Be Framework community: https://github.com/beframework
- Joint discussion: [Proposed forum/channel for cross-pollination]

Let's build the future of legible software together.

## References

1. Daniel Jackson et al. "What You See Is What It Does: A Structural Pattern for Legible Software" (2024). arXiv:2508.14511v2. Available at: https://arxiv.org/html/2508.14511v2

2. Be Framework Core Documentation: "Being-Oriented Programming Manifesto" - https://github.com/beframework/docs

3. Jackson, D. "The Essence of Software: Why Concepts Matter for Great Design" (2021). Princeton University Press.

4. Gothinkster. "RealWorld: The mother of all demo apps" - https://github.com/gothinkster/realworld

5. Jimenez et al. "SWE-bench: Can Language Models Resolve Real-World GitHub Issues?" (2024). arXiv:2310.06770.

6. Laozi. "Tao Te Ching" (6th century BCE) - On Wu Wei (無為) and natural transformation.

7. Hofstadter, D. "Gödel, Escher, Bach: An Eternal Golden Braid" (1979). Basic Books.

## Acknowledgments

We thank the authors of "What You See Is What It Does" for their groundbreaking work that validates and extends ideas we've been exploring in Be Framework. Special recognition to Daniel Jackson for his pioneering work on Concepts that laid the foundation for WYSIWD. Thanks also to the Be Framework community for their insights on the philosophical parallels between these approaches.

## Appendix: For WYSIWD Readers New to Be Framework

Be Framework implements Being-Oriented Programming (BOP), where objects declare their transformations through type annotations. Instead of "Tell, Don't Ask," Be follows "Be, Don't Do"—objects don't perform actions but transform into new states. This reflects the Buddhist concept of dependent origination (縁起), where things arise through conditions rather than control.

Key concepts:
- **`#[Be]` Attribute**: Declares what an object will become
- **`#[Input]`**: Marks data flowing from previous transformation  
- **`#[Inject]`**: Marks services needed for transformation
- **Semantic Logging**: Rich execution traces with meaning, not just mechanics
- **Type-Driven Destiny**: Types determine possible transformations

For more details, see the Be Framework documentation and philosophy papers in the references.
