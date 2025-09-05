<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use PHPUnit\Framework\TestCase;

final class TypeMatchingFailureTest extends TestCase
{
    public function testCreateWithoutCandidateErrors(): void
    {
        $candidates = ['Class1', 'Class2', 'Class3'];
        $exception = TypeMatchingFailure::create($candidates, []);

        $this->assertSame('No matching class for becoming in [Class1, Class2, Class3]', $exception->getMessage());
        $this->assertEmpty($exception->getCandidateErrors());
    }

    public function testCreateWithCandidateErrors(): void
    {
        $candidates = ['Class1', 'Class2'];
        $candidateErrors = [
            'Class1' => 'Constructor parameter type mismatch',
            'Class2' => 'Missing required parameter',
        ];

        $exception = TypeMatchingFailure::create($candidates, $candidateErrors);

        $expectedMessage = "No matching class for becoming in [Class1, Class2]\n\n" .
                          "Candidate failures:\n" .
                          "  - Class1: Constructor parameter type mismatch\n" .
                          '  - Class2: Missing required parameter';

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($candidateErrors, $exception->getCandidateErrors());
    }

    public function testGetCandidateErrors(): void
    {
        $candidates = ['TestClass'];
        $candidateErrors = ['TestClass' => 'Test error message'];

        $exception = TypeMatchingFailure::create($candidates, $candidateErrors);
        $retrievedErrors = $exception->getCandidateErrors();

        $this->assertSame($candidateErrors, $retrievedErrors);
        $this->assertArrayHasKey('TestClass', $retrievedErrors);
        $this->assertSame('Test error message', $retrievedErrors['TestClass']);
    }
}
