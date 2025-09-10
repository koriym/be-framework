<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use PHPUnit\Framework\TestCase;

final class BeMatchExceptionTest extends TestCase
{
    public function testCreateWithoutCandidateErrors(): void
    {
        $candidates = ['Class1', 'Class2', 'Class3'];
        $exception = new BeMatchException($candidates, []);

        $this->assertSame('No matching class for becoming in [Class1, Class2, Class3]', $exception->getMessage());
        $this->assertEmpty($exception->getCandidateErrors());
    }

    public function testCreateWithCandidateErrors(): void
    {
        $candidates = ['Class1', 'Class2'];
        $unmatches = [
            new Unmatch('Class1', UnmatchReason::TypeMismatch),
            new Unmatch('Class2', UnmatchReason::Constructor, 'Missing required parameter'),
        ];

        $exception = new BeMatchException($candidates, $unmatches);

        $expectedMessage = "No matching class for becoming in [Class1, Class2]\n\n" .
                          "Candidate unmatches:\n" .
                          "  - Type mismatch in Class1\n" .
                          '  - Constructor error in Class2: Missing required parameter';

        $this->assertSame($expectedMessage, $exception->getMessage());

        // Test structured unmatches
        $actualUnmatches = $exception->getUnmatches();
        $this->assertCount(2, $actualUnmatches);
        $this->assertSame('Class1', $actualUnmatches[0]->className);
        $this->assertSame(UnmatchReason::TypeMismatch, $actualUnmatches[0]->reason);
        $this->assertSame('Class2', $actualUnmatches[1]->className);
        $this->assertSame(UnmatchReason::Constructor, $actualUnmatches[1]->reason);

        // Test legacy methods
        $legacyErrors = $exception->getCandidateErrors();
        $this->assertArrayHasKey('Class1', $legacyErrors);
        $this->assertArrayHasKey('Class2', $legacyErrors);
    }

    public function testGetCandidateErrors(): void
    {
        $candidates = ['TestClass'];
        $unmatches = [
            new Unmatch('TestClass', UnmatchReason::Validation, 'Test validation failed'),
        ];

        $exception = new BeMatchException($candidates, $unmatches);
        $retrievedErrors = $exception->getCandidateErrors();

        $this->assertArrayHasKey('TestClass', $retrievedErrors);
        $this->assertSame('Validation error in TestClass: Test validation failed', $retrievedErrors['TestClass']);

        // Test structured access
        $actualUnmatches = $exception->getUnmatches();
        $this->assertCount(1, $actualUnmatches);
        $this->assertSame('TestClass', $actualUnmatches[0]->className);
        $this->assertSame(UnmatchReason::Validation, $actualUnmatches[0]->reason);
        $this->assertSame('Test validation failed', $actualUnmatches[0]->details);
    }
}
