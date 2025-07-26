import { Be, Input } from '../../src';
import { FakeFailingUserA } from './FakeFailingUserA';
import { FakeFailingUserB } from './FakeFailingUserB';

/**
 * Class that will fail to transform because both target classes require parameters not provided
 */
@Be([FakeFailingUserA, FakeFailingUserB])
export class FakeFailingBranch {
  constructor(
    @Input() public readonly name: string
    // Missing parameters required by FakeFailingUserA and FakeFailingUserB
  ) {}
}
