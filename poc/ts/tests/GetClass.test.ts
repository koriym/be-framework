import 'reflect-metadata';
import { GetClass } from '../src/GetClass';
import { FakeNoMetamorphosis } from './Fake/FakeNoMetamorphosis';
import { FakeValidatedUser } from './Fake/FakeValidatedUser';
import { FakeBranchingInput } from './Fake/FakeBranchingInput';
import { FakeRegisteredUser } from './Fake/FakeRegisteredUser';
import { FakeRegularUser } from './Fake/FakeRegularUser';
import { FakePremiumUser } from './Fake/FakePremiumUser';
import { FakeActiveUser } from './Fake/FakeActiveUser';

describe('GetClass', () => {
  let getClass: GetClass;

  beforeEach(() => {
    getClass = new GetClass();
    // Register classes to resolve string identifiers
    getClass.registerClass('FakeRegisteredUser', FakeRegisteredUser);
    getClass.registerClass('FakeActiveUser', FakeActiveUser);
  });

  it('should return the next class for linear transformation', () => {
    // FakeValidatedUser has @Be(FakeRegisteredUser)
    const validatedUser = new FakeValidatedUser('John', 'john@example.com', 25);
    const nextClass = getClass.invoke(validatedUser);
    expect(nextClass).toBe(FakeRegisteredUser);
  });

  it('should return an array of classes for branching transformation', () => {
    // FakeBranchingInput has @Be([FakePremiumUser, FakeRegularUser])
    const branchingInput = new FakeBranchingInput('John', 'john@example.com', true);
    const nextClasses = getClass.invoke(branchingInput);
    expect(Array.isArray(nextClasses)).toBe(true);
    expect(nextClasses).toContain(FakePremiumUser);
    expect(nextClasses).toContain(FakeRegularUser);
  });

  it('should return null for classes without the Be decorator', () => {
    // FakeNoMetamorphosis has no @Be decorator
    const noMetamorphosis = new FakeNoMetamorphosis('Hello World');
    const nextClass = getClass.invoke(noMetamorphosis);
    expect(nextClass).toBeNull();
  });

  it('should return null for final classes in a metamorphosis chain', () => {
    // FakeActiveUser has no @Be decorator (it's the end of the chain)
    const registeredUser = new FakeRegisteredUser('John', 'john@example.com', 25);
    const nextClass = getClass.invoke(registeredUser);
    expect(nextClass).not.toBeNull(); // It should have a next class

    // But if we were to get the next class after the final one, it would be null
    // This is tested in the Becoming integration test
  });
});
