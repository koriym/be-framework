import { Be, Input } from '../../src';

// Forward declaration to avoid circular dependency
// We'll use a string identifier instead of the actual class
const FAKE_REGISTERED_USER = 'FakeRegisteredUser';

/**
 * Validated user class that becomes RegisteredUser
 */
@Be(FAKE_REGISTERED_USER)
export class FakeValidatedUser {
  constructor(
    @Input() public readonly name: string,
    @Input() public readonly email: string,
    @Input() public readonly age: number
  ) {
    // Validation logic
    if (!name) {
      throw new Error('Name cannot be empty');
    }
    if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
      throw new Error('Invalid email format');
    }
    if (age < 0) {
      throw new Error('Age must be positive');
    }
  }
}
