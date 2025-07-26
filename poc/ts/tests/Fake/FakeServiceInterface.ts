/**
 * Interface for fake service
 */
export interface FakeServiceInterface {
  name: string;
}

// Symbol for DI binding
export const FakeServiceInterfaceSymbol = Symbol.for('FakeServiceInterface');
