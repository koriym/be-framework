/**
 * Interface for resolving constructor arguments for metamorphosis transformations
 *
 * Resolves arguments needed for the destination class constructor from the current object,
 * following Be Framework's metamorphic programming paradigm.
 */
export interface BecomingArgumentsInterface {
  /**
   * Resolve constructor arguments for the becoming (destination) class
   *
   * @param current The current object being transformed
   * @param becoming The constructor function of the destination class
   * @returns Associative array of constructor arguments [paramName => value]
   */
  invoke(current: object, becoming: Function): Record<string, any>;
}
