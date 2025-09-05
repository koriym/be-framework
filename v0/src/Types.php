<?php

declare(strict_types=1);

namespace Be\Framework;

/**
 * Domain-specific type aliases for Be Framework
 *
 * These types provide semantic meaning to primitive and complex types used throughout the framework,
 * improving type safety and code documentation via Psalm static analysis.
 *
 * @link https://psalm.dev/docs/annotating_code/supported_annotations/#psalm-type
 *
 * String domain types
 * @psalm-type ClassName = string                   Simple class name (e.g., "User", "ValidationError")
 * @psalm-type PropertyName = string                Object property name (e.g., "email", "createdAt")
 * @psalm-type ParameterName = string               Method/constructor parameter name (e.g., "userId", "config")
 * @psalm-type InterfaceName = string               Interface or service name for DI (e.g., "UserRepository")
 * @psalm-type ErrorMessage = string                Human-readable error description
 * @psalm-type AttributeName = string               PHP attribute class name (e.g., "Input", "Validate")
 * @psalm-type SemanticVariableName = string        Variable name for semantic validation (e.g., "email", "age")
 * @psalm-type LogContextId = string                Unique identifier for logging context correlation
 * @psalm-type BeAttributeValue = string            Value of #[Be] attribute for transformation target
 * @psalm-type SemanticNamespace = string           Namespace for semantic validation classes
 *
 * Class and value domain types
 * @psalm-type QualifiedClassName = class-string    Fully qualified class name (e.g., "App\Domain\User")
 * @psalm-type ConstructorArgumentValue = mixed     Value passed to constructor during metamorphosis
 * @psalm-type PropertyValue = mixed                Value stored in object property after construction
 * @psalm-type ValidationArgumentValue = mixed      Value being validated by semantic validator
 *
 * Array domain types
 * @psalm-type ConstructorArguments = array<ParameterName, ConstructorArgumentValue>   Parameter-to-value map for object construction
 * @psalm-type ObjectProperties = array<PropertyName, PropertyValue>                   Property-to-value map of constructed object
 * @psalm-type BecomingClasses = array<ClassName>                                      Array of class names for metamorphosis
 * @psalm-type QualifiedClasses = array<QualifiedClassName>                            Array of fully qualified class names
 * @psalm-type ImmanentSources = array<ParameterName, PropertyName>                    #[Input] parameter sources from object properties
 * @psalm-type TranscendentSources = array<ParameterName, InterfaceName>               #[Inject] parameter sources from DI container
 * @psalm-type ParameterAttributes = array<AttributeName>                              Collection of PHP attribute names on parameters
 * @psalm-type LocalizedMessages = array<string, string>                               Locale-to-message mapping for i18n
 * @psalm-type ValidationMessages = array<ErrorMessage>                                Collection of validation error messages
 * @psalm-type ExceptionCollection = array<Exception>                                  Collection of exception instances
 * @psalm-type CandidateErrors = array<ClassName, ErrorMessage>                        Class-to-error mapping for failed transformations
 * @psalm-type ValidationArguments = array<ValidationArgumentValue>                    Arguments passed to semantic validation
 * @psalm-type ReflectionMethods = array<ReflectionMethod>                             Collection of PHP reflection method objects
 * @psalm-type AttemptedClasses = array<ClassName>                                     Classes attempted during failed transformations
 */
final class Types
{
}
