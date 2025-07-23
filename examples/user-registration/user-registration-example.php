<?php

declare(strict_types=1);

/**
 * User Registration Example - Be Framework
 *
 * This example demonstrates the complete implementation of a user registration flow
 * using the Type-Driven Metamorphosis pattern. It showcases:
 *
 * - Constructor-driven transformation
 * - Type-driven branching through union types
 * - Objects that carry their own destiny
 * - Immutable state with public readonly properties
 * - Existential self-discovery instead of external routing
 */

namespace Example\UserRegistration;

use Ray\InputQuery\Attribute\Input;
use Ray\Framework\Be;
use Ray\Di\Di\Inject;

// =============================================================================
// DESTINY TYPE CLASSES
// =============================================================================

/**
 * Represents user input ready for registration.
 * This type embodies the "new user registration" destiny.
 */
final class UserInput
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {}
}

/**
 * Represents a conflicting user registration attempt.
 * This type embodies the "conflict" destiny.
 */
final class ConflictingUser
{
    public function __construct(
        public readonly string $email
    ) {}
}

// =============================================================================
// STAGE 1: RAW INPUT (The Egg)
// =============================================================================

/**
 * The initial input stage representing raw registration data.
 *
 * This class serves as the entry point for the registration process.
 * It contains no logic, only data, representing the "egg" stage
 * of the metamorphosis.
 */
#[Be(ValidatedRegistration::class)]
final class RegistrationInput
{
    /**
     * Initializes a new registration input with the provided email, password, and password confirmation.
     *
     * This class serves as a pure data container for raw user registration input and performs no validation.
     */
    public function __construct(
        #[Input] public readonly string $email,
        #[Input] public readonly string $password,
        #[Input] public readonly string $passwordConfirmation
    ) {
        // Pure data container - no logic or validation
    }
}

// =============================================================================
// STAGE 2: VALIDATED INPUT (The Larva)
// =============================================================================

/**
 * Represents registration data that has passed validation and discovered its destiny.
 *
 * The existence of this object guarantees that:
 * - Email format is valid
 * - Password meets strength requirements
 * - Password and confirmation match
 * - The object knows what it will become next
 *
 * This demonstrates Type-Driven Metamorphosis - the object carries its own destiny.
 */
#[Be([UnverifiedUser::class, UserConflict::class])]
final class ValidatedRegistration
{
    /**
     * Validates registration input and determines the next registration stage.
     *
     * Checks email format, password strength, and password confirmation. If all validations pass, sets the next stage to either a new user registration or a conflict state based on whether the email already exists.
     *
     * @param string $email The user's email address.
     * @param string $password The user's chosen password.
     * @param string $passwordConfirmation The password confirmation input.
     */
    public function __construct(
        #[Input] public readonly string $email,
        #[Input] public readonly string $password,
        #[Input] string $passwordConfirmation,
        #[Inject] UserValidator $validator,
        #[Inject] UserRepository $userRepo
    ) {
        // The constructor IS the validation
        // If any validation fails, this object never exists
        $validator->validateEmailFormat($this->email);
        $validator->validatePasswordStrength($this->password);
        $validator->validatePasswordsMatch($this->password, $passwordConfirmation);

        // The existential question: Who will I become?
        $this->being = $userRepo->existsByEmail($this->email)
            ? new ConflictingUser($this->email)
            : new UserInput($this->email, $this->password);
    }
    
    // I carry my destiny within me
    public readonly UserInput|ConflictingUser $being;
}

// =============================================================================
// STAGE 3: TYPE-DRIVEN BRANCHING (No Traffic Controller Needed!)
// =============================================================================

// Notice: No RegistrationRouter class needed!
// The framework automatically routes based on the $being property type:
// - If $being instanceof UserInput → UnverifiedUser::class
// - If $being instanceof ConflictingUser → UserConflict::class
//
// This is the power of Type-Driven Metamorphosis:
// Objects discover their own nature instead of external routing.

// =============================================================================
// SUCCESS PATH - STAGE 4: UNVERIFIED USER (The Pupa)
// =============================================================================

/**
 * Represents a successfully created but unverified user.
 *
 * The existence of this object guarantees:
 * - User has been persisted to the database
 * - Password has been hashed
 * - Verification token has been generated
 *
 * This is the "pupa" stage - almost ready for final form.
 */
#[Be(VerificationEmailSent::class)]
final class UnverifiedUser
{
    public readonly string $userId;
    public readonly string $verificationToken;

    public function __construct(
        #[Input] UserInput $being,  // From previous object property
        #[Inject] PasswordHasher $hasher,  // From DI container
        #[Inject] TokenGenerator $tokenGenerator,
        #[Inject] UserRepository $userRepo
    ) {
        // Hash the password for secure storage
        $hashedPassword = $hasher->hash($being->password);

        // Generate a unique verification token
        $token = $tokenGenerator->generate();

        // Persist the user in unverified state
        $user = $userRepo->createUnverified($being->email, $hashedPassword, $token);

        // Store the results as immutable properties
        $this->userId = $user->id;
        $this->verificationToken = $user->verificationToken;
    }
}

