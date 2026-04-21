<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;

/**
 * Close context: the metamorphosis failed with a thrown exception.
 */
final class BeingErrorCloseContext extends AbstractContext implements JsonSerializable
{
    public const string TYPE = 'being_error_close';

    public const string SCHEMA_URL = 'https://be-framework.org/schemas/being-error-close.json';

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
