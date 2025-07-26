import 'reflect-metadata';
import { BecomingArgumentsInterface } from './BecomingArgumentsInterface';
import { BecomingArguments } from './BecomingArguments';
import { DebugLoggerInterface } from './Debug/DebugLoggerInterface';
import { EchoDebugLogger } from './Debug/EchoDebugLogger';
import { INPUT_METADATA_KEY, INJECT_METADATA_KEY } from './decorators';

/**
 * Debug version of BecomingArguments with verbose logging
 *
 * Uses composition to wrap BecomingArguments and add debug logging
 * without modifying the original class or requiring inheritance.
 */
export class DebugBecomingArguments implements BecomingArgumentsInterface {
  constructor(
    private readonly becomingArguments: BecomingArguments,
    private readonly logger: DebugLoggerInterface = new EchoDebugLogger()
  ) {}

  invoke(current: object, becoming: Function): Record<string, any> {
    this.logger.debug("\n=== DebugBecomingArguments ===");
    this.logger.debug(`Current object: ${current.constructor.name}`);
    this.logger.debug(`Becoming: ${becoming.name}`);

    const properties = this.getObjectProperties(current);
    this.logger.dump('Available properties', properties);

    const params = Reflect.getMetadata('design:paramtypes', becoming) || [];

    if (params.length === 0) {
      this.logger.debug('No constructor parameters found');
      return {};
    }

    this.logger.debug("\nProcessing constructor parameters:");

    // Log parameter details before processing
    const paramNames = this.getParameterNames(becoming);
    for (let i = 0; i < params.length; i++) {
      this.logParameterDetails(becoming, paramNames[i], i, properties);
    }

    // Delegate to the original BecomingArguments for actual processing
    const args = this.becomingArguments.invoke(current, becoming);

    this.logger.dump('Final resolved args', args);
    this.logger.debug('=== End Debug ===\n');

    return args;
  }

  /**
   * Gets all properties of an object
   */
  private getObjectProperties(obj: object): Record<string, any> {
    return Object.getOwnPropertyNames(obj)
      .filter(prop => prop !== 'constructor')
      .reduce((props, prop) => {
        props[prop] = (obj as any)[prop];
        return props;
      }, {} as Record<string, any>);
  }

  /**
   * Gets parameter names from a constructor function
   */
  private getParameterNames(func: Function): string[] {
    const fnStr = func.toString();
    const result = fnStr.slice(fnStr.indexOf('(')+1, fnStr.indexOf(')')).match(/([^\s,]+)/g);
    return result || [];
  }

  /**
   * Logs details about a parameter
   */
  private logParameterDetails(
    constructor: Function,
    paramName: string,
    paramIndex: number,
    properties: Record<string, any>
  ): void {
    this.logger.debug(`- Parameter: ${paramName}`);

    const inputParams: number[] = Reflect.getMetadata(INPUT_METADATA_KEY, constructor) || [];
    const injectParams: number[] = Reflect.getMetadata(INJECT_METADATA_KEY, constructor) || [];

    const hasInput = inputParams.includes(paramIndex);
    const hasInject = injectParams.includes(paramIndex);

    this.logger.debug(`  @Input: ${hasInput ? 'Yes' : 'No'}`);
    this.logger.debug(`  @Inject: ${hasInject ? 'Yes' : 'No'}`);

    if (hasInput) {
      if (properties.hasOwnProperty(paramName)) {
        this.logger.dump('  Available in properties', properties[paramName]);
      } else {
        this.logger.debug('  ERROR: Required parameter missing!');
      }
    } else {
      this.logger.debug('  Will be resolved from DI container');
    }
  }
}
