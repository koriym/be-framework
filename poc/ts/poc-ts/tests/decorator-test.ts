import 'reflect-metadata';
import { Input, Inject, INPUT_METADATA_KEY, INJECT_METADATA_KEY } from '../src/decorators';

// Test class with decorated parameters
class TestClass {
  constructor(
    @Input() public readonly inputParam: string,
    @Inject() public readonly injectParam: number
  ) {}
}

// Main function to run the test
function testDecorators() {
  console.log('Testing decorators...');

  // Get the metadata
  const inputParams: number[] = Reflect.getMetadata(INPUT_METADATA_KEY, TestClass) || [];
  const injectParams: number[] = Reflect.getMetadata(INJECT_METADATA_KEY, TestClass) || [];

  console.log('Input params:', inputParams);
  console.log('Inject params:', injectParams);

  // Check if the decorators are working
  if (inputParams.includes(0)) {
    console.log('Input decorator is working!');
  } else {
    console.log('Input decorator is NOT working!');
  }

  if (injectParams.includes(1)) {
    console.log('Inject decorator is working!');
  } else {
    console.log('Inject decorator is NOT working!');
  }
}

// Run the test
testDecorators();
