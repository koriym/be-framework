<?php

declare(strict_types=1);

namespace Koriym\NullObject;

/** @template T of object */
final class NullObject implements NullObjectInterface
{
    /**
     * {@inheritDoc}
     */
    public function newInstance(string $interface): object
    {
        return (new Code())->newInstance($interface); // @phpstan-ignore-line
    }

    /**
     * {@inheritDoc}
     */
    public function generate(string $interface): GeneratedCode
    {
        return (new Code())->generate($interface);
    }

    /**
     * {@inheritDoc}
     */
    public function save(string $interface, string $scriptDir): string
    {
        $nullFile = new NullObjectFile($interface, $scriptDir);
        $maybeClassName = $nullFile->include();
        if ($maybeClassName) {
            // @codeCoverageIgnoreStart
            return $maybeClassName;
            // @codeCoverageIgnoreEnd
        }

        return $nullFile->save();
    }
}
