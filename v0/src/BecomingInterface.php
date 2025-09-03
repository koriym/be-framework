<?php

declare(strict_types=1);

namespace Be\Framework;

/**
 * The essence of becoming - the contract for continuous metamorphosis
 *
 * This interface defines the fundamental operation of ontological transformation:
 * an object enters, undergoes continuous becoming, and emerges transformed.
 */
interface BecomingInterface
{
    /**
     * Orchestrate continuous metamorphosis until transformation completes
     *
     * @param object $input The initial state entering the flow of becoming
     *
     * @return object The final form after all transformations
     */
    public function __invoke(object $input): object;
}