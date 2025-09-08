<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use Be\Framework\Types;
use RuntimeException;

use function implode;

/**
 * Thrown when type matching fails during array-based becoming
 *
 * @psalm-import-type QualifiedClasses from Types
 */
final class BeMatchException extends RuntimeException
{
    /**
     * @param QualifiedClasses $candidates
     * @param Unmatch[]        $unmatches
     * @phpstan-param array<class-string> $candidates
     * @phpstan-param array<Unmatch> $unmatches
     */
    public function __construct(array $candidates, private array $unmatches = [])
    {
        $candidateList = implode(', ', $candidates);
        $message = "No matching class for becoming in [{$candidateList}]";

        if (! empty($unmatches)) {
            $errorDetails = [];
            foreach ($unmatches as $unmatch) {
                $errorDetails[] = "  - {$unmatch->getMessage()}";
            }

            $message .= "\n\nCandidate unmatches:\n" . implode("\n", $errorDetails);
        }

        parent::__construct($message);
    }

    /**
     * Get structured unmatch information for each candidate class
     *
     * @return Unmatch[]
     *
     * @psalm-mutation-free
     */
    public function getUnmatches(): array
    {
        return $this->unmatches;
    }

    /**
     * Get detailed error information for each candidate class (legacy)
     *
     * @return array<string, string>
     *
     * @psalm-mutation-free
     * @deprecated Use getUnmatches() instead
     */
    public function getCandidateErrors(): array
    {
        $errors = [];
        foreach ($this->unmatches as $unmatch) {
            $errors[$unmatch->className] = $unmatch->getMessage();
        }

        return $errors;
    }
}
