<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use PHPUnit\Framework\TestCase;
use ReflectionFunction;

final class ScalarParameterRequiresNamedTest extends TestCase
{
    public function testCreate(): void
    {
        // Create a ReflectionParameter for testing
        $reflection = new ReflectionFunction(static function (string $testParam): void {
        });
        $parameters = $reflection->getParameters();
        $param = $parameters[0];

        $exception = ScalarParameterRequiresNamed::create($param);

        $this->assertSame(
            'Scalar parameter "testParam" requires #[Named] attribute or default value',
            $exception->getMessage(),
        );
    }
}
