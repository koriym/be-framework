import { Input } from '../../src';

/**
 * Another user class that requires a parameter not provided by FakeFailingBranch
 */
export class FakeFailingUserB {
  constructor(
    @Input() public readonly name: string,
    @Input() public readonly anotherRequired: number // This parameter is not provided by FakeFailingBranch
  ) {}
}
