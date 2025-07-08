<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Compiler\Exception\ScriptDirNotReadable;
use Ray\Compiler\Exception\Unbound;
use Ray\Di\Annotation\ScriptDir;
use Ray\Di\Name;

use function file_exists;
use function in_array;
use function is_dir;
use function is_readable;
use function realpath;
use function spl_autoload_register;
use function sprintf;
use function str_replace;

/**
 * Compiled Injector
 *
 * An injector that requires all bindings to be pre-compiled into PHP code.
 * Use Ray\Compiler\Compiler to compile the bindings.
 * Runtime compilation is not supported.
 *
 * @psalm-import-type ScriptDir from Types
 * @psalm-import-type Singletons from Types
 */
final class CompiledInjector implements ScriptInjectorInterface
{
    /** @var ScriptDir */
    private $scriptDir;

    /**
     * Singleton instance container
     *
     * @var Singletons
     */
    private $singletons = [];

    /** @var array<ScriptDir> */
    private static $scriptDirs = [];

    /**
     * @param ScriptDir $scriptDir generated instance script folder path
     *
     * @psalm-suppress UnresolvableInclude
     * @ScriptDir
     */
    #[ScriptDir]
    public function __construct(string $scriptDir)
    {
        $realPath = realpath($scriptDir);
        if ($realPath === false || ! is_dir($realPath) || ! is_readable($realPath)) {
            $message = sprintf('Script directory "%s" is not readable. See https://ray-di.github.io/Ray.Compiler/error/ScriptDirNotReadable', $scriptDir);

            throw new ScriptDirNotReadable($message);
        }

        /** @psalm-var ScriptDir $realPath */
        $this->scriptDir = $realPath;
        $this->registerLoader();
    }

    public function __wakeup()
    {
        $this->registerLoader();
    }

    /**
     * {@inheritDoc}
     *
     * @template T
     * @SuppressWarnings(PHPMD.UnusedLocalVariable) // @phpstan-ignore-line
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        $dependencyIndex = $interface . '-' . $name;
        if (isset($this->singletons[$dependencyIndex])) {
            return $this->singletons[$dependencyIndex];
        }

        $scriptFile = sprintf('%s/%s.php', $this->scriptDir, str_replace('\\', '_', $dependencyIndex));
        if (! file_exists($scriptFile)) {
            throw new Unbound($dependencyIndex); // Binding not found
        }

        /** @psalm-suppress  UnsupportedPropertyReferenceUsage */
        $singletons = &$this->singletons;
        $scriptDir = realpath($this->scriptDir);

        // $scriptDir, $Singletons, and $dependencyIndex can be used in the included file
        /** @var mixed $instance */
        $instance = require $scriptFile;

        /** @psalm-var T $instance */
        return $instance;
    }

    private function registerLoader(): void
    {
        $scriptDir = $this->scriptDir;
        if (in_array($scriptDir, self::$scriptDirs, true)) {
            return;
        }

        if (self::$scriptDirs === []) {
            spl_autoload_register(
                static function (string $class): void {
                    foreach (self::$scriptDirs as $scriptDir) {
                        $file = sprintf('%s/%s.php', $scriptDir, str_replace('\\', '_', $class));
                        if (file_exists($file)) {
                            require_once $file; // @codeCoverageIgnore
                        }
                    }
                }
            );
        }

        self::$scriptDirs[] = $scriptDir;
    }
}
