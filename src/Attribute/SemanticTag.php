<?php

declare(strict_types=1);

namespace Be\Framework\Attribute;

use Attribute;

/**
 * Marks a class as a SemanticTag for hierarchical semantic validation
 *
 * SemanticTags provide hierarchical semantic validation by adding constraints
 * to basic variable contracts. This attribute identifies classes that function
 * as SemanticTags and provides semantic metadata.
 *
 * Pattern: Basic contract (variable name) + SemanticTag constraint = Hierarchical semantics
 *
 * Examples:
 *   #[SemanticTag(description: "Age constraint for teenagers")]
 *   final class Teen {}
 *
 *   #[SemanticTag(description: "Price tier for premium products")]
 *   final class Premium {}
 *
 * @todo Add appliesTo domain specification for SemanticTags
 *       - Teen → age domain
 *       - Premium → price domain
 *       - Adult/Senior → user/person domain
 *       - Enable automatic applicability checking
 * @todo ALPS descriptor generation from SemanticTag metadata
 *       - name (class name) → ALPS descriptor id
 *       - description → ALPS descriptor doc
 *       - type → ALPS descriptor type
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class SemanticTag
{
    public function __construct(
        public readonly string $description,
    ) {
    }
}
