<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use stdClass;

/**
 * Context for transformation completion (Close context)
 *
 * Records the essential result: object properties and next destination.
 */
final class MetamorphosisCloseContext extends AbstractContext implements JsonSerializable
{
    public const TYPE = 'metamorphosis_close';
    public const SCHEMA_URL = 'https://be-framework.org/docs/schemas/metamorphosis-close.json';

    /**
     * @param array<string, mixed>                                                       $properties Object properties after construction
     * @param SingleDestination|MultipleDestination|DestinationNotFound|FinalDestination $be         Next metamorphosis destination
     */
    public function __construct(
        public readonly array $properties,
        public readonly SingleDestination|MultipleDestination|FinalDestination|DestinationNotFound $be,
    ) {
    }

    public function jsonSerialize(): array
    {
        // For now, create a simplified structure that matches schema
        return [
            'fromClass' => 'Unknown',
            'toClass' => 'Unknown',
            'beAttribute' => 'Unknown',
            'resultProperties' => empty($this->properties) ? new stdClass() : (object) $this->properties,
            'success' => true, // Simplified for now
        ];
    }
}
