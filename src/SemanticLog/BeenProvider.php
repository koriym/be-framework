<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Koriym\SemanticLogger\SemanticLoggerInterface;
use Override;
use Ray\Di\ProviderInterface;

/**
 * Provides a fresh empty `Been` accumulator to each `#[Inject] Been $been`.
 *
 * Scope is per-injection, not singleton — a new `Been` per metamorphosis
 * step. For the single-step toy example this is all that is needed.
 *
 * Limitation: this provider does not thread `Been` across a multi-step
 * metamorphosis chain. In a chain `A -> B -> C`, step B's injected `$been`
 * does not contain step A's events. If/when multi-step accumulation is
 * needed, the recommended route is `#[Input] Been $been` threading, which
 * reuses `BecomingArguments`' existing name-matched `#[Input]` resolution
 * and requires no engine changes — the first step simply constructs an
 * empty `Been` and assigns it to a `public readonly Been $been` property,
 * and every downstream step receives it via `#[Input]`.
 *
 * @implements ProviderInterface<Been>
 */
final class BeenProvider implements ProviderInterface
{
    public function __construct(
        private readonly SemanticLoggerInterface $logger,
    ) {
    }

    #[Override]
    public function get(): Been
    {
        return new Been($this->logger);
    }
}
