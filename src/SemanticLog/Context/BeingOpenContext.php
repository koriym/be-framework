<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use Be\Framework\Types;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;
use stdClass;

/**
 * Open context for an intermediate being — the target class carries a #[Be] attribute,
 * so the metamorphosis will continue with another step after this one.
 *
 * Captures what the current being is trying to become, along with the inputs it will
 * receive and the services it will be injected with.
 *
 * @psalm-import-type ImmanentSources from Types
 * @psalm-import-type TranscendentSources from Types
 * @psalm-import-type ObjectProperties from Types
 */
final class BeingOpenContext extends AbstractContext implements JsonSerializable
{
    public const string TYPE = 'being_open';

    public const string SCHEMA_URL = 'https://be-framework.org/schemas/being-open.json';

    /**
     * @param class-string        $from   Class being transformed from
     * @param class-string        $be     Target class FQCN for the next metamorphosis
     * @param ImmanentSources     $input  #[Input] parameter names mapped to their origin path on the previous being
     * @param TranscendentSources $inject #[Inject] parameter names mapped to the interface / service identifier from DI
     */
    public function __construct(
        public readonly string $from,
        public readonly string $be,
        public readonly array $input = [],
        public readonly array $inject = [],
    ) {
    }

    /** @return ObjectProperties */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'from' => $this->from,
            'be' => $this->be,
            'input' => empty($this->input) ? new stdClass() : (object) $this->input,
            'inject' => empty($this->inject) ? new stdClass() : (object) $this->inject,
        ];
    }
}
