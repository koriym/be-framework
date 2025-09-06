<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use Be\Framework\Types;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;
use stdClass;

/**
 * Context for transformation completion (Close context)
 *
 * Records the essential result: object properties and next destination.
 *
 * @psalm-import-type ObjectProperties from Types
 */
final class MetamorphosisCloseContext extends AbstractContext implements JsonSerializable
{
    public const string TYPE = 'metamorphosis_close';

    public const string SCHEMA_URL = 'https://be-framework.org/docs/schemas/metamorphosis-close.json';

    /**
     * @param ObjectProperties                                                           $properties Object properties after construction
     * @param SingleDestination|MultipleDestination|DestinationNotFound|FinalDestination $be         Next metamorphosis destination
     */
    public function __construct(
        public readonly array $properties,
        public readonly SingleDestination|MultipleDestination|FinalDestination|DestinationNotFound $be,
    ) {
    }

    /** @return ObjectProperties */
    #[Override]
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
