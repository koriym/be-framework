<?php

declare(strict_types=1);

namespace Be\Framework\Attribute;

use Attribute;

/**
 * Declares what this object can become
 *
 * Examples:
 * - #[Be(NextStage::class)] - Linear transformation
 * - #[Be([SuccessPath::class, FailurePath::class])] - Type-driven branching
 *
 * When array is used, the actual becoming is determined by the framework
 * based on the object's internal state and type matching.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Be
{
    /**
     * @param class-string|array<class-string> $being What this object can become
     *                                                Single string for linear transformation
     *                                                Array for type-driven branching
     */
    public function __construct(
        public readonly string|array $being,
    ) {
    }
}
