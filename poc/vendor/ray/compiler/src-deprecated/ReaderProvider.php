<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Doctrine\Common\Annotations\Reader;
use Ray\Di\ProviderInterface;
use Ray\ServiceLocator\ServiceLocator;

/**
 * @deprecated
 * @codeCoverageIgnore
 */
final class ReaderProvider implements ProviderInterface // @phpstan-ignore-line
{
    public function get(): Reader
    {
        return ServiceLocator::getReader();
    }
}
