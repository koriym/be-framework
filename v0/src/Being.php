<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Attribute\Be;
use Be\Framework\Exception\TypeMatchingFailure;
use Be\Framework\SemanticLog\LoggerInterface;
use ReflectionClass;
use Throwable;

use function assert;
use function is_string;

/**
 * Gets the next class name in metamorphosis chain
 *
 * @psalm-import-type BecomingClasses from Types
 * @psalm-import-type CandidateErrors from Types
 */
final class Being
{
    public function __construct(private LoggerInterface $logger, private BecomingArgumentsInterface $becomingArguments)
    {
    }

    /**
     * Get what this object is becoming
     *
     * @param object $current Current object in metamorphosis chain
     *
     * @return string|BecomingClasses|null Next class name(s) or null if transformation is complete
     */
    public function willBe(object $current): string|array|null
    {
        $reflection = new ReflectionClass($current);
        $beAttributes = $reflection->getAttributes(Be::class);

        if (empty($beAttributes)) {
            return null;
        }

        $be = $beAttributes[0]->newInstance();

        return $be->being;  // Returns what this object is becoming
    }

    /**
     * The moment of transformation - pure and irreversible
     *
     * @param string|BecomingClasses $becoming
     */
    public function metamorphose(object $current, string|array $becoming): object
    {
        if (is_string($becoming)) {
            return $this->performSingleTransformation($current, $becoming);
        }

        // Array case: find the appropriate class based on type matching
        return $this->performTypeMatching($current, $becoming);
    }

    private function performSingleTransformation(object $current, string $becoming): object
    {
        $openId = $this->logger->open($current, $becoming);

        try {
            $args = $this->becomingArguments->be($current, $becoming);
            /** @var class-string $becoming */
            $result = (new ReflectionClass($becoming))->newInstanceArgs($args);

            $this->logger->close($result, $openId);

            return $result;
        } catch (Throwable $e) {
            $this->logger->close(null, $openId, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Perform type matching to select the appropriate class from array of possibilities
     *
     * @param BecomingClasses $becoming
     */
    private function performTypeMatching(object $current, array $becoming): object
    {
        /** @var CandidateErrors $candidateErrors */
        $candidateErrors = [];

        foreach ($becoming as $class) {
            try {
                return $this->performSingleTransformation($current, $class);
            } catch (Throwable $e) {
                // Capture detailed error information for each candidate
                $candidateErrors[$class] = $e->getMessage();
                continue; // Try next class if this one fails
            }
        }

        throw TypeMatchingFailure::create($becoming, $candidateErrors);
    }
}
