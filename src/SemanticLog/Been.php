<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Koriym\SemanticLogger\AbstractContext;
use Koriym\SemanticLogger\SemanticLoggerInterface;

/**
 * Immutable carrier of a Final object's `$been` — ordered, typed evidence of
 * what made it what it is.
 *
 * `Been` holds a list of `AbstractContext` events. Each call to `with()`
 * appends a new event and returns a new `Been`; the original is untouched.
 * Simultaneously, `with()` writes the event to the live
 * `SemanticLoggerInterface` stream via `event()`, so the in-memory `$been`
 * and the produced semantic log are the same thing viewed from two sides.
 *
 * The logger reference is held intentionally. This is not a violation of
 * immutability — the event list is immutable, and the logger is external
 * state. Do not "clean this up" by removing the logger call: deferring the
 * event stream to after the constructor returns would lose spatial/temporal
 * ordering and would drop events emitted before a constructor throw.
 *
 * Exception safety: `with()` writes to the stream **before** returning the
 * new `Been`. If a later step throws, events already emitted remain on the
 * stream — they reflect what actually happened, not what succeeded. This is
 * correct logging semantics; do not expect transactional rollback.
 */
final class Been
{
    /** @param list<AbstractContext> $events */
    public function __construct(
        private readonly SemanticLoggerInterface $logger,
        public readonly array $events = [],
    ) {
    }

    public function with(AbstractContext $context): self
    {
        $this->logger->event($context);

        return new self($this->logger, [...$this->events, $context]);
    }
}
