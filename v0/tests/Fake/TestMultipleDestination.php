<?php

declare(strict_types=1);

namespace Be\Framework;

#[Be(['ClassA', 'ClassB'])]
final class TestMultipleDestination
{
    public function __construct()
    {
    }
}