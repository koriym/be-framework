<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use Be\Framework\Types;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;
use stdClass;

/**
 * Close context: the metamorphosis reached a terminal being (no further #[Be]).
 *
 * @psalm-import-type ObjectProperties from Types
 */
final class BecomingFinalContext extends AbstractContext implements JsonSerializable
{
    public const string TYPE = 'becoming_final';

    public const string SCHEMA_URL = 'https://be-framework.org/schemas/becoming-final.json';

    /**
     * @param class-string     $final Class FQCN of the terminal being
     * @param ObjectProperties $prop  Properties of the terminal being
     */
    public function __construct(
        public readonly string $final,
        public readonly array $prop,
    ) {
    }

    /** @return array{final: string, prop: object} */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'final' => $this->final,
            'prop' => empty($this->prop) ? new stdClass() : (object) $this->prop,
        ];
    }
}
