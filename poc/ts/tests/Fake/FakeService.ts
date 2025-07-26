import { injectable } from 'inversify';
import { FakeServiceInterface } from './FakeServiceInterface';

/**
 * Simple service implementation
 */
@injectable()
export class FakeService implements FakeServiceInterface {
  constructor(
    public readonly name: string = 'DefaultService'
  ) {}
}
