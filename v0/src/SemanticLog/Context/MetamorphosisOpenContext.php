<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use Koriym\SemanticLogger\AbstractContext;

/**
 * Context for transformation start (Open context)
 *
 * Records constructor arguments BEFORE instantiation.
 * This captures what we intend to pass to the constructor.
 */
final class MetamorphosisOpenContext extends AbstractContext
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
}
