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
final class BeingFinalCloseContext extends AbstractContext implements JsonSerializable
{
    public const string TYPE = 'being_final_close';

    public const string SCHEMA_URL = 'https://be-framework.org/schemas/being-final-close.json';

    /**
     * @param class-string          $final Class FQCN of the terminal being
     * @param ObjectProperties      $prop  Properties of the terminal being
     * @param list<AbstractContext> $been  Events the terminal being curated into its own `$been` carrier (empty when it doesn't hold one)
     */
    public function __construct(
        public readonly string $final,
        public readonly array $prop,
        public readonly array $been = [],
    ) {
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        $payload = [
            'final' => $this->final,
            'prop' => empty($this->prop) ? new stdClass() : (object) $this->prop,
        ];

        if ($this->been !== []) {
            $payload['been'] = $this->been;
        }

        return $payload;
    }
}
