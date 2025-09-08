<?php

declare(strict_types=1);

namespace Be\Framework\Attribute;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class SemanticTagTest extends TestCase
{
    public function testSemanticTagCanBeInstantiated(): void
    {
        $description = 'Test semantic tag description';
        $semanticTag = new SemanticTag($description);

        $this->assertInstanceOf(SemanticTag::class, $semanticTag);
        $this->assertSame($description, $semanticTag->description);
    }

    public function testSemanticTagIsReadonly(): void
    {
        $description = 'Age constraint for adults';
        $semanticTag = new SemanticTag($description);

        $this->assertSame($description, $semanticTag->description);

        // Verify the property is readonly (this would cause error if not readonly)
        $reflection = new ReflectionProperty($semanticTag, 'description');
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testSemanticTagWithEmptyDescription(): void
    {
        $semanticTag = new SemanticTag('');

        $this->assertSame('', $semanticTag->description);
    }

    public function testSemanticTagWithLongDescription(): void
    {
        $longDescription = 'This is a very long description for a semantic tag that provides detailed information about the validation constraints and business rules that should be applied when this semantic tag is used in the context of hierarchical semantic validation';
        $semanticTag = new SemanticTag($longDescription);

        $this->assertSame($longDescription, $semanticTag->description);
    }
}
