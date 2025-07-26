import { Be, Input } from '../../src';
import { FakePremiumUser } from './FakePremiumUser';
import { FakeRegularUser } from './FakeRegularUser';

/**
 * Class that can become multiple different types (branching metamorphosis)
 */
@Be([FakePremiumUser, FakeRegularUser])
export class FakeBranchingInput {
  constructor(
    @Input() public readonly name: string,
    @Input() public readonly email: string,
    @Input() public readonly isPremium: boolean = false
  ) {}
}
