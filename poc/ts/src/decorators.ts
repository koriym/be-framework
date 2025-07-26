import 'reflect-metadata';

/**
 * Metadata keys for decorators
 */
export const INPUT_METADATA_KEY = 'be:framework:input';
export const INJECT_METADATA_KEY = 'be:framework:inject';
export const NAMED_METADATA_KEY = 'be:framework:named';

/**
 * Marks a parameter to be resolved from the current object's properties
 */
export function Input(): ParameterDecorator {
  return function(target: Object, propertyKey: string | symbol | undefined, parameterIndex: number) {
    // Store metadata on the class
    const existingInputParams: number[] = Reflect.getOwnMetadata(INPUT_METADATA_KEY, target.constructor) || [];
    existingInputParams.push(parameterIndex);
    Reflect.defineMetadata(INPUT_METADATA_KEY, existingInputParams, target.constructor);
  };
}

/**
 * Marks a parameter to be resolved from the DI container
 */
export function Inject(): ParameterDecorator {
  return function(target: Object, propertyKey: string | symbol | undefined, parameterIndex: number) {
    // Store metadata on the class
    const existingInjectParams: number[] = Reflect.getOwnMetadata(INJECT_METADATA_KEY, target.constructor) || [];
    existingInjectParams.push(parameterIndex);
    Reflect.defineMetadata(INJECT_METADATA_KEY, existingInjectParams, target.constructor);
  };
}

/**
 * Specifies a named binding for DI resolution
 */
export function Named(value: string): ParameterDecorator {
  return function(target: Object, propertyKey: string | symbol | undefined, parameterIndex: number) {
    // Store metadata on the class with the named value
    const existingNamedParams: Record<number, string> = Reflect.getOwnMetadata(NAMED_METADATA_KEY, target.constructor) || {};
    existingNamedParams[parameterIndex] = value;
    Reflect.defineMetadata(NAMED_METADATA_KEY, existingNamedParams, target.constructor);
  };
}
