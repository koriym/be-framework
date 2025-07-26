import { Input } from '../../src';

/**
 * Regular user class (fallback for non-premium users)
 */
export class FakeRegularUser {
  constructor(
    @Input() public readonly name: string,
    @Input() public readonly email: string,
    @Input() public readonly isPremium: boolean = false
  ) {
    // This constructor accepts any isPremium value
  }
}
