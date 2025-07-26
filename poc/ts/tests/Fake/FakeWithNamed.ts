import { Input, Inject, Named } from '../../src';

/**
 * Class that uses @Named for named DI bindings
 */
export class FakeWithNamed {
  constructor(
    @Input() public readonly input: string,
    @Inject() @Named('debug') public readonly logLevel: string
  ) {}
}
