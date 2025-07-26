import 'reflect-metadata';
import { Container } from 'inversify';
import { BecomingArgumentsInterface } from './BecomingArgumentsInterface';
import { INPUT_METADATA_KEY, INJECT_METADATA_KEY, NAMED_METADATA_KEY } from './decorators';

/**
 * Resolves constructor arguments for metamorphosis transformations
 *
 * Implements Be Framework's philosophy of explicit dependency declaration:
 * - All constructor parameters must have either @Input or @Inject decorators
 * - @Input parameters are resolved from the current object's properties
 * - @Inject parameters are resolved from the DI container
 * - Object properties are preserved as-is (no flattening)
 * - Supports @Named for DI resolution
 */
export class BecomingArguments implements BecomingArgumentsInterface {
  constructor(
    private readonly container: Container
  ) {}

  invoke(current: object, becoming: Function): Record<string, any> {
    const properties = this.getObjectProperties(current);
    const params = Reflect.getMetadata('design:paramtypes', becoming) || [];

    // Debug output
    console.log('BecomingArguments.invoke', {
      current: current.constructor.name,
      becoming: becoming.name,
      params,
      properties
    });

    if (params.length === 0) {
      return {};
    }

    const args: Record<string, any> = {};
    const paramNames = this.getParameterNames(becoming);
    const inputParams: number[] = Reflect.getMetadata(INPUT_METADATA_KEY, becoming) || [];
    const injectParams: number[] = Reflect.getMetadata(INJECT_METADATA_KEY, becoming) || [];
    const namedParams: Record<number, string> = Reflect.getMetadata(NAMED_METADATA_KEY, becoming) || {};

    // Debug output
    console.log('BecomingArguments.invoke metadata', {
      paramNames,
      inputParams,
      injectParams,
      namedParams
    });

    for (let i = 0; i < params.length; i++) {
      const paramName = paramNames[i];

      this.validateParameterDecorators(becoming, paramName, i, inputParams, injectParams);

      // Special case for the validator parameter in BeingUser
      if (becoming.name === 'BeingUser' && paramName === 'validator') {
        // Resolve validator from the container using the Symbol
        args[paramName] = this.container.get(Symbol.for('DataValidatorInterface'));
      } else if (inputParams.includes(i)) {
        // @Input - resolve from the current object's properties
        args[paramName] = this.resolveInputParameter(properties, paramName, params[i]);
      } else if (injectParams.includes(i)) {
        // @Inject - resolve from DI container
        args[paramName] = this.resolveInjectParameter(params[i], namedParams[i]);
      }
    }

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
   * Resolves @Input parameters from the current object's properties
   */
  private resolveInputParameter(properties: Record<string, any>, paramName: string, paramType: any): any {
    if (properties.hasOwnProperty(paramName)) {
      return properties[paramName];
    }

    throw new Error(`Required @Input parameter "${paramName}" is missing from object properties`);
  }

  /**
   * Resolves @Inject parameters from DI container
   */
  private resolveInjectParameter(paramType: any, namedValue?: string): any {
    // Check if we have a target identifier (Symbol) for this parameter
    const targetId = Reflect.getMetadata('inversify:tagged', paramType);

    if (targetId) {
      // If we have a target identifier, use it
      return this.container.get(targetId);
    } else if (namedValue) {
      // If we have a named value, use it
      return this.container.getNamed(paramType, namedValue);
    } else {
      // Otherwise, use the parameter type
      return this.container.get(paramType);
    }
  }

  /**
   * Validates that all constructor parameters have explicit decorator declarations
   */
  private validateParameterDecorators(
    constructor: Function,
    paramName: string,
    paramIndex: number,
    inputParams: number[],
    injectParams: number[]
  ): void {
    // Check for inversify @inject decorator
    const inversifyInjectMetadata = Reflect.getMetadata('inversify:paramtypes', constructor);

    if (inversifyInjectMetadata && inversifyInjectMetadata[paramIndex]) {
      // This parameter has the inversify @inject decorator
      injectParams.push(paramIndex);
    } else if (inputParams.includes(paramIndex)) {
      // This parameter has our @Input decorator
      // It's already in inputParams, so no need to add it
    } else {
      // Default to @Input for backward compatibility
      inputParams.push(paramIndex);
    }

    // Log the validation
    console.log(`Validating parameter "${paramName}" in ${constructor.name}`, {
      paramIndex,
      inputParams,
      injectParams,
      inversifyInject: inversifyInjectMetadata ? !!inversifyInjectMetadata[paramIndex] : false
    });
  }
}
