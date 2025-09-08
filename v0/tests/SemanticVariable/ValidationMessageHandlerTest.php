<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\Attribute\Message;
use DomainException;
use InvalidArgumentException;
use LogicException;
use MyVendor\MyApp\SemanticVariables\InvalidEmailFormatException;
use MyVendor\MyApp\SemanticVariables\NegativeAgeException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

use function fclose;
use function is_resource;
use function tmpfile;

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

    public function testGetMessageWithoutMessageAttribute(): void
    {
        // Test with a plain DomainException that has no Message attributes
        $exception = new DomainException('Plain domain error');

        $message = $this->handler->getMessage($exception, 'en');

        $this->assertSame('Plain domain error', $message);
    }

    public function testGetMessageWithInvalidLanguageCode(): void
    {
        // Test with invalid/unsupported language code
        $exception = new InvalidEmailFormatException('test@invalid');

        $message = $this->handler->getMessage($exception, 'invalid_lang');

        // Should fallback to English
        $this->assertSame('Invalid email format: test@invalid', $message);
    }

    public function testGetAllMessagesWithEmptyExceptionClass(): void
    {
        // Test getAllMessages with exception that has no Message attributes
        $exception = new RuntimeException('Runtime error');

        $messages = $this->handler->getAllMessages($exception);

        $this->assertArrayHasKey('en', $messages);
        $this->assertCount(1, $messages);
        $this->assertSame('Runtime error', $messages['en']);
    }

    public function testGetMessagesForExceptionsWithEmptyArray(): void
    {
        // Test with empty exceptions array
        $messages = $this->handler->getMessagesForExceptions([], 'en');

        $this->assertIsArray($messages);
        $this->assertEmpty($messages);
    }

    public function testGetMessagesForExceptionsWithNonDomainException(): void
    {
        // Test with non-domain exceptions
        $exceptions = [
            new InvalidArgumentException('Invalid argument'),
            new LogicException('Logic error'),
        ];

        $messages = $this->handler->getMessagesForExceptions($exceptions, 'en');

        $this->assertCount(2, $messages);
        $this->assertContains('Invalid argument', $messages);
        $this->assertContains('Logic error', $messages);
    }

    public function testTemplateInterpolationWithMissingValue(): void
    {
        // Create a domain exception that might have template issues
        $exception = new DomainException('Error with {missing_value}');

        $message = $this->handler->getMessage($exception, 'en');

        // Should return the message as-is since no template interpolation occurs for plain exceptions
        $this->assertSame('Error with {missing_value}', $message);
    }

    public function testTemplateInterpolationWithBooleanValue(): void
    {
        // Create an exception with boolean values to test the bool => string conversion
        $exception = new #[Message(['en' => 'Valid: {validFlag}, Invalid: {invalidFlag}'])]
        class (true, false) extends DomainException {
            public function __construct(
                public readonly bool $validFlag,
                public readonly bool $invalidFlag,
            ) {
                parent::__construct('Valid: {validFlag}, Invalid: {invalidFlag}');
            }
        };

        $message = $this->handler->getMessage($exception, 'en');

        $this->assertSame('Valid: true, Invalid: false', $message);
    }

    public function testTemplateInterpolationWithNullValue(): void
    {
        // Test null value interpolation
        $exception = new #[Message(['en' => 'Value is: {nullValue}'])]
        class (null) extends DomainException {
            public function __construct(public readonly string|null $nullValue)
            {
                parent::__construct('Value is: {nullValue}');
            }
        };

        $message = $this->handler->getMessage($exception, 'en');

        $this->assertSame('Value is: null', $message);
    }

    public function testTemplateInterpolationWithArrayValue(): void
    {
        // Test array value interpolation
        $exception = new #[Message(['en' => 'Array data: {testArray}'])]
        class (['test', 'array']) extends DomainException {
            public function __construct(public readonly array $testArray)
            {
                parent::__construct('Array data: {testArray}');
            }
        };

        $message = $this->handler->getMessage($exception, 'en');

        $this->assertStringContainsString('Array data: ["test","array"]', $message);
    }

    public function testTemplateInterpolationWithObjectValue(): void
    {
        // Test object value interpolation
        $testObject = new stdClass();
        $exception = new #[Message(['en' => 'Object type: {testObject}'])]
        class ($testObject) extends DomainException {
            public function __construct(public readonly object $testObject)
            {
                parent::__construct('Object type: {testObject}');
            }
        };

        $message = $this->handler->getMessage($exception, 'en');

        $this->assertSame('Object type: stdClass', $message);
    }

    public function testSafeJsonEncodeWithUnencodableArray(): void
    {
        // Create array with binary data that will cause JSON encoding to fail
        $unencodableData = ['key' => "\x00\x01\x02\x03"];

        $exception = new #[Message(['en' => 'Data: {data}'])]
        class ($unencodableData) extends DomainException {
            public function __construct(public readonly array $data)
            {
                parent::__construct('Data: {data}');
            }
        };

        $message = $this->handler->getMessage($exception, 'en');

        // Should fallback gracefully, either with partial output or fallback representations
        $this->assertIsString($message);
        $this->assertStringContainsString('Data:', $message);
    }

    public function testSafeJsonEncodeWithResourceValue(): void
    {
        // Test with resource type in template interpolation
        $resource = tmpfile();
        $exception = new #[Message(['en' => 'Resource: {resourceValue}'])]
        class ($resource) extends DomainException {
            /** @var resource */
            public readonly mixed $resourceValue;

            public function __construct($resource)
            {
                $this->resourceValue = $resource;

                parent::__construct('Resource: {resourceValue}');
            }
        };

        $message = $this->handler->getMessage($exception, 'en');

        // Should handle resource type gracefully
        $this->assertIsString($message);
        $this->assertStringContainsString('Resource:', $message);

        if (is_resource($resource)) {
            fclose($resource);
        }
    }

    public function testSafeJsonEncodeWithCircularReference(): void
    {
        // Create object with circular reference to force JSON encoding failure
        $obj1 = new stdClass();
        $obj2 = new stdClass();
        $obj1->ref = $obj2;
        $obj2->ref = $obj1;

        $circularArray = ['circular' => $obj1];

        $exception = new #[Message(['en' => 'Circular: {circularData}'])]
        class ($circularArray) extends DomainException {
            public function __construct(public readonly array $circularData)
            {
                parent::__construct('Circular: {circularData}');
            }
        };

        $message = $this->handler->getMessage($exception, 'en');

        // Should fallback gracefully when JSON encoding fails due to circular reference
        $this->assertIsString($message);
        $this->assertStringContainsString('Circular:', $message);
    }

    public function testSafeJsonEncodeVarExportFallbackFailure(): void
    {
        // Create an array that will force all JSON encoding attempts to fail
        // Use very deeply nested array that might cause var_export to fail too
        $deepArray = [];
        $current = &$deepArray;

        // Create a very deep nested structure that might cause issues
        for ($i = 0; $i < 1000; $i++) {
            $current['level_' . $i] = [];
            $current = &$current['level_' . $i];
        }

        $exception = new #[Message(['en' => 'Deep: {deepData}'])]
        class ($deepArray) extends DomainException {
            public function __construct(public readonly array $deepData)
            {
                parent::__construct('Deep: {deepData}');
            }
        };

        $message = $this->handler->getMessage($exception, 'en');

        // Should fall back to final fallback string
        $this->assertIsString($message);
        $this->assertStringContainsString('Deep:', $message);
    }
}
