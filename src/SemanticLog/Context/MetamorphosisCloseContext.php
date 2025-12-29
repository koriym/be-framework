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
     * @param string                                                                     $fromClass   Class that was transformed from
     * @param string                                                                     $toClass     Class that was transformed to
     * @param string                                                                     $beAttribute The #[Be] attribute that triggered transformation
     * @param ObjectProperties                                                           $properties  Object properties after construction
     * @param SingleDestination|MultipleDestination|DestinationNotFound|FinalDestination $be          Next metamorphosis destination
     */
    public function __construct(
        public readonly string $fromClass,
        public readonly string $toClass,
        public readonly string $beAttribute,
        public readonly array $properties,
        public readonly SingleDestination|MultipleDestination|FinalDestination|DestinationNotFound $be,
    ) {
    }

    /** @return array{fromClass: string, toClass: string, beAttribute: string, resultProperties: stdClass|object, success: bool, error?: string} */
    #[Override]
    public function jsonSerialize(): array
    {
        $result = [
            'fromClass' => $this->fromClass,
            'toClass' => $this->toClass,
            'beAttribute' => $this->beAttribute,
            'resultProperties' => empty($this->properties) ? new stdClass() : (object) $this->properties,
            'success' => ! $this->be instanceof DestinationNotFound,
        ];

        if ($this->be instanceof DestinationNotFound) {
            $result['error'] = $this->be->error;
        }

        return $result;
    }
}
