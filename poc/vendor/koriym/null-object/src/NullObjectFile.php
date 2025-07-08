<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use Koriym\NullObject\Exception\FileNotWritable;
use ReflectionClass;

use function assert;
use function class_exists;
use function dirname;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function is_string;
use function mkdir;
use function rename;
use function sprintf;
use function str_replace;
use function tempnam;
use function unlink;

final class NullObjectFile
{
    /** @var class-string */
    private $className;

    /** @var string */
    private $scriptDir;

    /** @var class-string */
    private $interface;

    /** @param class-string $interface */
    public function __construct(string $interface, string $scriptDir)
    {
        $code = new Code();
        $this->className = $code->getNullClassName(new ReflectionClass($interface));
        $this->scriptDir = $scriptDir;
        $this->interface = $interface;
    }

    /** @return class-string|null */
    public function include(): ?string
    {
        $filePath = sprintf(
            '%s/%s.php',
            $this->scriptDir,
            str_replace('\\', '_', $this->className)
        );

        if (! file_exists($filePath)) {
            return null;
        }

        if (! class_exists($this->className)) {
            // @codeCoverageIgnoreStart
            /** @psalm-suppress UnresolvableInclude */
            require_once $filePath;
            // @codeCoverageIgnoreEnd
        }

        return $this->className;
    }

    /** @return class-string */
    public function save(): string
    {
        $code = (new Code())->generate($this->interface, $this->className);
        $this->filePutContents($code->class, sprintf("<?php\n%s", $code->code));

        return $this->className;
    }

    /** @param class-string $className */
    public function filePutContents(string $className, string $content): void
    {
        $filename = $this->getFilePath($className);
        $dir = dirname($filename);
        ! is_dir($dir) && ! mkdir($dir, 0777, true) && ! is_dir($dir);
        $tmpFile = tempnam(dirname($filename), 'swap');
        if (is_string($tmpFile) && file_put_contents($tmpFile, $content) && @rename($tmpFile, $filename)) {
            if (! class_exists($className, false)) {
                assert(file_exists($filename));
                require $filename;
            }

            return;
        }

        // @codeCoverageIgnoreStart
        @unlink((string) $tmpFile);

        throw new FileNotWritable($filename);
        // @codeCoverageIgnoreEnd
    }

    private function getFilePath(string $className): string
    {
        return sprintf(
            '%s/%s.php',
            $this->scriptDir,
            str_replace('\\', '_', $className)
        );
    }
}
