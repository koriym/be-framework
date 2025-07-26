import { Input } from '../../src';

/**
 * Simple result object that will be passed as property
 */
export class FakeResult {
  constructor(
    @Input() public readonly value: string,
    @Input() public readonly isSuccess: boolean
  ) {}
}
