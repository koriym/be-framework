<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;
use stdClass;

/**
 * Context for transformation start (Open context)
 *
 * Records constructor arguments BEFORE instantiation.
 * This captures what we intend to pass to the constructor.
 */
final class MetamorphosisOpenContext extends AbstractContext implements JsonSerializable
{
    public const TYPE = 'metamorphosis_open';

    public const SCHEMA_URL = 'https://be-framework.org/docs/schemas/metamorphosis-open.json';

    /**
     * @param class-string          $fromClass           Class being transformed from
     * @param string                $beAttribute         The #[Be] attribute triggering transformation
     * @param array<string, mixed>  $immanentSources     #[Input] parameter values from previous object
     * @param array<string, string> $transcendentSources #[Inject] interface/service names from DI container
     */
    public function __construct(
        public readonly string $fromClass,
        public readonly string $beAttribute,
        public readonly array $immanentSources = [],
        public readonly array $transcendentSources = [],
    ) {
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'fromClass' => $this->fromClass,
            'beAttribute' => $this->beAttribute,
            'immanentSources' => empty($this->immanentSources) ? new stdClass() : (object) $this->immanentSources,
            'transcendentSources' => empty($this->transcendentSources) ? new stdClass() : (object) $this->transcendentSources,
        ];
    }
}
