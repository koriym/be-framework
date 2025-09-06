# Psalm Immutability Experiment: Framework Reality Check

This document records the experimental application of Psalm's immutability annotations to the Be Framework, revealing the practical limitations of `@psalm-immutable` in real-world framework code.

## Context

After reading [Psalm's "Immutability and Beyond"](https://psalm.dev/articles/immutability-and-beyond) article, we attempted to systematically apply `@psalm-immutable`, `@psalm-mutation-free`, and `@psalm-readonly` annotations to the Be Framework codebase to improve type safety and functional programming enforcement.

**Initial Hypothesis**: Framework classes with `public readonly` properties and constructor-only logic should be good candidates for immutability annotations.

**Reality Check**: The experiment revealed fundamental incompatibilities between Psalm's strict immutability model and practical framework architecture.

## Experimental Process

### Phase 1: Targeting "Obviously Immutable" Classes

**Initial Candidates**:
- `Types` - Static type alias definitions only
- `Be`, `Message`, `SemanticTag` - Attribute classes with `public readonly` properties
- Context classes - `MetamorphosisOpenContext`, `MetamorphosisCloseContext`
- Data classes - `Errors`, destination classes

### Phase 2: Psalm Error Discovery

#### Error Type 1: MutableDependency
```
Be\Framework\SemanticLog\Context\MetamorphosisCloseContext is marked @psalm-immutable 
but Koriym\SemanticLogger\AbstractContext is not
```

**Discovery**: `@psalm-immutable` classes cannot inherit from non-immutable parents, even if the child only adds `public readonly` properties.

#### Error Type 2: ImpureMethodCall  
```
Cannot call a possibly-mutating method ValidationMessageHandler::getMessagesForExceptions 
from a mutation-free context
```

**Discovery**: Even instantiating objects (`new ValidationMessageHandler()`) is considered "impure" by Psalm.

#### Error Type 3: MissingImmutableAnnotation
```
Be\Framework\SemanticVariable\Errors is marked @psalm-immutable, 
but Be\Framework\SemanticVariable\NullErrors is not marked @psalm-immutable
```

**Discovery**: Immutability must propagate through entire inheritance chains.

### Phase 3: Framework Logic Classes

Attempted to apply `@psalm-immutable` to core framework classes:

**`Being` class errors**:
```
Cannot call a possibly-mutating method ReflectionAttribute::newInstance from a mutation-free context
Cannot call a possibly-mutating method LoggerInterface::open from a mutation-free context  
Cannot call a possibly-mutating method BecomingArgumentsInterface::be from a mutation-free context
Cannot call a possibly-mutating method ReflectionClass::newInstanceArgs from a mutation-free context
```

**Discovery**: Virtually all framework operations are considered "impure":
- Reflection API usage
- Dependency injection container calls
- Logging operations
- Object instantiation

## Technical Discoveries

### Psalm's Immutability Strictness

Psalm enforces an extremely strict definition of immutability that excludes:

1. **External System Interactions**:
   - DI container calls (`$injector->getInstance()`)
   - Logging operations (`$logger->log()`)
   - Database queries, HTTP requests, file I/O

2. **Reflection and Meta-programming**:
   - `ReflectionClass::newInstanceArgs()`
   - `ReflectionAttribute::newInstance()`
   - Any dynamic object creation

3. **Inheritance Constraints**:
   - Parent classes must also be `@psalm-immutable`
   - Cannot extend third-party non-immutable classes
   - Child classes must maintain immutability

### Framework Architecture Reality

Modern frameworks fundamentally depend on "impure" operations:

```php
// Core framework pattern - all "impure" by Psalm standards
public function metamorphose(object $current, string $becoming): object {
    $openId = $this->logger->open($current, $becoming);        // Logging
    $args = $this->becomingArguments->be($current, $becoming); // DI container
    $result = (new ReflectionClass($becoming))->newInstanceArgs($args); // Reflection
    $this->logger->close($result, $openId);                   // Logging
    return $result;
}
```

## Design Consideration: ImmutableBecoming

### The Hypothesis
Could we create a state-less `ImmutableBecoming` that receives dependencies externally?

```php
final class ImmutableBecoming {
    public function __invoke(object $input, InjectorInterface $injector): object {
        // No instance state, all dependencies injected
    }
}
```

### The Reality Check
Even with external dependency injection, the core operations remain "impure":

1. **DI Container Usage**: `$injector->getInstance()` mutates container state
2. **Object Creation**: `new ReflectionClass()` is considered impure
3. **Logging**: Any external output violates pure function principles

**Conclusion**: The fundamental architecture of dependency injection frameworks is incompatible with Psalm's immutability model.

## Successfully Applied Annotations

### @psalm-immutable (Class-level)
Only the most trivial classes could accept this strict annotation:

- **`Types`**: Empty class with only type aliases
- **`Be`**: Attribute class with single `public readonly` property
- **`Message`**: Attribute class with `public readonly` array
- **`SemanticTag`**: Attribute class with `public readonly` string

**Common Characteristics**:
- No methods (or only trivial accessors)
- No external dependencies
- No inheritance from non-immutable classes
- PHP attributes (used for metadata, not executed logic)

### @psalm-mutation-free (Method-level) - More Practical Success
Several methods could accept this more flexible annotation:

**Static Factory Methods**:
- `TypeMatchingFailure::create()` - String manipulation only
- `MissingParameterAttribute::create()` - String manipulation only
- `ConflictingParameterAttributes::create()` - String manipulation only

**Simple Getters**:
- `TypeMatchingFailure::getCandidateErrors()` - Property return only
- `Errors::hasErrors()` - Array empty check
- `Errors::count()` - Array count

**Read-only Operations**:
- `BecomingArguments::isInputParameter()` - Reflection attribute reading with validation

**Key Discovery**: `@psalm-mutation-free` proved much more practical than `@psalm-immutable` because:
1. **Method-level constraints** are less restrictive than class-level
2. **Common patterns** (getters, calculations, validation) are often mutation-free
3. **Framework code** can benefit without architectural changes

## Practical Value Assessment

### @psalm-immutable - Academic Value: High, Practical Value: Near Zero
- **Demonstrates Psalm's theoretical capabilities**
- **Enforces strict functional programming principles**  
- **Provides mathematical guarantees about state immutability**
- **But**: Applicable only to trivial data structures
- **But**: Incompatible with framework architecture patterns
- **But**: Most applicable classes are already obviously immutable

### @psalm-mutation-free - Moderate Practical Value
- **Applicable to common patterns** (getters, validators, factories)
- **Documents method behavior** for API consumers
- **Provides optimization hints** for static analysis
- **Enables partial functional guarantees** without architectural changes
- **Low maintenance overhead** compared to class-level annotations

### The Paradox
Classes that could benefit from immutability annotations (complex logic classes) cannot use them due to external dependencies, while classes that can use them (simple data structures) don't need them because their immutability is already obvious.

## Framework Design Implications

### Alternative Approaches for Immutability

Instead of Psalm annotations, the Be Framework achieves practical immutability through:

1. **Language-Level Immutability**:
   - `readonly` classes (PHP 8.2+)
   - `public readonly` properties
   - Constructor-only logic patterns

2. **Architectural Immutability**:
   - Pure constructor logic with all side effects in DI container
   - No mutator methods
   - Transformation through metamorphosis rather than mutation

3. **Conventional Immutability**:
   - Clear naming patterns (`Being` vs `Becoming`)
   - Documented immutable contracts
   - Test coverage ensuring no state mutation

## Lessons Learned

### 1. Tool Limitations vs. Real-World Code
Static analysis tools often impose theoretical purity constraints that conflict with practical software architecture needs.

### 2. Framework Architecture Trade-offs
Dependency injection, logging, and reflection - core framework features - are fundamentally incompatible with strict functional purity.

### 3. Immutability Spectrum
There's a spectrum from "theoretically pure" to "practically immutable." Framework code operates in the practical zone.

### 4. Annotation Overhead
Adding annotations without practical benefit creates maintenance overhead and false signals about code architecture.

## Future Recommendations

### For Be Framework Development

1. **Skip `@psalm-immutable` Class Annotations**: The cost/benefit ratio is prohibitive for framework code
2. **Selectively Apply `@psalm-mutation-free`**: Use for getters, validators, and pure computational methods
3. **Focus on Language-Level Immutability**: Use `readonly` classes and properties as primary strategy
4. **Maintain Architectural Immutability**: Continue constructor-only transformation patterns
5. **Document Design Patterns**: Record immutability through architecture, not just annotations

### For Framework Design Generally

1. **Embrace Pragmatic Purity**: Accept that frameworks require some impure operations
2. **Isolate Side Effects**: Concentrate impure operations in dedicated boundaries (DI container, logging layer)
3. **Use Language Features**: Leverage built-in immutability features over external tooling
4. **Test Behavior, Not Annotations**: Focus on behavioral tests rather than static annotation compliance

## Conclusion

This experiment demonstrated that while Psalm's immutability annotations represent sophisticated static analysis capabilities, they have limited practical application in framework development. The strict theoretical purity they enforce is incompatible with the architectural patterns that make frameworks useful.

**Key Insight**: Practical immutability in frameworks comes from disciplined architecture and language features, not from static analysis annotations.

**Recommendation**: Framework developers should focus on architectural immutability patterns rather than attempting to satisfy strict static analysis immutability constraints.

---

*This experiment highlights the importance of understanding tool limitations and choosing the right level of abstraction for different types of immutability guarantees in software systems.*