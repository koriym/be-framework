import { Input } from '../../src';
import { FakeResult } from './FakeResult';

/**
 * Final class that receives the result object from previous step
 */
export class FakeFinishedProcess {
  constructor(
    @Input() public readonly result: FakeResult,
    @Input() public readonly input: string
  ) {}
}
