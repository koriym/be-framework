<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Attribute\Message;
use Exception;
use ReflectionClass;

use function array_map;
use function get_object_vars;
use function str_replace;

/**
 * Handles multilingual message generation for validation exceptions
 */
final class ValidationMessageHandler
{
    /**
     * Generate localized message for validation exception
     */
    public function getMessage(Exception $exception, string $locale = 'en'): string
    {
        $reflection = new ReflectionClass($exception);
        $messageAttributes = $reflection->getAttributes(Message::class);
        
        if (empty($messageAttributes)) {
            return $exception->getMessage() ?: 'Validation error';
        }
        
        $messageAttribute = $messageAttributes[0]->newInstance();
        $template = $messageAttribute->messages[$locale] 
                   ?? $messageAttribute->messages['en'] 
                   ?? 'Validation error';
        
        return $this->interpolateTemplate($template, $exception);
    }
    
    /**
     * Get all available messages for exception
     * 
     * @return array<string, string>
     */
    public function getAllMessages(Exception $exception): array
    {
        $reflection = new ReflectionClass($exception);
        $messageAttributes = $reflection->getAttributes(Message::class);
        
        if (empty($messageAttributes)) {
            return ['en' => $exception->getMessage() ?: 'Validation error'];
        }
        
        $messageAttribute = $messageAttributes[0]->newInstance();
        $messages = [];
        
        foreach ($messageAttribute->messages as $locale => $template) {
            $messages[$locale] = $this->interpolateTemplate($template, $exception);
        }
        
        return $messages;
    }
    
    /**
     * Interpolate template with exception properties
     */
    private function interpolateTemplate(string $template, object $exception): string
    {
        $properties = get_object_vars($exception);
        
        foreach ($properties as $key => $value) {
            $placeholder = "{{$key}}";
            $template = str_replace($placeholder, (string)$value, $template);
        }
        
        return $template;
    }
    
    /**
     * Get messages for multiple exceptions
     * 
     * @param array<Exception> $exceptions
     * @return array<string>
     */
    public function getMessagesForExceptions(array $exceptions, string $locale = 'en'): array
    {
        return array_map(
            fn(Exception $exception) => $this->getMessage($exception, $locale),
            $exceptions
        );
    }
}