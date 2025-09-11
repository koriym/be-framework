# Error Handling: Implementation Guide

This reference provides comprehensive examples of semantic exception handling and error recovery patterns.

## Exception Hierarchy Design

```php
namespace App\Exception;

// Base domain exception
abstract class DomainException extends Exception
{
    public function __construct(
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

// User-related exceptions
abstract class UserException extends DomainException {}

final class EmptyNameException extends UserException {}
final class InvalidNameFormatException extends UserException {}
final class NameTooLongException extends UserException {}

// Email-related exceptions
abstract class EmailException extends DomainException {}

final class InvalidEmailFormatException extends EmailException {}
final class EmailAlreadyExistsException extends EmailException {}
final class DisposableEmailException extends EmailException {}
final class EmailDomainBlacklistedException extends EmailException {}

// Age-related exceptions
abstract class AgeException extends DomainException {}

final class NegativeAgeException extends AgeException {}
final class AgeTooHighException extends AgeException {}
final class AgeTooYoungException extends AgeException {}
final class AgeNotAllowedException extends AgeException {}

// Payment-related exceptions
abstract class PaymentException extends DomainException {}

final class InsufficientFundsException extends PaymentException {}
final class InvalidPaymentMethodException extends PaymentException {}
final class PaymentTimeoutException extends PaymentException {}
final class FraudDetectedException extends PaymentException {}
```

## Rich Exception Context

```php
final class PaymentValidationException extends PaymentException
{
    public function __construct(
        public readonly Money $attemptedAmount,
        public readonly Money $availableBalance,
        public readonly PaymentMethod $method,
        public readonly DateTime $attemptedAt,
        public readonly string $transactionId,
        public readonly ?string $declineCode = null
    ) {
        $message = "Payment of {$attemptedAmount->getDisplayAmount()} failed. " .
                  "Available balance: {$availableBalance->getDisplayAmount()}. " .
                  "Method: {$method->getDisplayName()}. " .
                  "Transaction: {$transactionId}";
                  
        if ($declineCode) {
            $message .= " (Code: {$declineCode})";
        }
        
        parent::__construct($message);
    }
    
    public function getInsightfulData(): array
    {
        return [
            'attempted_amount' => $this->attemptedAmount->toArray(),
            'available_balance' => $this->availableBalance->toArray(),
            'shortfall' => $this->attemptedAmount->subtract($this->availableBalance)->toArray(),
            'method_type' => $this->method->getType(),
            'decline_reason' => $this->declineCode,
            'retry_allowed' => $this->method->allowsRetry(),
        ];
    }
}
```

## Multilingual Error Messages

```php
#[Message([
    'en' => 'Name cannot be empty.',
    'ja' => '名前は空にできません。',
    'es' => 'El nombre no puede estar vacío.',
    'fr' => 'Le nom ne peut pas être vide.',
    'de' => 'Der Name darf nicht leer sein.',
    'zh' => '姓名不能为空。'
])]
final class EmptyNameException extends UserException {}

#[Message([
    'en' => 'Email "{email}" is already registered.',
    'ja' => 'メール「{email}」は既に登録されています。',
    'es' => 'El email "{email}" ya está registrado.',
    'fr' => 'L\'email "{email}" est déjà enregistré.'
])]
final class EmailAlreadyExistsException extends EmailException
{
    public function __construct(public readonly string $email)
    {
        parent::__construct("Email \"{$email}\" is already registered.");
    }
}

#[Message([
    'en' => 'Age must be between {min} and {max} years. Provided: {actual}',
    'ja' => '年齢は{min}歳から{max}歳の間でなければなりません。入力値: {actual}',
    'es' => 'La edad debe estar entre {min} y {max} años. Proporcionado: {actual}'
])]
final class AgeOutOfRangeException extends AgeException
{
    public function __construct(
        public readonly int $actual,
        public readonly int $min = 0,
        public readonly int $max = 150
    ) {
        parent::__construct("Age must be between {$min} and {$max} years. Provided: {$actual}");
    }
}
```

