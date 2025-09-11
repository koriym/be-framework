# Semantic Variables: Detailed Examples

This reference provides comprehensive examples of Semantic Variables implementation patterns.

## Basic Semantic Variable Pattern

```php
final class Email
{
    #[Validate]
    public function validate(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailFormatException($email);
        }
    }
}

#[Message([
    'en' => 'Email format is invalid: {email}',
    'ja' => 'メールの形式が無効です: {email}'
])]
final class InvalidEmailFormatException extends DomainException
{
    public function __construct(public readonly string $email)
    {
        parent::__construct("Email format is invalid: {$this->email}");
    }
}
```

## Context-Aware Validation

```php
final class ProductCode
{
    #[Validate]
    public function validate(string $code): void
    {
        if (!preg_match('/^[A-Z]{2,4}-\\d{3,6}$/', $code)) {
            throw new InvalidProductCodeFormatException($code);
        }
    }

    #[Validate] 
    public function validateLegacy(#[Legacy] string $code): void
    {
        if (!preg_match('/^[0-9]{6}$/', $code)) {
            throw new InvalidLegacyCodeException($code);
        }
    }

    #[Validate]
    public function validatePremium(#[Premium] string $code): void
    {
        if (!str_starts_with($code, 'PREM-')) {
            throw new NotPremiumProductException($code);
        }
    }
}
```

## Hierarchical Validation

```php
final class Age
{
    #[Validate]
    public function validate(int $age): void
    {
        if ($age < 0) throw new NegativeAgeException($age);
        if ($age > 150) throw new AgeTooHighException($age);
    }
}

final class TeenAge  
{
    #[Validate]
    public function validate(#[Teen] int $age): void
    {
        // Inherits basic Age validation automatically
        if ($age < 13) throw new TeenAgeTooYoungException($age);
        if ($age > 19) throw new TeenAgeTooOldException($age);
    }
}

final class ChildAge
{
    #[Validate]
    public function validate(#[Child] int $age): void
    {
        // Inherits basic Age validation automatically
        if ($age < 0) throw new NegativeAgeException($age);
        if ($age > 12) throw new ChildAgeTooOldException($age);
    }
}
```

## Complex Business Rules

```php
final class PaymentAmount
{
    #[Validate]
    public function validate(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidPaymentAmountException($amount);
        }
    }

    #[Validate]
    public function validateSubscription(#[Subscription] float $amount): void
    {
        if ($amount < 5.00) {
            throw new SubscriptionTooSmallException($amount);
        }
        if ($amount > 999.99) {
            throw new SubscriptionTooLargeException($amount);
        }
    }

    #[Validate]
    public function validateDonation(#[Donation] float $amount): void
    {
        if ($amount < 1.00) {
            throw new DonationTooSmallException($amount);
        }
        // No upper limit for donations
    }
}
```

## Semantic Tags

```php
namespace App\Tag;

final class English {}
final class Japanese {}
final class Formal {}
final class Casual {}
final class Legacy {}
final class Premium {}
final class Teen {}
final class Child {}
final class Adult {}
final class Senior {}
final class Subscription {}
final class Donation {}
```

## Integration with Being Constructors

```php
final readonly class InternationalGreeting
{
    public readonly string $greeting;
    public readonly string $message;
    
    public function __construct(
        #[Input] #[English] public string $name,     // English name validation
        #[Input] #[Japanese] public string $title,   // Japanese title validation
        #[Input] #[Formal] public string $message,   // Formal message validation
        #[Inject] TranslationService $translator
    ) {
        $this->greeting = "Dear " . $name . "-san";
        $this->message = $translator->translate($message, 'ja');
    }
}
```

## Error Collection Example

```php
final readonly class UserRegistration
{
    public function __construct(
        #[Input] public string $name,        // May throw EmptyNameException
        #[Input] public string $email,       // May throw InvalidEmailFormatException
        #[Input] public int $age,            // May throw NegativeAgeException
        #[Input] public string $password,    // May throw WeakPasswordException
        #[Inject] UserRepository $repository
    ) {
        // Framework automatically collects all validation errors
        // If any fail, throws SemanticVariableException with all errors
    }
}

// Usage with error handling
try {
    $user = $becoming(new UserRegistration($data));
} catch (SemanticVariableException $e) {
    $errors = $e->getErrors();
    
    // Get all error messages in English
    $messages = $errors->getMessages('en');
    // ["Name cannot be empty.", "Email format is invalid.", "Age cannot be negative."]
    
    // Get specific exception details
    foreach ($errors->exceptions as $exception) {
        match (get_class($exception)) {
            EmptyNameException::class => $this->handleNameError($exception),
            InvalidEmailFormatException::class => $this->handleEmailError($exception),
            NegativeAgeException::class => $this->handleAgeError($exception),
        };
    }
}
```

## Custom Validation Logic

```php
final class CreditCardNumber
{
    #[Validate]
    public function validate(string $number): void
    {
        $number = preg_replace('/\\D/', '', $number);
        
        if (strlen($number) < 13 || strlen($number) > 19) {
            throw new InvalidCreditCardLengthException($number);
        }
        
        if (!$this->luhnCheck($number)) {
            throw new InvalidCreditCardChecksumException($number);
        }
    }
    
    #[Validate]
    public function validateVisa(#[Visa] string $number): void
    {
        if (!str_starts_with($number, '4')) {
            throw new NotVisaCardException($number);
        }
        if (!in_array(strlen($number), [13, 16, 19])) {
            throw new InvalidVisaCardLengthException($number);
        }
    }
    
    private function luhnCheck(string $number): bool
    {
        $sum = 0;
        $alternate = false;
        
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = intval($number[$i]);
            
            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = ($digit % 10) + 1;
                }
            }
            
            $sum += $digit;
            $alternate = !$alternate;
        }
        
        return ($sum % 10) === 0;
    }
}
```

## Parameterized Error Messages

```php
#[Message([
    'en' => 'Password must be at least {minLength} characters long. Current length: {actualLength}',
    'ja' => 'パスワードは少なくとも{minLength}文字以上である必要があります。現在の文字数: {actualLength}'
])]
final class WeakPasswordException extends DomainException
{
    public function __construct(
        public readonly string $password,
        public readonly int $minLength = 8,
        public readonly int $actualLength = 0
    ) {
        $this->actualLength = $this->actualLength ?: strlen($password);
    }
}
```