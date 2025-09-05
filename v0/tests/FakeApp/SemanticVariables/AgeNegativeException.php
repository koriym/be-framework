<?php

declare(strict_types=1);

namespace Be\Framework\Fake\MyVendor\MyApp\SemanticVariables;

use Be\Framework\Attribute\Message;
use DomainException;

#[Message([
    'en' => 'Age cannot be negative: {age}',
    'ja' => '年齢は負の値にできません: {age}歳',
])]
final class AgeNegativeException extends DomainException
{
    public function __construct(
        public readonly int $age,
    ) {
        parent::__construct("Age cannot be negative: {$age}");
    }
}