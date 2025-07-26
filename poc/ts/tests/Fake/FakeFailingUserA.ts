import { Input } from '../../src';

/**
 * User class that requires a parameter not provided by FakeFailingBranch
 */
export class FakeFailingUserA {
  constructor(
    @Input() public readonly name: string,
    @Input() public readonly requiredParam: string // This parameter is not provided by FakeFailingBranch
  ) {}
}
