# Be Framework - TypeScript Implementation

This is a TypeScript implementation of the Be Framework, which implements the Ontological Programming paradigm where data transformation occurs through pure constructor-driven metamorphosis.

## Key Concepts

- **Being Classes**: Self-contained, immutable stages of existence and transformation
- **Constructor-Only Logic**: All transformation happens in constructors
- **Immutable State**: All properties are `readonly`
- **Type Transparency**: No hidden state or mystery boxes
- **Automatic Streaming**: Handle any data size with constant memory
- **Self-Organizing Pipelines**: Objects declare their own destiny with `@Be` decorator

## Installation

```bash
cd poc-ts
npm install
```

## Running the Demo

```bash
npm run dev
```

This will run the basic demo that demonstrates the core concepts of the framework.

## Building the Project

```bash
npm run build
```

This will compile the TypeScript code to JavaScript in the `dist` directory.

## Usage

Here's a simple example of how to use the framework:

```typescript
import 'reflect-metadata';
import { Container } from 'inversify';
import { Be, Becoming, Input, Inject } from 'be-framework-ts';

// Define a class with a Be decorator to specify what it can become
@Be(NextStage)
class InitialStage {
  constructor(
    @Input() public readonly data: string
  ) {}
}

// Define the next stage in the metamorphosis chain
class NextStage {
  constructor(
    @Input() public readonly data: string,
    @Inject() public readonly service: SomeService
  ) {}
}

// Define a service to be injected
@injectable()
class SomeService {
  doSomething(data: string): string {
    return `Processed: ${data}`;
  }
}

// Set up the DI container
const container = new Container();
container.bind<SomeService>(SomeService).toSelf();

// Create the Becoming framework instance
const becoming = new Becoming(container);

// Start the metamorphosis chain
const initial = new InitialStage('Hello, world!');
const final = becoming.invoke(initial);

console.log(final); // NextStage instance
```

## Philosophy

The Be Framework follows a philosophy of "Be, Don't Do" and focuses on defining what can exist rather than what should happen. Objects undergo metamorphosis through constructor injection - a continuous process of becoming.
