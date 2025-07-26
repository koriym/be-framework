import { DebugLoggerInterface } from './DebugLoggerInterface';

/**
 * Simple debug logger that outputs to console
 */
export class EchoDebugLogger implements DebugLoggerInterface {
  /**
   * Log a debug message
   */
  debug(message: string): void {
    console.log(message);
  }

  /**
   * Dump a variable with a label
   */
  dump(label: string, variable: any): void {
    console.log(`${label}:`, variable);
  }
}
