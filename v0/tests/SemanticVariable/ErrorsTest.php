<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use DomainException;
use PHPUnit\Framework\TestCase;

final class ErrorsTest extends TestCase
{
    public function testGetMessages(): void
    {
        $exception1 = new DomainException('Error 1');
        $exception2 = new DomainException('Error 2');
        $errors = new Errors([$exception1, $exception2]);

        $messages = $errors->getMessages();

        $this->assertCount(2, $messages);
        $this->assertContains('Error 1', $messages);
        $this->assertContains('Error 2', $messages);
    }

    public function testGetMessagesWithLocale(): void
    {
        $exception = new DomainException('Error message');
        $errors = new Errors([$exception]);

        $messages = $errors->getMessages('ja');

        $this->assertCount(1, $messages);
        $this->assertContains('Error message', $messages);
    }

    public function testCombine(): void
    {
        $exception1 = new DomainException('Error 1');
        $exception2 = new DomainException('Error 2');
        $exception3 = new DomainException('Error 3');

        $errors1 = new Errors([$exception1, $exception2]);
        $errors2 = new Errors([$exception3]);

        $combined = $errors1->combine($errors2);

        $this->assertCount(3, $combined->exceptions);
        $this->assertSame($exception1, $combined->exceptions[0]);
        $this->assertSame($exception2, $combined->exceptions[1]);
        $this->assertSame($exception3, $combined->exceptions[2]);
    }

    public function testCombineWithEmptyErrors(): void
    {
        $exception = new DomainException('Error');
        $errors1 = new Errors([$exception]);
        $errors2 = new Errors([]);

        $combined = $errors1->combine($errors2);

        $this->assertCount(1, $combined->exceptions);
        $this->assertSame($exception, $combined->exceptions[0]);
    }
}
