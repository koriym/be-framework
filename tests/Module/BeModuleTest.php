<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Module;

use Be\Framework\BecomingInterface;
use Be\Framework\Module\BeModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

final class BeModuleTest extends TestCase
{
    public function testModuleCanBeInstantiated(): void
    {
        $injector = new Injector(new BeModule());
        $becomming = $injector->getInstance(BecomingInterface::class);
        $this->assertInstanceOf(BecomingInterface::class, $becomming);
    }
}
