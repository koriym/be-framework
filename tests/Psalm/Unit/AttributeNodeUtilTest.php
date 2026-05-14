<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Psalm\Unit;

use Be\Framework\Psalm\Internal\AttributeNodeUtil;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use Psalm\Aliases;

final class AttributeNodeUtilTest extends TestCase
{
    public function testHasAttributeResolvesViaUseAlias(): void
    {
        $code = <<<'PHP'
        <?php
        namespace App;
        use Ray\InputQuery\Attribute\Input;
        final class Foo {
            public function __construct(#[Input] public string $name) {}
        }
        PHP;

        $class = $this->parseFirstClass($code);
        $aliases = new Aliases('App', uses: ['input' => 'Ray\\InputQuery\\Attribute\\Input']);

        $ctor = AttributeNodeUtil::findConstructor($class);
        self::assertNotNull($ctor);

        self::assertTrue(
            AttributeNodeUtil::hasAttribute($ctor->params[0]->attrGroups, 'Ray\\InputQuery\\Attribute\\Input', $aliases),
        );

        self::assertFalse(
            AttributeNodeUtil::hasAttribute($ctor->params[0]->attrGroups, 'Ray\\Di\\Di\\Inject', $aliases),
        );
    }

    public function testHasAttributeResolvesAliasedImport(): void
    {
        $code = <<<'PHP'
        <?php
        namespace App;
        use Ray\InputQuery\Attribute\Input as In;
        final class Foo {
            public function __construct(#[In] public string $name) {}
        }
        PHP;

        $class = $this->parseFirstClass($code);
        $aliases = new Aliases('App', uses: ['in' => 'Ray\\InputQuery\\Attribute\\Input']);

        $ctor = AttributeNodeUtil::findConstructor($class);
        self::assertNotNull($ctor);

        self::assertTrue(
            AttributeNodeUtil::hasAttribute($ctor->params[0]->attrGroups, 'Ray\\InputQuery\\Attribute\\Input', $aliases),
        );
    }

    public function testHasAttributeWithFullyQualifiedNameWithoutAliases(): void
    {
        $code = <<<'PHP'
        <?php
        namespace App;
        final class Foo {
            public function __construct(#[\Ray\InputQuery\Attribute\Input] public string $name) {}
        }
        PHP;

        $class = $this->parseFirstClass($code);
        $aliases = new Aliases('App');

        $ctor = AttributeNodeUtil::findConstructor($class);
        self::assertNotNull($ctor);

        self::assertTrue(
            AttributeNodeUtil::hasAttribute($ctor->params[0]->attrGroups, 'Ray\\InputQuery\\Attribute\\Input', $aliases),
        );
    }

    public function testFindConstructorReturnsNullWhenAbsent(): void
    {
        $code = <<<'PHP'
        <?php
        namespace App;
        final class Foo { public function bar(): void {} }
        PHP;

        $class = $this->parseFirstClass($code);

        self::assertNull(AttributeNodeUtil::findConstructor($class));
    }

    private function parseFirstClass(string $code): Class_
    {
        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromComponents(8, 3));
        $ast = $parser->parse($code);
        self::assertNotNull($ast);

        foreach ($ast as $stmt) {
            if ($stmt instanceof Namespace_) {
                foreach ($stmt->stmts as $sub) {
                    if ($sub instanceof Class_) {
                        return $sub;
                    }
                }
            }

            if ($stmt instanceof Class_) {
                return $stmt;
            }
        }

        self::fail('No class found in parsed AST');
    }
}
