<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Attribute\Be;
use Be\Framework\Exception\TypeMatchingFailure;
use Be\Framework\SemanticLog\LoggerInterface;
use ReflectionClass;
use Throwable;

use function implode;
use function is_string;
use function sprintf;

/**
 * Gets the next class name in metamorphosis chain
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
     * @return string|array|null Next class name(s) or null if transformation is complete
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
        $openId = $this->logger?->open($current, $becoming);

        try {
            $args = $this->becomingArguments->be($current, $becoming);
            $result = (new ReflectionClass($becoming))->newInstanceArgs($args);

            $this->logger?->close($result, $openId);

            return $result;
        } catch (Throwable $e) {
            $this->logger?->close(null, $openId, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Perform type matching to select the appropriate class from array of possibilities
     */
    private function performTypeMatching(object $current, array $becoming): object
    {
        foreach ($becoming as $class) {
            try {
                return $this->performSingleTransformation($current, $class);
            } catch (Throwable) {
                continue; // Try next class if this one fails
            }
        }

        throw new TypeMatchingFailure(
            sprintf('No matching class for becoming in [%s]', implode(', ', $becoming)),
        );
    }
}
