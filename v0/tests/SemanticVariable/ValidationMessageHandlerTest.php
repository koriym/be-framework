<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\SemanticVariable\ValidationMessageHandler;
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
        $this->assertSame('Age cannot be negative: -5', $messages['en']);
        $this->assertSame('年齢は負の値にできません: -5', $messages['ja']);
        $this->assertSame('La edad no puede ser negativa: -5', $messages['es']);
    }
    
    public function testTemplateInterpolation(): void
    {
        $exception = new NegativeAgeException(-10);
        
        $message = $this->handler->getMessage($exception, 'en');
        
        $this->assertSame('Age cannot be negative: -10', $message);
    }
}