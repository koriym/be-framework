import { Input } from '../../src';

/**
 * Class without @Be decorator - no metamorphosis
 */
export class FakeNoMetamorphosis {
  constructor(
    @Input() public readonly message: string
  ) {}
}
