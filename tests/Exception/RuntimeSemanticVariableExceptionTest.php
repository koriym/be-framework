<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use Be\Framework\SemanticVariable\Errors;
use DomainException;
use PHPUnit\Framework\TestCase;

final class RuntimeSemanticVariableExceptionTest extends TestCase
{
    public function testIsSemanticVariableException(): void
    {
        $errors = new Errors([new DomainException('Internal inconsistency')]);
        $exception = new RuntimeSemanticVariableException($errors);

        $this->assertInstanceOf(SemanticVariableException::class, $exception);
    }

    public function testGetErrorsAndMessage(): void
    {
        $errors = new Errors([new DomainException('Invalid email format')]);
        $exception = new RuntimeSemanticVariableException($errors);

        $this->assertSame('Invalid email format', $exception->getMessage());
        $this->assertSame($errors, $exception->getErrors());
    }

    public function testPreviousIsChained(): void
    {
        $errors = new Errors([new DomainException('Internal inconsistency')]);
        $previous = new SemanticVariableException($errors);

        $exception = new RuntimeSemanticVariableException($errors, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
