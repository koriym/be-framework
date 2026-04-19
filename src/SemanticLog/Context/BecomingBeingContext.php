<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use Be\Framework\Types;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;
use stdClass;

/**
 * Close context: the metamorphosis produced a new being that will continue transforming.
 *
 * Used when the result object has its own #[Be] attribute (single or multi-candidate),
 * meaning the Becoming pipeline will make another step.
 *
 * @psalm-import-type ObjectProperties from Types
 */
final class BecomingBeingContext extends AbstractContext implements JsonSerializable
{
    public const string TYPE = 'becoming_being';

    public const string SCHEMA_URL = 'https://be-framework.org/schemas/becoming-being.json';

    /**
     * @param class-string     $being Class FQCN of the new being (the instantiated result)
     * @param ObjectProperties $prop  Properties of the new being
     */
    public function __construct(
        public readonly string $being,
        public readonly array $prop,
    ) {
    }

    /** @return array{being: string, prop: object} */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'being' => $this->being,
            'prop' => empty($this->prop) ? new stdClass() : (object) $this->prop,
        ];
    }
}
