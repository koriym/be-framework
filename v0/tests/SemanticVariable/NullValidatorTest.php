<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use PHPUnit\Framework\TestCase;
use stdClass;

final class NullValidatorTest extends TestCase
{
    private NullValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new NullValidator();
    }

    public function testValidateAlwaysReturnsNullErrors(): void
    {
        $errors = $this->validator->validate('email', 'invalid-email');

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testValidateWithAttributesAlwaysReturnsNullErrors(): void
    {
        $errors = $this->validator->validateWithAttributes('age', ['Teen'], -5);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testValidateAndThrowNeverThrows(): void
    {
        // Should never throw regardless of invalid input
        $this->validator->validateAndThrow('email', 'completely-invalid');
        $this->validator->validateAndThrow('age', -999);

        // If we reach this line without throwing an exception, the test passes
        $this->expectNotToPerformAssertions();
    }

    public function testValidateObjectAlwaysReturnsNullErrors(): void
    {
        $object = new stdClass();
        $object->email = 'invalid';
        $object->age = -5;

        $errors = $this->validator->validateObject($object);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testImplementsSemanticValidatorInterface(): void
    {
        $this->assertInstanceOf(SemanticValidatorInterface::class, $this->validator);
    }
}
