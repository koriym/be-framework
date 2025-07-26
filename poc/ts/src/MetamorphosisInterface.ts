/**
 * Core metamorphosis engine interface
 *
 * This is the fundamental contract of Be Framework.
 * Objects transform through a chain of metamorphoses until reaching their final form.
 */
export interface MetamorphosisInterface {
  /**
   * Execute metamorphosis chain starting from input object
   *
   * @param input The starting object of metamorphosis chain
   * @returns The final transformed object
   */
  invoke(input: object): object;
}
