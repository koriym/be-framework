import { Input } from '../../src';

/**
 * Final active user class - no further metamorphosis
 */
export class FakeActiveUser {
  public readonly activatedAt: Date;

  constructor(
    @Input() public readonly name: string,
    @Input() public readonly email: string,
    @Input() public readonly age: number,
    @Input() public readonly id: string,
    @Input() activatedAt: Date | null = null
  ) {
    // Activation logic - set activation time if not provided
    this.activatedAt = activatedAt || new Date();
  }
}
