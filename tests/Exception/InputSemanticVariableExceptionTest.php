<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use Be\Framework\SemanticVariable\Errors;
use DomainException;
use PHPUnit\Framework\TestCase;

final class InputSemanticVariableExceptionTest extends TestCase
{
    public function testIsSemanticVariableException(): void
    {
        $errors = new Errors([new DomainException('Invalid input')]);
        $exception = new InputSemanticVariableException($errors);

        $this->assertInstanceOf(SemanticVariableException::class, $exception);
    }

    public function testGetErrorsAndMessage(): void
    {
        $errors = new Errors([new DomainException('Invalid email format')]);
        $exception = new InputSemanticVariableException($errors);

        $this->assertSame('Invalid email format', $exception->getMessage());
        $this->assertSame($errors, $exception->getErrors());
    }

    public function testPreviousIsChained(): void
    {
        $errors = new Errors([new DomainException('Invalid input')]);
        $previous = new SemanticVariableException($errors);

        $exception = new InputSemanticVariableException($errors, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
