<?php

declare(strict_types=1);

namespace Be\Framework;

#[Be([ClassA::class, ClassB::class])]
final class TestMultipleDestination
{
    public function __construct()
    {
    }
}

final class ClassA {}
final class ClassB {}