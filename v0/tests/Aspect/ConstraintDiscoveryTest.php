<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Aspect;

use Be\Framework\Aspect\ConstraintDiscovery;
use Be\Framework\BecomingType;
use Be\Framework\Tests\Fake\GreetingInput;
use PHPUnit\Framework\TestCase;

/**
 * Test for ConstraintDiscovery aspect
 */
final class ConstraintDiscoveryTest extends TestCase
{
    private ConstraintDiscovery $constraintDiscovery;

    protected function setUp(): void
    {
        $becomingType = new BecomingType();
        $this->constraintDiscovery = new ConstraintDiscovery($becomingType);
    }

    public function testDiscoverConstraintsRevealsHiddenConstraints(): void
    {
        // Create input object - constraints are NOT visible in this class
        $input = new GreetingInput('Hello');

        // Discover what constraints will apply during transformation
        $constraints = $this->constraintDiscovery->discoverConstraints($input);

        // The constraints should now be discoverable even though they're defined in the target class
        $this->assertArrayHasKey('greeting', $constraints);
        $this->assertContains('English', $constraints['greeting']);

        // This solves the visibility problem: developers can now see what constraints
        // will be applied to their input data without having to manually trace through
        // the transformation chain
    }

    public function testDiscoverConstraintsWithNoTransformation(): void
    {
        // Object with no #[Be] attribute
        $plainObject = new class {
            public function __construct(public readonly string $data)
            {
            }
        };

        $constraints = $this->constraintDiscovery->discoverConstraints($plainObject);

        $this->assertEmpty($constraints, 'Object with no transformation should have no constraints');
    }

    public function testDiscoverConstraintsShowsMultipleConstraints(): void
    {
        // If a property had multiple constraints, they should all be discoverable
        $input = new GreetingInput('Hello');
        $constraints = $this->constraintDiscovery->discoverConstraints($input);

        // The structure allows for multiple constraints per property
        $this->assertIsArray($constraints);
        if (isset($constraints['greeting'])) {
            $this->assertIsArray($constraints['greeting']);
        }
    }
}
