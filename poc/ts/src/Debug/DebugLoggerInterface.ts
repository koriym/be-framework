/**
 * Interface for debug logging in the Be Framework
 */
export interface DebugLoggerInterface {
  /**
   * Log a debug message
   */
  debug(message: string): void;

  /**
   * Dump a variable with a label
   */
  dump(label: string, variable: any): void;
}
