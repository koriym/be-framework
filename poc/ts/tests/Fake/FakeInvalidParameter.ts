/**
 * Class with parameter that has no @Input or @Inject decorator
 */
export class FakeInvalidParameter {
  constructor(
    public readonly input: string, // Missing @Input or @Inject decorator
    public readonly extra: string
  ) {}
}
