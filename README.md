# Ray.Framework Concept

> "Just as light rays pass through a prism—instant, pure, and transformed."

Ray.Framework is a PHP framework that implements the Metamorphic Programming paradigm, where data transformation occurs through pure constructor-driven metamorphosis.

## Philosophy

Ray.Framework emerged from a simple yet profound question: What if we programmed by defining what can exist, rather than what should happen?

Building upon the philosophical foundations of Ray.Di's dependency injection pattern, Ray.Framework treats all data transformations as light passing through prisms—instant, pure, and transformed. Each object accepts its inevitable premises and transforms itself into a new, perfect form through constructor injection.

## Core Concepts

### Metamorphosis Classes

Every class in Ray.Framework is a Metamorphosis Class—a self-contained, immutable stage of transformation:

```php
#[Be(ProcessedData::class)]
final class RawData
{
    public function __construct(
        #[Input] public readonly string $value
    ) {
        // Pure data, ready for transformation
    }
}

final class ProcessedData
{
    public readonly string $processed;
    
    public function __construct(
        #[Input] string $value,
        DataProcessor $processor
    ) {
        // Transform in constructor
        $this->processed = $processor->process($value);
    }
}
```

### The Light Ray Execution

```php
$ray = new Ray($injector);
$result = $ray(new RawData('input'));
echo $result->processed; // Transformation complete
```

## Key Principles

1. **Constructor-Only Logic**: All transformation happens in constructors
2. **Immutable State**: All properties are `public readonly`
3. **Type Transparency**: No hidden state or mystery boxes
4. **Automatic Streaming**: Handle any data size with constant memory
5. **Self-Organizing Pipelines**: Objects declare their own destiny with `#[Be]`

## Documentation

- **[Ontological Programming: A New Paradigm](docs/ontological-programming-paper.md)**  
  The philosophical foundation introducing programming by defining existence

- **[Ray.Framework Whitepaper](docs/ray-framework-whitepaper.md)**  
  The complete technical and philosophical foundations of the Metamorphic Programming paradigm

- **[Metamorphosis Architecture Manifesto](docs/metamorphosis-architecture-manifesto.md)**  
  Practical implementation patterns including Traffic Controller and testing strategies

## Examples

- **[User Registration](examples/user-registration/)**  
  Complete implementation demonstrating Traffic Controller pattern, type-safe factories, and testing strategies

## The Journey

Ray.Framework emerged from recognizing universal patterns in data transformation. What started as a specific solution for HTTP data processing revealed deeper principles about how objects transform through constructor injection. This recognition, built upon years of experience with dependency injection and resource-oriented architectures, crystallized into the Metamorphic Programming paradigm.

## Key Paradigm Shifts

Ray.Framework represents fundamental shifts in how we think about programs:

- From **doing** to **being**
- From **instructions** to **transformations**
- From **mutable state** to **immutable existence**
- From **complex lifecycles** to **simple metamorphosis**

## Status

Ray.Framework is currently in the conceptual and early implementation phase. The ideas presented here emerged from deep dialogues about the nature of programming and represent a new approach to building applications.

## Contributing

We welcome thoughts, discussions, and contributions to this new paradigm. Whether philosophical insights or practical implementations, your perspective helps shape the future of programming as metamorphosis.

## License

MIT License - see [LICENSE](LICENSE) file for details

---

*"Like opening a window to let in sunlight, Ray.Framework opens PHP to let data flow naturally, transform completely, and emerge perfectly."*
