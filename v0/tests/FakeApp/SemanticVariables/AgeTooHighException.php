<?php

declare(strict_types=1);

namespace Be\Framework\Fake\MyVendor\MyApp\SemanticVariables;

use Be\Framework\Attribute\Message;
use DomainException;

#[Message([
    'en' => 'Age cannot exceed 150: {age}',
    'ja' => '年齢は150を超えることはできません: {age}歳',
])]
final class AgeTooHighException extends DomainException
{
    public function __construct(
        public readonly int $age,
    ) {
        parent::__construct("Age cannot exceed 150: {$age}");
    }
}