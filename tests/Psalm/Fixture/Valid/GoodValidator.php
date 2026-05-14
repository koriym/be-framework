<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Psalm\Fixture\Valid;

use Be\Framework\Attribute\Validate;
use DomainException;

use function filter_var;
use function strlen;

use const FILTER_VALIDATE_EMAIL;

final class GoodValidator
{
    #[Validate]
    public function validateEmail(string $email): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException("Invalid email format: {$email}");
        }
    }

    #[Validate]
    public function validateLength(string $name): void
    {
        if (strlen($name) === 0) {
            throw new InvalidNameException('Name must not be empty');
        }
    }
}

final class InvalidNameException extends DomainException
{
}
