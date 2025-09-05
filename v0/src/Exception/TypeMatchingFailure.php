<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use RuntimeException;

use function implode;

/**
 * Thrown when type matching fails during array-based becoming
 */
final class TypeMatchingFailure extends RuntimeException
{
    /** @param array<string, string> $candidateErrors */
    private function __construct(string $message, private array $candidateErrors = [])
    {
        parent::__construct($message);
    }

    /**
     * Create exception with detailed candidate failure information
     *
     * @param array<string>         $candidates
     * @param array<string, string> $candidateErrors
     */
    public static function create(array $candidates, array $candidateErrors): self
    {
        $candidateList = implode(', ', $candidates);
        $message = "No matching class for becoming in [{$candidateList}]";

        if (! empty($candidateErrors)) {
            $errorDetails = [];
            foreach ($candidateErrors as $class => $error) {
                $errorDetails[] = "  - {$class}: {$error}";
            }

            $message .= "\n\nCandidate failures:\n" . implode("\n", $errorDetails);
        }

        return new self($message, $candidateErrors);
    }

    /**
     * Get detailed error information for each candidate class
     *
     * @return array<string, string>
     */
    public function getCandidateErrors(): array
    {
        return $this->candidateErrors;
    }
}
