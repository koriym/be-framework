import { Input } from '../../src';

/**
 * Premium user class (requires isPremium = true)
 */
export class FakePremiumUser {
  constructor(
    @Input() public readonly name: string,
    @Input() public readonly email: string,
    @Input() public readonly isPremium: boolean
  ) {
    // This constructor will fail if isPremium is false
    if (!isPremium) {
      throw new Error('Premium user requires isPremium = true');
    }
  }
}
