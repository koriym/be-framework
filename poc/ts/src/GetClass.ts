import 'reflect-metadata';
import { BE_METADATA_KEY } from './Be';

/**
 * Gets the next class name in metamorphosis chain
 */
export class GetClass {
  // Registry to resolve string identifiers to actual classes
  private classRegistry: Record<string, Function> = {};

  /**
   * Register a class with a string identifier
   *
   * @param identifier String identifier for the class
   * @param classConstructor The actual class constructor
   */
  registerClass(identifier: string, classConstructor: Function): void {
    this.classRegistry[identifier] = classConstructor;
  }

  /**
   * Get what this object is becoming
   *
   * @param current Current object in metamorphosis chain
   * @returns Next class(es) or null if transformation is complete
   */
  invoke(current: object): Function | Function[] | null {
    const constructor = current.constructor;

    // Check if the class has the Be decorator metadata
    if (Reflect.hasMetadata(BE_METADATA_KEY, constructor)) {
      // Get the being information from metadata
      const being = Reflect.getMetadata(BE_METADATA_KEY, constructor);

      // Handle string identifiers
      if (typeof being === 'string') {
        if (this.classRegistry[being]) {
          return this.classRegistry[being];
        }
        // If the class is not registered, return null
        console.warn(`Class "${being}" not found in registry`);
        return null;
      }

      // Handle array of string identifiers
      if (Array.isArray(being) && being.length > 0 && typeof being[0] === 'string') {
        return being.map(id => {
          if (typeof id === 'string' && this.classRegistry[id]) {
            return this.classRegistry[id];
          }
          return id;
        }).filter(Boolean);
      }

      // Return the being information as is (Function or Function[])
      return being;
    }

    return null;
  }
}