// =============================================================================
// SUCCESS PATH - STAGE 5: EMAIL SENT (The Butterfly)
// =============================================================================

/**
 * The final success stage confirming email notification sent.
 *
 * This is the "butterfly" stage - the final, beautiful form of a
 * successful registration. Its existence proves that:
 * - User was created successfully
 * - Verification email was sent
 * - Registration process completed
 */
#[Be(JsonResponse::class)]
final class VerificationEmailSent
{
    public readonly string $message;
    public readonly string $userId;

    public function __construct(
        #[Input] string $userId,
        #[Input] string $verificationToken,
        #[Inject] UserEmailResolver $emailResolver,
        #[Inject] MailerInterface $mailer
    ) {
        // Resolve the user's email from their ID
        $email = $emailResolver->getEmailForUser($userId);

        // Send the verification email
        $mailer->sendVerificationEmail($email, $verificationToken);

        // Store the user ID for the response
        $this->userId = $userId;
        $this->message = 'Registration successful. Please check your email to verify your account.';
    }
}

// =============================================================================
// CONFLICT PATH - STAGE 4: USER CONFLICT (Alternative Butterfly)
// =============================================================================

/**
 * Represents a registration attempt that failed due to duplicate email.
 *
 * This is an alternative "butterfly" - a different but equally valid
 * final form. Its existence indicates:
 * - Registration was attempted
 * - Email already exists in system
 * - Appropriate error response needed
 */
#[Be(JsonResponse::class)]
final class UserConflict
{
    public readonly string $error;
    public readonly string $message;

    public function __construct(
        #[Input] ConflictingUser $being
    ) {
        // Create a user-friendly error message
        $this->error = 'User already exists';
        $this->message = "The email address '{$being->email}' is already registered.";
    }
}

// =============================================================================
// TERMINAL STAGE: JSON RESPONSE (Universal Endpoint)
// =============================================================================

/**
 * Generic response transformer for JSON output.
 *
 * This is a reusable terminal stage that can transform any object
 * into a JSON response. It reads all public properties from the input
 * object and creates an appropriate HTTP response.
 *
 * Note: In a real implementation, this would produce a PSR-7 response.
 */
final class JsonResponse
{
    public readonly string $json;
    public readonly int $statusCode;
    public readonly array $headers;

    public function __construct(
        #[Input] object $payloadObject,
        int $statusCode = 200,
        array $headers = ['Content-Type' => 'application/json']
    ) {
        // Extract all public properties from the input object
        $data = get_object_vars($payloadObject);

        // Convert to JSON
        $this->json = json_encode($data, JSON_THROW_ON_ERROR);
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }
}

// =============================================================================
// SUPPORTING INTERFACES (Would be defined elsewhere)
// =============================================================================

/**
 * Validates user registration data.
 */
interface UserValidator
{
    public function validateEmailFormat(string $email): void;
    public function validatePasswordStrength(string $password): void;
    public function validatePasswordsMatch(string $password, string $confirmation): void;
}

/**
 * Repository for user persistence operations.
 */
interface UserRepository
{
    public function existsByEmail(string $email): bool;
    public function createUnverified(string $email, string $hashedPassword, string $token): object;
}

/**
 * Hashes passwords for secure storage.
 */
interface PasswordHasher
{
    public function hash(string $password): string;
}

/**
 * Generates unique tokens for email verification.
 */
interface TokenGenerator
{
    public function generate(): string;
}

/**
 * Sends emails through the mail system.
 */
interface MailerInterface
{
    public function sendVerificationEmail(string $email, string $token): void;
}

/**
 * Resolves email addresses from user IDs.
 */
interface UserEmailResolver
{
    public function getEmailForUser(string $userId): string;
}

// =============================================================================
// USAGE EXAMPLE
// =============================================================================

use Be\Framework\Becoming;
use Ray\Di\Injector;

// Create the Becoming executor with dependency injection
$injector = new Injector(new RegistrationModule());
$becoming = new Becoming($injector);

// Execute the registration flow
$finalObject = $becoming(new RegistrationInput(
    'newuser@example.com',
    'SecurePass123!',
    'SecurePass123!'
));

// The finalObject will be JsonResponse with appropriate status:
// - Success path: 201 Created with userId and success message
// - Conflict path: 409 Conflict with error message
// The path is determined by the data and business rules, not by the caller

// Send headers first
header('HTTP/1.1 ' . $finalObject->statusCode);
foreach ($finalObject->headers as $name => $value) {
    header("{$name}: {$value}");
}

// Then output the body
echo $finalObject->json;
