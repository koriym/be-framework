<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\BecomingArguments;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

final class HierarchicalSemanticValidationWithFakeTest extends TestCase
{
    private SemanticValidator $validator;

    protected function setUp(): void
    {
        $injector = new Injector();
        $becomingArguments = new BecomingArguments($injector);
        // Use Be\Framework\SemanticVariables namespace for Fake classes
        $this->validator = new SemanticValidator($becomingArguments, 'Be\\Framework\\SemanticVariables');
    }

    public function testBasicUserAgeValidationPasses(): void
    {
        $errors = $this->validator->validate('user_age', 25);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testBasicUserAgeValidationFailsForNegativeAge(): void
    {
        $errors = $this->validator->validate('user_age', -5);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('User age cannot be negative: -5', $errors->getMessages()[0]);
    }

    public function testBasicUserAgeValidationFailsForTooOldAge(): void
    {
        $errors = $this->validator->validate('user_age', 150);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('User age cannot exceed 120: 150', $errors->getMessages()[0]);
    }

    public function testHierarchicalAdultValidationPasses(): void
    {
        $errors = $this->validator->validateWithAttributes('user_age', ['Adult'], 25);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testHierarchicalAdultValidationFailsForTooYoung(): void
    {
        $errors = $this->validator->validateWithAttributes('user_age', ['Adult'], 16);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('Adult age must be at least 18: 16', $errors->getMessages()[0]);
    }

    public function testHierarchicalTeenValidationPasses(): void
    {
        $errors = $this->validator->validateWithAttributes('user_age', ['Teen'], 16);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testHierarchicalTeenValidationFailsForTooYoung(): void
    {
        $errors = $this->validator->validateWithAttributes('user_age', ['Teen'], 10);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('Teen age must be at least 13: 10', $errors->getMessages()[0]);
    }

    public function testHierarchicalTeenValidationFailsForTooOld(): void
    {
        $errors = $this->validator->validateWithAttributes('user_age', ['Teen'], 25);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('Teen age must be at most 19: 25', $errors->getMessages()[0]);
    }

    public function testHierarchicalSeniorValidationPasses(): void
    {
        $errors = $this->validator->validateWithAttributes('user_age', ['Senior'], 70);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testHierarchicalSeniorValidationFailsForTooYoung(): void
    {
        $errors = $this->validator->validateWithAttributes('user_age', ['Senior'], 60);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('Senior age must be at least 65: 60', $errors->getMessages()[0]);
    }

    public function testBasicProductPriceValidationPasses(): void
    {
        $errors = $this->validator->validate('product_price', 25.99);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testBasicProductPriceValidationFailsForNegative(): void
    {
        $errors = $this->validator->validate('product_price', -10.50);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('Product price cannot be negative: -10.5', $errors->getMessages()[0]);
    }

    public function testHierarchicalPremiumPriceValidationPasses(): void
    {
        $errors = $this->validator->validateWithAttributes('product_price', ['Premium'], 150.00);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testHierarchicalPremiumPriceValidationFailsForTooLow(): void
    {
        $errors = $this->validator->validateWithAttributes('product_price', ['Premium'], 50.00);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('Premium price must be at least 100: 50', $errors->getMessages()[0]);
    }

    public function testHierarchicalBudgetPriceValidationPasses(): void
    {
        $errors = $this->validator->validateWithAttributes('product_price', ['Budget'], 25.00);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testHierarchicalBudgetPriceValidationFailsForTooHigh(): void
    {
        $errors = $this->validator->validateWithAttributes('product_price', ['Budget'], 75.00);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('Budget price must be at most 50: 75', $errors->getMessages()[0]);
    }

    public function testMultiArgumentValidationWithProductPriceComparison(): void
    {
        $errors = $this->validator->validate('product_price', 25.00, 25.00);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('Product prices must be different for comparison', $errors->getMessages()[0]);
    }

    public function testMultiArgumentValidationWithProductPriceComparisonPasses(): void
    {
        $errors = $this->validator->validate('product_price', 25.00, 30.00);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testGameScoreBasicValidationPasses(): void
    {
        $errors = $this->validator->validate('game_score', 5000);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testGameScoreHighScoreValidationPasses(): void
    {
        $errors = $this->validator->validateWithAttributes('game_score', ['HighScore'], 50000);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testGameScoreHighScoreValidationFailsForTooLow(): void
    {
        $errors = $this->validator->validateWithAttributes('game_score', ['HighScore'], 5000);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('High score must be at least 10000: 5000', $errors->getMessages()[0]);
    }

    public function testGameScorePersonalBestValidationPasses(): void
    {
        $errors = $this->validator->validateWithAttributes('game_score', ['PersonalBest'], 2500);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testGameScorePersonalBestValidationFailsForTooLow(): void
    {
        $errors = $this->validator->validateWithAttributes('game_score', ['PersonalBest'], 500);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('Personal best must be at least 1000: 500', $errors->getMessages()[0]);
    }

    public function testNonExistentSemanticVariableReturnsNoErrors(): void
    {
        $errors = $this->validator->validate('non_existent_variable', 'some value');

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testNoMatchingValidationMethodsReturnsNoErrors(): void
    {
        // Test with too many arguments for any validation method
        $errors = $this->validator->validate('user_age', 25, 30, 35);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }
}
