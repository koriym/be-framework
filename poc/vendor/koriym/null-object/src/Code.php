<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use Koriym\NullObject\Exception\LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

use function array_slice;
use function assert;
use function end;
use function explode;
use function file;
use function filemtime;
use function hash;
use function implode;
use function is_int;
use function is_string;
use function sprintf;
use function str_replace;

use const PHP_EOL;
use const PHP_VERSION_ID;

/** @template T of object */
final class Code
{
    private const CLASS_TEMPLATE = <<<'EOT'
%sclass %s implements \%s
{
%s}
EOT;

    /** @param class-string $interface */
    public function newInstance(string $interface): object
    {
        $generated = $this->generate($interface);
        eval($generated->code);
        $class = $generated->class;

        /** @psalm-suppress MixedMethodCall */
        $object = new $class();
        assert($object instanceof $interface);

        return $object;
    }

    /**
     * @param class-string $interface
     *
     * @psalm-suppress InvalidReturnStatement
     */
    public function generate(string $interface, ?string $fqcn = null): GeneratedCode
    {
        $fqcn = $fqcn ?: $this->getNullClassName(new ReflectionClass($interface));
        $class = new ReflectionClass($interface);

        $classMeta = $this->getClassMeta($class);
        $parts = explode('\\', $fqcn);
        $shortName = end($parts);
        $code = sprintf(self::CLASS_TEMPLATE, $classMeta, $shortName, $interface, $this->getMethods($class));

        /** @var class-string $fqcn */
        return new GeneratedCode($fqcn, $code);
    }

    /** @param ReflectionClass<object> $class */
    private function getClassMeta(ReflectionClass $class): string
    {
        $file = (array) file((string) $class->getFileName());
        $fileMeta = array_slice($file, 1, $class->getStartLine() - 2);

        return implode('', $fileMeta);
    }

    /** @param ReflectionClass<object> $class */
    private function getMethods(ReflectionClass $class): string
    {
        $methods = $class->getMethods();
        $methodStrings = [];
        $file = (array) file((string) $class->getFileName());
        foreach ($methods as $method) {
            $interfaceMethod = implode(PHP_EOL, array_slice($file, $method->getStartLine() - 1, $method->getEndLine()  - $method->getStartLine() + 1));
            $methodSignature = str_replace(';', "\n    {\n    }", $interfaceMethod);
            $methodStrings[] = $this->getMethodMeta($method) . $methodSignature;
        }

        return implode(PHP_EOL, $methodStrings);
    }

    private function getMethodMeta(ReflectionMethod $method): string
    {
        $attr =  PHP_VERSION_ID >= 80000 ? sprintf("    %s\n", $this->getAttributes($method)) : '';

        return sprintf("    %s\n%s", $method->getDocComment(), $attr);
    }

    /** @psalm-suppress MixedAssignment */
    private function getAttributes(ReflectionMethod $method): string
    {
        $attrs = $method->getAttributes();
        $attrList = [];
        if ($attrs) {
            foreach ($attrs as $attr) {
                /** @psalm-var ReflectionAttribute $attr */
                $attrList[] = $this->getAttribute($attr); // @phpstan-ignore-line
            }
        }

        return implode(PHP_EOL, $attrList);
    }

    /**
     * @psalm-param ReflectionAttribute $attr
     * @phpstan-param ReflectionAttribute<object> $attr
     */
    private function getAttribute(ReflectionAttribute $attr): string
    {
        /** @var array<float|int|string> $args */
        $args = $attr->getArguments();
        $argList = [];
        foreach ($args as $key => $arg) {
            $val = is_string($arg) ? sprintf("'%s'", $arg) : (string) $arg;
            $argList[$key] = is_int($key) ? $val : "{$key}: $val";
        }

        /** @var class-string $class */
        $class = $attr->getName();
        $shortName = (new ReflectionClass($class))->getShortName();

        return sprintf('#[%s(%s)]', $shortName, implode(', ', $argList));
    }

    /**
     * @param ReflectionClass<object> $class
     *
     * @return class-string
     *
     * @psalm-suppress MoreSpecificReturnType
     */
    public function getNullClassName(ReflectionClass $class): string
    {
        /** @psalm-suppress LessSpecificReturnStatement */
        return $class->getName() . $this->getTime($class) . 'Null'; // @phpstan-ignore-line
    }

    /** @param ReflectionClass<object> $class */
    private function getTime(ReflectionClass $class): string
    {
        $time = 0;
        while ($class instanceof ReflectionClass) {
            $fileName = $class->getFileName();
            if ($fileName === false) {
                throw new LogicException();
            }

            $time .= filemtime($class->getFileName()); // @phpstan-ignore-line
            $class = $class->getParentClass();
        }

        return hash('crc32b', (string) $time);
    }
}
