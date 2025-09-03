<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use DomainException;
use MyVendor\MyApp\SemanticVariables\InvalidEmailFormatException;
use MyVendor\MyApp\SemanticVariables\NegativeAgeException;
use PHPUnit\Framework\TestCase;

final class ValidationMessageHandlerTest extends TestCase
{
    private ValidationMessageHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new ValidationMessageHandler();
    }

    public function testGetMessageInEnglish(): void
    {
        $exception = new InvalidEmailFormatException('invalid-email');

        $message = $this->handler->getMessage($exception, 'en');

        $this->assertSame('Invalid email format: invalid-email', $message);
    }

    public function testGetMessageInJapanese(): void
    {
        $exception = new InvalidEmailFormatException('invalid-email');

        $message = $this->handler->getMessage($exception, 'ja');

        $this->assertSame('無効なメール形式: invalid-email', $message);
    }

    public function testGetMessageInSpanish(): void
    {
        $exception = new InvalidEmailFormatException('invalid-email');

        $message = $this->handler->getMessage($exception, 'es');

        $this->assertSame('Formato de correo inválido: invalid-email', $message);
    }

    public function testGetMessageWithNonExistentLocale(): void
    {
        $exception = new InvalidEmailFormatException('invalid-email');

        $message = $this->handler->getMessage($exception, 'fr');

        // Should fallback to English
        $this->assertSame('Invalid email format: invalid-email', $message);
    }

    public function testGetAllMessages(): void
    {
        $exception = new NegativeAgeException(-5);

        $messages = $this->handler->getAllMessages($exception);

        $this->assertArrayHasKey('en', $messages);
        $this->assertArrayHasKey('ja', $messages);
        $this->assertArrayHasKey('es', $messages);
        $this->assertSame('Age cannot be negative: -5 years old', $messages['en']);
        $this->assertSame('年齢は負の値にできません: -5歳', $messages['ja']);
        $this->assertSame('La edad no puede ser negativa: -5 años', $messages['es']);
    }

    public function testTemplateInterpolation(): void
    {
        $exception = new NegativeAgeException(-10);

        $message = $this->handler->getMessage($exception, 'en');

        $this->assertSame('Age cannot be negative: -10 years old', $message);
    }

    public function testGetAllMessagesWithoutAttributes(): void
    {
        $exception = new DomainException('Simple error message');

        $messages = $this->handler->getAllMessages($exception);

        $this->assertArrayHasKey('en', $messages);
        $this->assertCount(1, $messages);
        $this->assertSame('Simple error message', $messages['en']);
    }

    public function testGetMessagesForExceptions(): void
    {
        $exceptions = [
            new InvalidEmailFormatException('invalid@'),
            new NegativeAgeException(-5),
        ];

        $messages = $this->handler->getMessagesForExceptions($exceptions, 'en');

        $this->assertCount(2, $messages);
        $this->assertContains('Invalid email format: invalid@', $messages);
        $this->assertContains('Age cannot be negative: -5 years old', $messages);
    }
}
