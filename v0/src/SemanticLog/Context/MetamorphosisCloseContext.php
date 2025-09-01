<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use Koriym\SemanticLogger\AbstractContext;

/**
 * Context for transformation completion (Close context)
 * 
 * Records the essential result: object properties and next destination.
 */
final class MetamorphosisCloseContext extends AbstractContext
{
    public const TYPE = 'metamorphosis_close';
    public const SCHEMA_URL = 'https://be-framework.org/docs/schemas/metamorphosis-close.json';
    
    /**
     * @param array<string, mixed> $properties Object properties after construction
     * @param SingleDestination|MultipleDestination|DestinationNotFound|FinalDestination $be Next metamorphosis destination
     */
    public function __construct(
        public readonly array $properties,
        public readonly SingleDestination|MultipleDestination|FinalDestination|DestinationNotFound $be,
    ) {}
}

