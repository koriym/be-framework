<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;

/**
 * Close context for the whole metamorphosis chain.
 *
 * Emitted when the chain terminates — either because it reached a being with
 * no further #[Be], or because a transformation threw. Carries the chain exit
 * state plus either the terminal FQCN or the exception that ended the chain.
 */
final class BecomingCloseContext extends AbstractContext implements JsonSerializable
{
    public const string TYPE = 'becoming_close';

    public const string SCHEMA_URL = 'https://be-framework.org/schemas/becoming-close.json';

    public const string EXIT_SUCCESS = 'success';

    public const string EXIT_ERROR = 'error';

    /**
     * @param class-string|null $final   FQCN of the terminal being, or null if the chain failed.
     * @param string|null       $exit    Exit status for the chain.
     * @param string|null       $error   FQCN of the thrown exception, or null on success.
     * @param string|null       $message Exception message, or null on success.
     */
    public function __construct(
        public readonly string|null $final = null,
        public readonly string|null $exit = null,
        public readonly string|null $error = null,
        public readonly string|null $message = null,
    ) {
    }

    /** @return array<string, string|null> */
    #[Override]
    public function jsonSerialize(): array
    {
        $payload = [];
        if ($this->exit !== null) {
            $payload['exit'] = $this->exit;
        }

        if ($this->final !== null) {
            $payload['final'] = $this->final;
        }

        if ($this->error !== null) {
            $payload['error'] = $this->error;
            $payload['message'] = $this->message ?? '';
        }

        return $payload;
    }
}
