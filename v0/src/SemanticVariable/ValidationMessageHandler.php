<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\Attribute\Message;
use Exception;
use JsonException;
use ReflectionClass;
use Throwable;

use function array_map;
use function get_object_vars;
use function is_array;
use function is_bool;
use function is_numeric;
use function is_object;
use function is_string;
use function json_encode;
use function str_replace;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;
use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_PARTIAL_OUTPUT_ON_ERROR;

/**
 * Handles multilingual message generation for validation exceptions
 */
final class ValidationMessageHandler
{
    /**
     * Generate localized message for validation exception
     */
    public function getMessage(Throwable $exception, string $locale = 'en'): string
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
    public function getAllMessages(Throwable $exception): array
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

        /** @psalm-suppress MixedAssignment */
        foreach ($properties as $key => $value) {
            $placeholder = "{{$key}}";
            $stringValue = match (true) {
                is_string($value) => $value,
                is_numeric($value) => (string) $value,
                is_bool($value) => $value ? 'true' : 'false',
                $value === null => 'null',
                is_array($value) => $this->safeJsonEncode($value),
                is_object($value) => $value::class,
                default => get_debug_type($value)
            };
            $template = str_replace($placeholder, $stringValue, $template);
        }

        return $template;
    }

    /**
     * Get messages for multiple exceptions
     *
     * @param array<Exception> $exceptions
     *
     * @return array<string>
     */
    public function getMessagesForExceptions(array $exceptions, string $locale = 'en'): array
    {
        return array_map(
            fn (Throwable $exception) => $this->getMessage($exception, $locale),
            $exceptions,
        );
    }

    /**
     * Safely encode array as JSON, falling back to alternatives if encoding fails
     */
    private function safeJsonEncode(array $value): string
    {
        try {
            return json_encode(
                $value,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        } catch (JsonException) {
            // First fallback: try with partial output on error
            $fallback = json_encode($value, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE);
            if ($fallback !== false) {
                return $fallback;
            }
            
            // Second fallback: try var_export
            try {
                return var_export($value, true);
            } catch (Throwable) {
                // Final fallback: simple description
                return '[unencodable array]';
            }
        }
    }
}
