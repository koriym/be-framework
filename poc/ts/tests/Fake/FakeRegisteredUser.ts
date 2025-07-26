import { Be, Input } from '../../src';

// Forward declaration to avoid circular dependency
// We'll use a string identifier instead of the actual class
const FAKE_ACTIVE_USER = 'FakeActiveUser';

/**
 * Registered user class that becomes ActiveUser
 */
@Be(FAKE_ACTIVE_USER)
export class FakeRegisteredUser {
  public readonly id: string;

  constructor(
    @Input() public readonly name: string,
    @Input() public readonly email: string,
    @Input() public readonly age: number,
    @Input() id: string | null = null
  ) {
    // Registration logic - assign ID if not provided
    this.id = id || `user_${Math.random().toString(36).substring(2, 9)}`;
  }
}
