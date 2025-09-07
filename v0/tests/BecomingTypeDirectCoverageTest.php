<?php

declare(strict_types=1);

namespace Be\Framework\Tests;

use Be\Framework\BecomingType;
use DirectIntersectionFailTest\PartialImpl;
use IntersectionDirectTest\InvalidIntersectionImpl;
use IntersectionDirectTest\ValidIntersectionImpl;
use PHPUnit\Framework\TestCase;
use stdClass;

use function fclose;
use function tmpfile;

use const PHP_VERSION_ID;

/**
 * Direct test to cover specific uncovered lines in BecomingType
 */
final class BecomingTypeDirectCoverageTest extends TestCase
{
    private BecomingType $becomingType;

    protected function setUp(): void
    {
        $this->becomingType = new BecomingType();
    }

    public function testActualIntersectionTypeHandling(): void
    {
        // Test lines 70, 99-101, 105-106: Real intersection type execution
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('Intersection types require PHP 8.1+');
        }

        // Create a REAL intersection type scenario by making a class with intersection constructor param
        eval('
        namespace IntersectionDirectTest;
        
        interface TestableInterface {
            public function test(): void;
        }
        
        interface CountableInterface {
            public function count(): int;
        }
        
        class IntersectionTargetClass {
            public function __construct(public TestableInterface&CountableInterface $param) {}
        }
        
        class ValidIntersectionImpl implements TestableInterface, CountableInterface {
            public function test(): void {}
            public function count(): int { return 1; }
        }
        
        class InvalidIntersectionImpl implements TestableInterface {
            public function test(): void {}
            // Missing CountableInterface implementation
        }
        ');

        // Test 1: Valid intersection implementation (should hit lines 99-101 loop and line 105 success)
        $validImpl = new ValidIntersectionImpl();
        $inputValid = new class ($validImpl) {
            public function __construct(public ValidIntersectionImpl $param)
            {
            }
        };

        $result1 = $this->becomingType->match($inputValid, 'IntersectionDirectTest\IntersectionTargetClass');
        $this->assertTrue($result1, 'Valid intersection implementation should match');

        // Test 2: Invalid intersection implementation (should hit line 100-101 failure path)
        $invalidImpl = new InvalidIntersectionImpl();
        $inputInvalid = new class ($invalidImpl) {
            public function __construct(public InvalidIntersectionImpl $param)
            {
            }
        };

        $result2 = $this->becomingType->match($inputInvalid, 'IntersectionDirectTest\IntersectionTargetClass');
        $this->assertFalse($result2, 'Invalid intersection implementation should not match');
    }

    public function testUnknownReflectionTypeHandling(): void
    {
        // Test lines 77-78: Force unknown ReflectionType scenario

        // The easiest way to trigger this is actually with a very complex type that PHP can't handle
        // But for practical purposes, let's create a scenario where type checking fails

        $testObject = new class {
            public mixed $complexProperty = 'some_value';
        };

        // Create a target class expecting a specific type that doesn't match
        $targetClass = new class (42) {
            public function __construct(public int $complexProperty)
            {
            } // Expects int, gets string-in-mixed
        };

        $result = $this->becomingType->match($testObject, $targetClass::class);
        // This should fail because mixed contains string but target expects int
        $this->assertFalse($result, 'Mixed property with string should not match int parameter');
    }

    public function testTrueIntersectionTypeFailure(): void
    {
        // More direct test for intersection type failure scenario
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('Intersection types require PHP 8.1+');
        }

        eval('
        namespace DirectIntersectionFailTest;
        
        interface RequiredA { 
            public function methodA(): void; 
        }
        
        interface RequiredB { 
            public function methodB(): void; 
        }
        
        class DirectTestClass {
            public function __construct(public RequiredA&RequiredB $both) {}
        }
        
        class PartialImpl implements RequiredA {
            public function methodA(): void {}
        }
        ');

        $partialImpl = new PartialImpl();
        $input = new class ($partialImpl) {
            public function __construct(public PartialImpl $both)
            {
            }
        };

        $result = $this->becomingType->match($input, 'DirectIntersectionFailTest\DirectTestClass');
        $this->assertFalse($result, 'Partial implementation should fail intersection type check');
    }

    public function testSpecificTypeFailures(): void
    {
        // Test scenarios that should trigger different failure paths
        $scenarios = [
            // String to int failure
            [
                'input' => new class {
                    public string $value = 'text';
                },

                'target' => new class (0) {
                    public function __construct(public int $value)
                    {
                    }
                },
                'shouldMatch' => false,
                'description' => 'String should not match int',
            ],
            // Object to string failure
            [

                'input' => new class {
                    public object $value;

                    public function __construct()
                    {
                        $this->value = new stdClass();
                    }
                },

                'target' => new class ('') {
                    public function __construct(public string $value)
                    {
                    }
                },
                'shouldMatch' => false,
                'description' => 'Object should not match string',
            ],
            // Array to int failure
            [
                'input' => new class {
                    public array $value = [1, 2, 3];
                },

                'target' => new class (0) {
                    public function __construct(public int $value)
                    {
                    }
                },
                'shouldMatch' => false,
                'description' => 'Array should not match int',
            ],
        ];

        foreach ($scenarios as $i => $scenario) {
            $result = $this->becomingType->match($scenario['input'], $scenario['target']::class);
            $this->assertSame($scenario['shouldMatch'], $result, "Scenario {$i}: {$scenario['description']}");
        }
    }

    public function testResourceTypeHandling(): void
    {
        // Test the default case in getValueType method with resource
        $resource = tmpfile();
        if ($resource === false) {
            $this->markTestSkipped('Could not create temporary file resource');
        }

        $input = new class ($resource) {
            public function __construct(public mixed $resourceProp)
            {
            }
        };

        $targetClass = new class (null) {
            public function __construct(public mixed $resourceProp)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Resource should match mixed type');

        fclose($resource);
    }
}
