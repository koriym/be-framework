<?php

declare(strict_types=1);

/**
 * User Registration Example - Ray.Framework
 * 
 * This example demonstrates the complete implementation of a user registration flow
 * using the Metamorphosis Architecture pattern. It showcases:
 * 
 * - Constructor-driven transformation
 * - Traffic Controller pattern for branching
 * - Type-safe factory interfaces
 * - Immutable state with public readonly properties
 * - Clear separation of success and failure paths
 */

namespace Example\UserRegistration;

use Ray\Framework\Attribute\Input;
use Ray\Framework\Attribute\To;

// =============================================================================
// FACTORY INTERFACES
// =============================================================================

/**
 * Factory for creating unverified users in the success path.
 * 
 * This interface ensures type-safe creation of users who have passed validation
 * but have not yet verified their email address.
 */
interface UnverifiedUserFactory
{
    public function create(string $email, string $password): UnverifiedUser;
}

/**
 * Factory for handling registration conflicts.
 * 
 * This interface is used when a user attempts to register with an email
 * that already exists in the system.
 */
interface UserConflictFactory
{
    public function create(string $email): UserConflict;
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
#[To(ValidatedRegistration::class)]
final class RegistrationInput
{
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
 * Represents registration data that has passed validation.
 * 
 * The existence of this object guarantees that:
 * - Email format is valid
 * - Password meets strength requirements
 * - Password and confirmation match
 * 
 * This is the "larva" stage - transformed but not yet final.
 */
#[To(RegistrationRouter::class)]
final class ValidatedRegistration
{
    public function __construct(
        #[Input] public readonly string $email,
        #[Input] public readonly string $password,
        #[Input] string $passwordConfirmation,
        UserValidator $validator // Injected dependency for validation
    ) {
        // The constructor IS the validation
        // If any validation fails, this object never exists
        $validator->validateEmailFormat($this->email);
        $validator->validatePasswordStrength($this->password);
        $validator->validatePasswordsMatch($this->password, $passwordConfirmation);
        
        // If we reach here, validation passed
        // The object's existence is proof of validity
    }
}

// =============================================================================
// STAGE 3: TRAFFIC CONTROLLER (The Decision Point)
// =============================================================================

/**
 * Routes the registration to either success or conflict path.
 * 
 * This is a specialized metamorphosis class that acts as a "Traffic Controller."
 * It makes a decision based on business rules and initiates the appropriate
 * downstream pipeline. It holds no state and exists only to direct flow.
 * 
 * Key pattern: Guard clauses handle exceptional paths first, making the
 * happy path clear and obvious.
 */
final class RegistrationRouter
{
    public function __construct(
        #[Input] ValidatedRegistration $validated,
        UserRepository $userRepo,
        UnverifiedUserFactory $unverifiedUserFactory,
        UserConflictFactory $userConflictFactory
    ) {
        // Guard clause: Check for conflicts first
        if ($userRepo->existsByEmail($validated->email)) {
            // Email already exists - initiate conflict path
            $userConflictFactory->create($validated->email);
            return; // Stop here - no further processing
        }
        
        // Happy path: Create new user
        // This only executes if no conflict exists
        $unverifiedUserFactory->create(
            $validated->email, 
            $validated->password
        );
    }
}

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
#[To(VerificationEmailSent::class)]
final class UnverifiedUser
{
    public readonly string $userId;
    public readonly string $verificationToken;

    public function __construct(
        string $email,
        string $password,
        PasswordHasher $hasher,
        TokenGenerator $tokenGenerator,
        UserRepository $userRepo
    ) {
        // Hash the password for secure storage
        $hashedPassword = $hasher->hash($password);
        
        // Generate a unique verification token
        $token = $tokenGenerator->generate();
        
        // Persist the user in unverified state
        $user = $userRepo->createUnverified($email, $hashedPassword, $token);
        
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
#[To(JsonResponse::class, statusCode: 201)]
final class VerificationEmailSent
{
    public readonly string $message = 'Registration successful. Please check your email to verify your account.';
    public readonly string $userId;

    public function __construct(
        #[Input] string $userId,
        #[Input] string $verificationToken,
        UserEmailResolver $emailResolver,
        MailerInterface $mailer
    ) {
        // Resolve the user's email from their ID
        $email = $emailResolver->getEmailForUser($userId);
        
        // Send the verification email
        $mailer->sendVerificationEmail($email, $verificationToken);
        
        // Store the user ID for the response
        $this->userId = $userId;
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
#[To(JsonResponse::class, statusCode: 409)]
final class UserConflict
{
    public readonly string $error = 'User already exists';
    public readonly string $message;

    public function __construct(string $email)
    {
        // Create a user-friendly error message
        $this->message = "The email address '{$email}' is already registered.";
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

use Ray\Framework\Ray;
use Ray\Di\Injector;

// Create the Ray executor with dependency injection
$injector = new Injector(new RegistrationModule());
$ray = new Ray($injector);

// Execute the registration flow
$response = $ray(new RegistrationInput(
    'newuser@example.com',
    'SecurePass123!',
    'SecurePass123!'
));

// The response will be JsonResponse with appropriate status:
// - Success path: 201 Created with userId and success message
// - Conflict path: 409 Conflict with error message
// The path is determined by the data and business rules, not by the caller

// Send headers first
header('HTTP/1.1 ' . $response->statusCode);
foreach ($response->headers as $name => $value) {
    header("{$name}: {$value}");
}

// Then output the body
echo $response->json;