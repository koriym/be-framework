<?php

declare(strict_types=1);

namespace Koriym\NullObject;

final class GeneratedCode
{
    /** @var class-string */
    public $class;

    /** @var string */
    public $code;

    /** @param class-string $class */
    public function __construct(string $class, string $code)
    {
        $this->class = $class;
        $this->code = $code;
    }
}
