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

    public const string ORIGIN_INPUT = 'input';

    public const string ORIGIN_RUNTIME = 'runtime';

    /**
     * @param string            $exit    Exit status for the chain (self::EXIT_SUCCESS|self::EXIT_ERROR).
     * @param class-string|null $final   FQCN of the terminal being, or null if the chain failed.
     * @param string|null       $error   FQCN of the thrown exception, or null on success.
     * @param string|null       $message Exception message, or null on success.
     * @param string|null       $origin  Provenance of a semantic validation failure
     *                                    (self::ORIGIN_INPUT|self::ORIGIN_RUNTIME), or null when
     *                                    the failure is not a semantic validation error.
     */
    public function __construct(
        public readonly string $exit,
        public readonly string|null $final = null,
        public readonly string|null $error = null,
        public readonly string|null $message = null,
        public readonly string|null $origin = null,
    ) {
    }

    /** @return array<string, string|null> */
    #[Override]
    public function jsonSerialize(): array
    {
        $payload = ['exit' => $this->exit];

        if ($this->final !== null) {
            $payload['final'] = $this->final;
        }

        if ($this->error !== null) {
            $payload['error'] = $this->error;
            $payload['message'] = $this->message ?? '';

            if ($this->origin !== null) {
                $payload['origin'] = $this->origin;
            }
        }

        return $payload;
    }
}
