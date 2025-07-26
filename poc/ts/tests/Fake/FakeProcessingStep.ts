import { Be, Input } from '../../src';
import { FakeResult } from './FakeResult';
import { FakeFinishedProcess } from './FakeFinishedProcess';

/**
 * Processing step that creates a result object and declares next transformation
 */
@Be(FakeFinishedProcess)
export class FakeProcessingStep {
  public readonly result: FakeResult;

  constructor(
    @Input() public readonly input: string
  ) {
    // Create result object based on input
    this.result = new FakeResult(input, input.length > 3);
  }
}
