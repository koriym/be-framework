import 'reflect-metadata';

/**
 * Metadata key for the Be decorator
 */
export const BE_METADATA_KEY = 'be:framework:be';

/**
 * Declares what this object can become
 *
 * Examples:
 * - @Be(NextStage) - Linear transformation
 * - @Be([SuccessPath, FailurePath]) - Type-driven branching
 *
 * When array is used, the actual becoming is determined by the framework
 * based on the object's internal state and type matching.
 */
export function Be(being: Function | Function[]) {
  return function<T extends { new (...args: any[]): any }>(target: T): T {
    // Store the being information as metadata on the class
    Reflect.defineMetadata(BE_METADATA_KEY, being, target);
    return target;
  };
}
