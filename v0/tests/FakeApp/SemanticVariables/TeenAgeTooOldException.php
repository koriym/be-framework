<?php

declare(strict_types=1);

namespace MyVendor\MyApp\SemanticVariables;

use Be\Framework\Attribute\Message;
use DomainException;

#[Message([
    'en' => 'Teen age must be at most 19: {age}',
    'ja' => 'ティーンエイジャーは最大19歳でなければなりません: {age}歳',
])]
final class TeenAgeTooOldException extends DomainException
{
    public function __construct(
        public readonly int $age,
    ) {
        parent::__construct("Teen age must be at most 19 years: {$age}");
    }
}
