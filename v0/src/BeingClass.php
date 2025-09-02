<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Attribute\Be;
use ReflectionClass;

/**
 * Gets the next class name in metamorphosis chain
 */
final class BeingClass
{
    /**
     * Get what this object is becoming
     *
     * @param object $current Current object in metamorphosis chain
     *
     * @return string|array|null Next class name(s) or null if transformation is complete
     */
    public function __invoke(object $current): string|array|null
    {
        $reflection = new ReflectionClass($current);
        $beAttributes = $reflection->getAttributes(Be::class);

        if (empty($beAttributes)) {
            return null;
        }

        $be = $beAttributes[0]->newInstance();

        return $be->being;  // Returns what this object is becoming
    }
}
