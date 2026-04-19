<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;

/**
 * Close context: the metamorphosis failed with a thrown exception.
 */
final class BecomingErrorContext extends AbstractContext implements JsonSerializable
{
    public const string TYPE = 'becoming_error';

    public const string SCHEMA_URL = 'https://be-framework.org/docs/schemas/becoming-error.json';

    /**
     * @param string $error   Fully-qualified class name of the thrown exception
     *                        (or a sentinel string such as "UnknownError" when no Throwable is available)
     * @param string $message Exception message
     */
    public function __construct(
        public readonly string $error,
        public readonly string $message,
    ) {
    }

    /** @return array{error: string, message: string} */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'error' => $this->error,
            'message' => $this->message,
        ];
    }
}
