<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;

/**
 * Open context for the whole metamorphosis chain.
 *
 * Wraps every being_open/being_*_close pair into a single root so
 * sequential transformations appear as sibling children of one outer span,
 * rather than as disconnected top-level operations.
 */
final class BecomingOpenContext extends AbstractContext implements JsonSerializable
{
    public const string TYPE = 'becoming_open';

    public const string SCHEMA_URL = 'https://be-framework.org/schemas/becoming-open.json';

    /**
     * @param class-string         $input FQCN of the initial input being that starts the chain.
     * @param array<string, mixed> $prop  Public input properties captured at chain entry.
     */
    public function __construct(
        public readonly string $input,
        public readonly array $prop = [],
    ) {
    }

    /** @return array{input: string, prop?: array<string, mixed>} */
    #[Override]
    public function jsonSerialize(): array
    {
        $payload = [
            'input' => $this->input,
        ];

        if ($this->prop !== []) {
            $payload['prop'] = $this->prop;
        }

        return $payload;
    }
}
