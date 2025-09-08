<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Fake;

use ArrayAccess;

/**
 * Test fixture implementing only ArrayAccess interface
 * for testing intersection type failures
 */
final class ArrayAccessOnly implements ArrayAccess
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }
}