<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use Be\Framework\Types;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;
use stdClass;

/**
 * Context for transformation start (Open context)
 *
 * Records constructor arguments BEFORE instantiation.
 * This captures what we intend to pass to the constructor.
 *
 * @psalm-import-type ImmanentSources from Types
 * @psalm-import-type TranscendentSources from Types
 * @psalm-import-type ObjectProperties from Types
 */
final class MetamorphosisOpenContext extends AbstractContext implements JsonSerializable
{
    public const string TYPE = 'metamorphosis_open';

    public const string SCHEMA_URL = 'https://be-framework.org/docs/schemas/metamorphosis-open.json';

    /**
     * @param class-string        $fromClass           Class being transformed from
     * @param string              $beAttribute         The #[Be] attribute triggering transformation
     * @param ImmanentSources     $immanentSources     #[Input] parameter values from previous object
     * @param TranscendentSources $transcendentSources #[Inject] interface/service names from DI container
     */
    public function __construct(
        public readonly string $fromClass,
        public readonly string $beAttribute,
        public readonly array $immanentSources = [],
        public readonly array $transcendentSources = [],
    ) {
    }

    /** @return ObjectProperties */
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
