<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Integration;

use Be\Framework\BecomingInterface;
use Be\Framework\Module\BeModule;
use Be\Framework\SemanticVariable\NullValidator;
use Be\Framework\SemanticVariable\SemanticValidatorInterface;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use MyVendor\MyApp\Ldd\RegisterUser\RegisteredUser;
use MyVendor\MyApp\Ldd\RegisterUser\UnverifiedEmail;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

use function assert;
use function file_get_contents;
use function is_array;
use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * End-to-end verification of the Log-Driven Development loop.
 *
 * Steps:
 *   1. Load the hand-authored `$been` spec (`tests/Integration/Ldd/RegisterUser/been.json`).
 *   2. Run the generated classes (`UnverifiedEmail` -> `RegisteredUser`)
 *      through the framework's `Becoming` engine under full DI.
 *   3. Flush the `SemanticLoggerInterface` singleton and normalise the result.
 *   4. Assert the produced log equals the authored spec.
 *
 * If this test passes, the LDD loop closes: the JSON is simultaneously the
 * specification, the example, and the test.
 */
final class LddLoopTest extends TestCase
{
    public function testRegisterUserLoopClosesOnAuthoredSpec(): void
    {
        $injector = new Injector(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->install(new BeModule());
                $this->bind(SemanticValidatorInterface::class)->to(NullValidator::class);
            }
        });
        $becoming = $injector->getInstance(BecomingInterface::class);
        $semanticLogger = $injector->getInstance(SemanticLoggerInterface::class);
        assert($becoming instanceof BecomingInterface);
        assert($semanticLogger instanceof SemanticLoggerInterface);

        $result = $becoming(new UnverifiedEmail('alice@example.com'));
        $this->assertInstanceOf(RegisteredUser::class, $result);

        $produced = $this->normalise($semanticLogger->flush()->toArray());

        $authoredJson = file_get_contents(__DIR__ . '/Ldd/RegisterUser/been.json');
        $this->assertNotFalse($authoredJson);
        /** @var array<string, mixed> $authored */
        $authored = json_decode($authoredJson, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($authored, $produced);
    }

    /**
     * Round-trip through json_encode/decode to flatten nested value objects
     * (`FinalDestination`, etc.) into plain arrays for structural comparison.
     *
     * @param  array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function normalise(array $data): array
    {
        $encoded = json_encode($data, JSON_THROW_ON_ERROR);
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($encoded, true, 512, JSON_THROW_ON_ERROR);
        assert(is_array($decoded));

        return $decoded;
    }
}