## Error Collection and Handling

```php
// Complex validation with error collection
final class UserRegistrationValidator
{
    public function __construct(
        #[Input] string $name,           // EmptyNameException, InvalidNameFormatException
        #[Input] string $email,          // InvalidEmailFormatException, EmailAlreadyExistsException
        #[Input] int $age,               // NegativeAgeException, AgeTooYoungException
        #[Input] string $password,       // WeakPasswordException
        #[Input] ?string $phone = null,  // InvalidPhoneFormatException (optional)
        #[Inject] UserRepository $repository,
        #[Inject] ValidationService $validator
    ) {
        // Framework automatically collects all validation errors
        // If any semantic variable validation fails, all errors are collected
        // and thrown as a single SemanticVariableException
    }
}

// Error handling in application
try {
    $validator = $becoming(new UserRegistrationValidator(
        $data['name'],
        $data['email'], 
        $data['age'],
        $data['password'],
        $data['phone'] ?? null
    ));
} catch (SemanticVariableException $e) {
    $errors = $e->getErrors();
    
    // Get all error messages in user's language
    $userLanguage = $this->getUserLanguage();
    $messages = $errors->getMessages($userLanguage);
    
    // Log detailed error information for debugging
    $this->logger->error('User registration validation failed', [
        'errors' => $errors->toArray(),
        'input_data' => $this->sanitizeForLogging($data),
        'user_ip' => $request->getClientIp(),
        'user_agent' => $request->getUserAgent()
    ]);
    
    // Return user-friendly response
    return new JsonResponse([
        'success' => false,
        'errors' => $messages,
        'error_count' => count($errors->exceptions)
    ], 400);
}
```

## Error Recovery Patterns

```php
#[Be([ValidatedUser::class, InvalidUser::class, SuspiciousUser::class])]
final class UserValidationResult
{
    public readonly ValidatedUser|InvalidUser|SuspiciousUser $being;
    
    public function __construct(
        #[Input] string $name,
        #[Input] string $email,
        #[Input] int $age,
        #[Input] string $ipAddress,
        #[Inject] ValidationService $validator,
        #[Inject] FraudDetection $fraud
    ) {
        try {
            // Attempt validation
            $validatedData = $validator->validateAll($name, $email, $age);
            
            // Check for suspicious activity
            $fraudScore = $fraud->analyzeRegistration($email, $ipAddress);
            
            if ($fraudScore->isHigh()) {
                $this->being = new SuspiciousUser($validatedData, $fraudScore);
            } else {
                $this->being = new ValidatedUser($validatedData);
            }
            
        } catch (ValidationException $e) {
            $this->being = new InvalidUser($e->getErrors(), $e->getContext());
        }
    }
}

// Different handling based on validation result
$result = $becoming(new UserValidationResult($data));

match (true) {
    $result->being instanceof ValidatedUser => $this->createUser($result->being),
    $result->being instanceof SuspiciousUser => $this->flagForReview($result->being),
    $result->being instanceof InvalidUser => $this->returnValidationErrors($result->being),
};
```

## Testing Error Conditions

