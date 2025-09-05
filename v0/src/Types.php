<?php

declare(strict_types=1);

namespace Be\Framework;

use Exception;
use ReflectionMethod;

/**
 * Domain-specific type aliases for Be Framework
 *
 * These types provide semantic meaning to arrays used throughout the framework,
 * improving type safety and code documentation via Psalm static analysis.
 *
 * @psalm-type ConstructorArguments = array<string, mixed>
 * @psalm-type ObjectProperties = array<string, mixed>
 * @psalm-type BecomingClasses = array<string>
 * @psalm-type QualifiedClasses = array<class-string>
 * @psalm-type ImmanentSources = array<string, string>
 * @psalm-type TranscendentSources = array<string, string>
 * @psalm-type ParameterAttributes = array<string>
 * @psalm-type LocalizedMessages = array<string, string>
 * @psalm-type ValidationMessages = array<string>
 * @psalm-type ExceptionCollection = array<Exception>
 * @psalm-type CandidateErrors = array<string, string>
 * @psalm-type ValidationArguments = array<mixed>
 * @psalm-type ReflectionMethods = array<ReflectionMethod>
 * @psalm-type AttemptedClasses = array<string>
 */
final class Types
{
    // This class serves as a container for psalm type definitions
    // All type definitions are in the class docblock above
    // The class itself is empty and serves only as a namespace for the types
}
