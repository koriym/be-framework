import 'reflect-metadata';
import { BE_METADATA_KEY, Be } from '../src/Be';
import { FakeNoMetamorphosis } from './Fake/FakeNoMetamorphosis';
import { FakeValidatedUser } from './Fake/FakeValidatedUser';
import { FakeBranchingInput } from './Fake/FakeBranchingInput';
import { FakeRegisteredUser } from './Fake/FakeRegisteredUser';
import { FakeRegularUser } from './Fake/FakeRegularUser';
import { FakePremiumUser } from './Fake/FakePremiumUser';

describe('Be Decorator', () => {
  it('should store metadata on the class for linear transformation', () => {
    // FakeValidatedUser has @Be('FakeRegisteredUser') due to circular dependency
    const metadata = Reflect.getMetadata(BE_METADATA_KEY, FakeValidatedUser);
    expect(metadata).toBe('FakeRegisteredUser');
  });

  it('should store metadata on the class for branching transformation', () => {
    // FakeBranchingInput has @Be([FakePremiumUser, FakeRegularUser])
    const metadata = Reflect.getMetadata(BE_METADATA_KEY, FakeBranchingInput);
    expect(Array.isArray(metadata)).toBe(true);
    expect(metadata).toContain(FakePremiumUser);
    expect(metadata).toContain(FakeRegularUser);
    expect(metadata.length).toBe(2);
  });

  it('should not have metadata on classes without the Be decorator', () => {
    // FakeNoMetamorphosis has no @Be decorator
    const metadata = Reflect.getMetadata(BE_METADATA_KEY, FakeNoMetamorphosis);
    expect(metadata).toBeUndefined();
  });

  // Test the decorator directly
  it('should apply metadata when used as a decorator', () => {
    // Create a test class with the Be decorator
    @Be(FakeNoMetamorphosis)
    class TestClass {}

    const metadata = Reflect.getMetadata(BE_METADATA_KEY, TestClass);
    expect(metadata).toBe(FakeNoMetamorphosis);
  });

  it('should apply array metadata when used as a decorator with multiple classes', () => {
    // Create a test class with the Be decorator using an array
    @Be([FakePremiumUser, FakeRegularUser])
    class TestClass {}

    const metadata = Reflect.getMetadata(BE_METADATA_KEY, TestClass);
    expect(Array.isArray(metadata)).toBe(true);
    expect(metadata).toContain(FakePremiumUser);
    expect(metadata).toContain(FakeRegularUser);
  });
});
