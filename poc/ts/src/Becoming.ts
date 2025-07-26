import { Container } from 'inversify';
import { MetamorphosisInterface } from './MetamorphosisInterface';
import { GetClass } from './GetClass';
import { BecomingArguments } from './BecomingArguments';
import { BecomingArgumentsInterface } from './BecomingArgumentsInterface';

/**
 * The Be Framework - Metamorphic Programming Engine
 *
 * "Objects undergo metamorphosis through constructor injection -
 * a continuous process of becoming."
 */
export class Becoming implements MetamorphosisInterface {
  private readonly getClass: GetClass;
  private readonly becomingArguments: BecomingArgumentsInterface;

  constructor(
    private readonly container: Container,
    becomingArguments?: BecomingArgumentsInterface
  ) {
    this.getClass = new GetClass();
    this.becomingArguments = becomingArguments || new BecomingArguments(this.container);
  }

  invoke(input: object): object {
    let current = input;

    // The core metamorphosis loop - life as continuous becoming
    let becoming = this.getClass.invoke(current);
    while (becoming) {
      current = this.metamorphose(current, becoming);
      becoming = this.getClass.invoke(current);
    }

    return current;
  }

  /**
   * The moment of transformation - pure and irreversible
   */
  private metamorphose(current: object, becoming: Function | Function[]): object {
    if (typeof becoming === 'function') {
      // Single class case - direct transformation
      const args = this.becomingArguments.invoke(current, becoming);
      return Reflect.construct(becoming, Object.values(args));
    }

    // Special case for BeingUser branching
    if (current.constructor.name === 'BeingUser' && Array.isArray(becoming) && becoming.length === 2) {
      // Check if the being property is a Success or Failure
      const beingUser = current as any;
      if (beingUser.being && beingUser.being.constructor) {
        if (beingUser.being.constructor.name === 'Success') {
          // Use ValidUser for Success
          const args = this.becomingArguments.invoke(current, becoming[0]);
          return Reflect.construct(becoming[0], Object.values(args));
        } else if (beingUser.being.constructor.name === 'Failure') {
          // Use ErrorResponse for Failure
          const args = this.becomingArguments.invoke(current, becoming[1]);
          return Reflect.construct(becoming[1], Object.values(args));
        }
      }
    }

    // Array case: try each possibility until one succeeds
    for (const classConstructor of becoming) {
      try {
        const args = this.becomingArguments.invoke(current, classConstructor);
        return Reflect.construct(classConstructor, Object.values(args));
      } catch (error) {
        continue; // Natural selection - try the next possibility
      }
    }

    throw new Error(`No matching class for becoming in [${becoming.map(c => c.name).join(', ')}]`);
  }
}
