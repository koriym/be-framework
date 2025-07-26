import { Input, Inject } from '../../src';
import { FakeService } from './FakeService';

/**
 * Class that uses @Inject for DI resolution
 */
export class FakeWithInject {
  constructor(
    @Input() public readonly input: string,
    @Inject() public readonly service: FakeService
  ) {}
}
