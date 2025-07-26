import 'reflect-metadata';
import { Container, injectable, inject } from 'inversify';
import { Be, Becoming, Input, Inject } from './index';

// =============================================================================
// SERVICES - Injected capabilities
// =============================================================================

interface DataValidatorInterface {
  isValid(data: string): boolean;
  getErrors(data: string): string;
}

// Symbol for DI binding
const DataValidatorInterfaceSymbol = Symbol.for('DataValidatorInterface');

// =============================================================================
// DEMO OBJECTS - The Metamorphosis Chain
// =============================================================================

// =============================================================================
// VALUE OBJECTS - The essence of being
// =============================================================================

class Success {
  constructor(
    public readonly name: string,
    public readonly message: string
  ) {}
}

class Failure {
  constructor(
    public readonly error: string
  ) {}
}

/**
 * Valid user after successful validation
 */
class ValidUser {
  constructor(
    @Input() public readonly being: Success
  ) {}
}

/**
 * Error response containing user input and error details
 */
class ErrorResponse {
  constructor(
    @Input() public readonly being: Failure
  ) {}
}

/**
 * Validation attempt - determines success or failure
 */
@Be([ValidUser, ErrorResponse])
class BeingUser {
  public readonly being: Success | Failure;

  constructor(
    @Input() name: string,
    validator: DataValidatorInterface
  ) {
    this.being = validator.isValid(name)
      ? new Success(name, 'Valid user name')
      : new Failure(validator.getErrors(name));
  }
}

/**
 * User input to be validated
 */
@Be(BeingUser)
class UserInput {
  constructor(
    @Input() public readonly name: string
  ) {}
}

@injectable()
class SimpleValidator implements DataValidatorInterface {
  isValid(data: string): boolean {
    return data !== '' && data.length > 3;
  }

  getErrors(data: string): string {
    if (data === '') {
      return 'Data cannot be empty';
    }
    if (data.length <= 3) {
      return 'Data must be longer than 3 characters';
    }
    return '';
  }
}

// =============================================================================
// DEMO EXECUTION
// =============================================================================

console.log("Be Framework - Metamorphic Programming Demo\n");

// Setup DI container
const container = new Container();
container.bind<DataValidatorInterface>(DataValidatorInterfaceSymbol).to(SimpleValidator);

// Create Becoming framework instance
const becoming = new Becoming(container);

const userNames = ['Alice', 'Bo', ''];

userNames.forEach(name => {
  const userInput = new UserInput(name);
  const maybeUser = becoming.invoke(userInput);

  if (maybeUser instanceof ValidUser) {
    console.log(`'${name}' -> ValidUser: ${(maybeUser.being as Success).name}`);
  } else if (maybeUser instanceof ErrorResponse) {
    console.log(`'${name}' -> ErrorResponse: ${(maybeUser.being as Failure).error}`);
  }
});
