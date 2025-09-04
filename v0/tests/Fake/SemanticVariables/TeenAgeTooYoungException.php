<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariables;

use Be\Framework\Attribute\Message;
use DomainException;

#[Message([
    'en' => 'Teen age must be at least 13: {age}',
    'ja' => 'ティーンエイジャーは最低13歳でなければなりません: {age}歳',
])]
final class TeenAgeTooYoungException extends DomainException
{
    public function __construct(
        public readonly int $age,
    ) {
        parent::__construct("Teen age must be at least 13 years: {$age}");
    }
}