```php
class UserValidationTest extends TestCase
{
    public function testEmptyNameThrowsException(): void
    {
        $this->expectException(SemanticVariableException::class);
        
        $becoming = new Becoming();
        $becoming(new UserRegistrationValidator('', 'test@example.com', 25, 'password123'));
    }
    
    public function testCollectsAllValidationErrors(): void
    {
        try {
            $becoming = new Becoming();
            $becoming(new UserRegistrationValidator('', 'invalid-email', -5, '123'));
            
            $this->fail('Expected SemanticVariableException');
        } catch (SemanticVariableException $e) {
            $errors = $e->getErrors();
            
            $this->assertCount(4, $errors->exceptions);
            $this->assertInstanceOf(EmptyNameException::class, $errors->exceptions[0]);
            $this->assertInstanceOf(InvalidEmailFormatException::class, $errors->exceptions[1]);
            $this->assertInstanceOf(NegativeAgeException::class, $errors->exceptions[2]);
            $this->assertInstanceOf(WeakPasswordException::class, $errors->exceptions[3]);
        }
    }
    
    public function testMultilingualErrorMessages(): void
    {
        try {
            $becoming = new Becoming();
            $becoming(new UserRegistrationValidator('', 'test@example.com', 25, 'password123'));
        } catch (SemanticVariableException $e) {
            $errors = $e->getErrors();
            
            $englishMessages = $errors->getMessages('en');
            $japaneseMessages = $errors->getMessages('ja');
            $spanishMessages = $errors->getMessages('es');
            
            $this->assertContains('Name cannot be empty.', $englishMessages);
            $this->assertContains('名前は空にできません。', $japaneseMessages);
            $this->assertContains('El nombre no puede estar vacío.', $spanishMessages);
        }
    }
    
    public function testErrorContextInformation(): void
    {
        try {
            $paymentGateway = $this->createMock(PaymentGateway::class);
            $paymentGateway->method('process')->willThrowException(
                new PaymentValidationException(
                    Money::USD(1000),
                    Money::USD(500), 
                    new CreditCardPaymentMethod('****1234'),
                    new DateTime(),
                    'TXN-123456',
                    'INSUFFICIENT_FUNDS'
                )
            );
            
            // Test that rich context is preserved
        } catch (PaymentValidationException $e) {
            $context = $e->getInsightfulData();
            
            $this->assertEquals(1000, $context['attempted_amount']['cents']);
            $this->assertEquals(500, $context['available_balance']['cents']);
            $this->assertEquals(500, $context['shortfall']['cents']);
            $this->assertEquals('INSUFFICIENT_FUNDS', $context['decline_reason']);
        }
    }
}
```

## Semantic Logging Integration

```php
// Automatic logging of validation failures
{
    "timestamp": "2024-03-15T10:30:00Z",
    "level": "error",
    "event": "metamorphosis_failed",
    "source_class": "UserRegistrationValidator",
    "destination_class": null,
    "errors": [
        {
            "exception": "EmptyNameException",
            "message": "Name cannot be empty",
            "field": "name",
            "value": "",
            "validation_context": "user_registration"
        },
        {
            "exception": "InvalidEmailFormatException", 
            "message": "Email format is invalid",
            "field": "email",
            "value": "not-an-email",
            "validation_context": "user_registration"
        }
    ],
    "input_context": {
        "user_ip": "192.168.1.100",
        "user_agent": "Mozilla/5.0...",
        "request_id": "req-12345",
        "session_id": "sess-67890"
    },
    "error_count": 2,
    "validation_duration_ms": 15
}
```

## Production Error Handling

```php
// Environment-specific error handling
class ErrorHandler
{
    public function handleSemanticVariableException(
        SemanticVariableException $e,
        Request $request
    ): Response {
        $errors = $e->getErrors();
        $userLanguage = $this->detectUserLanguage($request);
        
        if ($this->app->environment('production')) {
            // Production: User-friendly messages only
            return new JsonResponse([
                'success' => false,
                'errors' => $errors->getMessages($userLanguage),
                'error_id' => $this->generateErrorId()
            ], 400);
        } else {
            // Development: Detailed debugging information
            return new JsonResponse([
                'success' => false,
                'errors' => $errors->getMessages($userLanguage),
                'debug' => [
                    'exceptions' => $errors->toArray(),
                    'stack_trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'input_data' => $request->all()
                ]
            ], 400);
        }
    }
    
    private function generateErrorId(): string
    {
        return 'ERR-' . strtoupper(bin2hex(random_bytes(4)));
    }
    
    private function detectUserLanguage(Request $request): string
    {
        return $request->getPreferredLanguage(['en', 'ja', 'es', 'fr']) ?? 'en';
    }
}
```