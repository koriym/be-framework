import { Be, Input } from '../../src';
import { FakeProcessingStep } from './FakeProcessingStep';

/**
 * Starting point for object property inheritance test
 */
@Be(FakeProcessingStep)
export class FakeInputData {
  constructor(
    @Input() public readonly input: string
  ) {}
}
