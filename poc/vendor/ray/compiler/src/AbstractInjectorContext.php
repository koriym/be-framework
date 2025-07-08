<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Doctrine\Common\Cache\CacheProvider;
use Ray\Di\AbstractModule;
use Ray\Di\Annotation\ScriptDir;

/** @psalm-import-type ScriptDir from Types */
abstract class AbstractInjectorContext implements LazyModuleInterface
{
    /**
     * @var ScriptDir
     * @readonly
     */
    public $tmpDir;

    /** @param ScriptDir $tmpDir */
    public function __construct(string $tmpDir)
    {
        $this->tmpDir = $tmpDir;
    }

    abstract public function __invoke(): AbstractModule;

    abstract public function getCache(): CacheProvider;

    /**
     * Return array of cacheable singleton class names
     *
     * @return array<class-string>
     */
    public function getSavedSingleton(): array
    {
        return [];
    }
}
