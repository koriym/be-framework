<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use Be\Framework\SemanticVariable\Errors;
use DomainException;
use PHPUnit\Framework\TestCase;

final class SemanticVariableExceptionTest extends TestCase
{
    public function testSingleError(): void
    {
        $domainException = new DomainException('Age must be positive');
        $errors = new Errors([$domainException]);

        $exception = new SemanticVariableException($errors);

        $this->assertSame('Age must be positive', $exception->getMessage());
        $this->assertSame($errors, $exception->getErrors());
    }

    public function testMultipleErrors(): void
    {
        $errors = new Errors([
            new DomainException('Age must be positive'),
            new DomainException('Name cannot be empty'),
        ]);

        $exception = new SemanticVariableException($errors);

        $this->assertSame(
            'Multiple semantic validation errors: Age must be positive, Name cannot be empty',
            $exception->getMessage(),
        );
        $this->assertSame($errors, $exception->getErrors());
    }

    public function testGetErrorsReturnsSameInstance(): void
    {
        $errors = new Errors([new DomainException('Test error')]);
        $exception = new SemanticVariableException($errors);

        $this->assertSame($errors, $exception->getErrors());
        $this->assertSame($errors, $exception->getErrors());
    }
}
