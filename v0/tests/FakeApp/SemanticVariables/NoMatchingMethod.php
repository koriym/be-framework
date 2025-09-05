<?php

declare(strict_types=1);

namespace MyVendor\MyApp\SemanticVariables;

use Be\Framework\Attribute\Validate;
use DomainException;

/**
 * Test fixture class for validating the "no matching method" scenario
 * 
 * This class intentionally has validation methods that don't match 
 * the single-argument signature used in the test, causing no methods
 * to be selected for validation.
 */
final class NoMatchingMethod
{
    /**
     * Method that requires two parameters - won't match single argument call
     */
    #[Validate]
    public function validateNoMatchingMethodTwoArgs(string $first, string $second): void
    {
        if (empty($first) || empty($second)) {
            throw new DomainException('Both arguments are required');
        }
    }

    /**
     * Method that requires three parameters - won't match single argument call
     */
    #[Validate]
    public function validateNoMatchingMethodThreeArgs(string $first, string $second, string $third): void
    {
        if (empty($first) || empty($second) || empty($third)) {
            throw new DomainException('All three arguments are required');
        }
    }

    /**
     * Method without validation attribute - should be ignored
     */
    public function someOtherMethod(string $arg): void
    {
        // This method doesn't have #[Validate] so it won't be considered
    }
}