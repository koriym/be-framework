<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Attribute\Be;

#[Be([ClassA::class, ClassB::class])]
final class TestMultipleDestination
{
    public function __construct()
    {
    }
}

final class ClassA {}
final class ClassB {}