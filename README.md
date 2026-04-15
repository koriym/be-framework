# Be Framework

> A paradigm where the domain, seen in the first person, ceaselessly transforms through encounters with others — proving its existence in every becoming.

**"Be, Don't Do"**

## The Core Shift: Domain in the First Person

In conventional programming, the system operates on the domain from a god's-eye view: fetch, validate, save — all commands issued in the third person. Even "Tell, Don't Ask" still has a commander.

Be Framework places the viewpoint inside the domain itself. The domain is the subject. Everything that follows is derived from this single shift in perspective.

### Self-Proving Existence

An `Email` object doesn't get validated — it either exists or it doesn't. Existence itself is proof of correctness. Semantic variables embody this principle: they define a world where invalid values simply cannot be.

### Transformation Through Encounter

The domain transforms itself through encounters with others. What it already is (immanent) meets what the world provides (transcendent), and a new being emerges. A `PatientArrival` encounters a triage protocol and becomes an `EmergencyCase` — not because something processes it, but because it becomes.

### Self-Determined Relations

The domain determines its own relationships with others. Unlike controllers that can call any model without constraint, each being declares what it can become. The `#[Be]` attribute is this declaration — the domain's own voice saying what transformation is possible.

### No Commander

There is no orchestrator, no service class directing the flow. Input enters and flows through a landscape of beings like water finding its path. This is choreography — each dancer knows only their own part, yet the whole emerges in harmony.

```php
$becoming = new Becoming($injector);
$final = $becoming(new OrderInput($items, $customer, $payment));
```

One line. No controller. The domain transforms itself.

## What Does This Look Like?

```php
#[Be(Greeting::class)]
final readonly class NameInput
{
    public function __construct(
        public string $name  // Immanent: what I am
    ) {}
}

final readonly class Greeting
{
    public string $message;

    public function __construct(
        #[Input] string $name,              // Immanent: carried forward
        #[Inject] WorldGreeting $greeting   // Transcendent: what I encounter
    ) {
        $this->message = "{$greeting->text} {$name}";  // New immanent: what I become
    }
}
```

```php
$becoming = new Becoming($injector);
$greeting = $becoming(new NameInput('world'));

echo $greeting->message; // hello world
```

`NameInput` declares that it can become a `Greeting`. When it encounters `WorldGreeting` from the outside world, it transforms. Each class is immutable — not because immutability is a goal, but because a being in a moment is that moment's being alone.

## Documentation

**[Full Documentation](https://be-framework.github.io/)**

## Patterns

**[be-patterns](https://github.com/be-framework/be-patterns)** — Working examples of Be Framework in action

## Philosophical Roots

Across cultures and centuries, deep inquiry into the nature of things arrives at the same place: existence and time. If that is where all fundamental questions converge, the domain too might be described in those terms — that is the question this paradigm explores.

## Status

Be Framework is currently in the conceptual and early implementation phase.
