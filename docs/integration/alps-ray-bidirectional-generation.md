# ALPS and Be Framework: Bidirectional Generation

> "The best way to understand a design is to implement it. The best way to implement a design is to understand it fully." — Anonymous

## Introduction

Be Framework represents a unique convergence of ALPS (Application-Level Profile Semantics) philosophy and executable code. This document explores the bidirectional relationship between ALPS specifications and Be Framework implementations, demonstrating how each can generate the other while maintaining semantic integrity.

## The Dual Nature of Be Framework

Be Framework exists simultaneously as:
- **Executable implementation** with concrete business logic
- **Living ALPS specification** with semantic state transitions
- **Protocol-agnostic design** that can manifest as REST, GraphQL, or message queues

This duality enables a bidirectional generation process that maintains perfect consistency between specification and implementation.

## ALPS to Be Framework Generation

### Source ALPS Specification
```json
{
  "alps": {
    "title": "Order Processing Workflow",
    "doc": { "value": "E-commerce order processing state transitions" },
    "descriptor": [
      {
        "id": "OrderRequest",
        "type": "semantic",
        "doc": { "value": "Initial order request from customer" },
        "rt": ["ValidOrder", "InvalidOrder"]
      },
      {
        "id": "ValidOrder",
        "type": "semantic", 
        "doc": { "value": "Order that has passed validation" },
        "rt": ["ProcessedOrder", "CancelledOrder"]
      },
      {
        "id": "ProcessedOrder",
        "type": "semantic",
        "doc": { "value": "Order that has been successfully processed" },
        "rt": ["ShippedOrder", "RefundedOrder"]
      }
    ]
  }
}
```

### Generated Be Framework Implementation
```bash
ray-generate --from-alps order-workflow.alps.json --output src/OrderWorkflow/
# Generates complete implementation skeleton in src/OrderWorkflow/
```

```php
// Generated from ALPS specification
namespace OrderWorkflow;

use Be\Framework\Attribute\Be;
use Be\Framework\Attribute\Input;

#[Be([ValidOrder::class, InvalidOrder::class])]
final class OrderRequest
{
    public readonly ValidOrder|InvalidOrder $being;
    public function __construct(
        #[Input] array $orderData,
        OrderValidator $validator,
        InventoryChecker $inventory
    ) {
        // Generated structure based on ALPS transitions
        // Business logic implementation required
        $this->being = $validator->isValid($orderData)
            ? new Valid($orderData, $inventory)
            : new Invalid($validator->getErrors());
    }
}

#[Be([ProcessedOrder::class, CancelledOrder::class])]
final class ValidOrder
{
    public readonly Processed|Cancelled $being;
    
    public function __construct(
        Valid $validOrderData,
        PaymentProcessor $paymentProcessor,
        OrderRepository $orderRepository
    ) {
        // Implementation follows ALPS state transition rules
        // Specific business logic to be implemented
    }
}

#[Be([ShippedOrder::class, RefundedOrder::class])]
final class ProcessedOrder
{
    public readonly Shipped|Refunded $being;
    
    public function __construct(
        Processed $processedOrder,
        ShippingService $shippingService,
        RefundProcessor $refundProcessor
    ) {
        // Final state transitions as defined in ALPS
    }
}
```

### Key Generation Features

**Structural Generation:**
- Class hierarchy mirrors ALPS descriptor relationships
- `#[Be]` attributes reflect `rt` (relation type) values
- Union types correspond to possible state transitions

**Semantic Preservation:**
- ALPS documentation becomes code comments
- Descriptor IDs become class names
- State transition logic is scaffolded but requires implementation

**Business Logic Scaffold:**
- Constructor parameters suggest required dependencies
- State determination logic structure is provided
- Implementation details remain for developer specification

## Be Framework to ALPS Generation

### Source Be Framework Implementation
```php
namespace LoanProcessing;

#[Be([ApprovedLoan::class, RejectedLoan::class, PendingReview::class])]
final class LoanApplication
{
    public readonly ApprovedLoan|RejectedLoan|PendingReview $being;
    
    public function __construct(
        #[Input] string $applicantId,
        #[Input] float $requestedAmount,
        #[Input] int $creditScore,
        LoanProcessor $processor,
        CreditAnalyzer $analyzer
    ) {
        $analysis = $analyzer->analyze($applicantId, $creditScore);
        
        $this->being = match (true) {
            $analysis->isHighRisk() => new Rejected($analysis->getReason()),
            $analysis->requiresReview() => new PendingReview($analysis->getReviewNotes()),
            default => new Approved($requestedAmount, $analysis->getTerms())
        };
    }
}

#[Be([FinalizedLoan::class, LoanCancellation::class])]
final class ApprovedLoan
{
    public readonly Finalized|Cancelled $being;
    
    public function __construct(
        Approved $approvedLoan,
        DocumentService $documentService,
        CustomerNotificationService $notificationService
    ) {
        // Implementation logic for loan finalization
    }
}
```

### Generated ALPS Specification
```bash
$ ray-extract --to-alps src/LoanProcessing/ --output loan-processing.alps.json
```

```json
{
  "alps": {
    "title": "Loan Processing API",
    "doc": { "value": "Generated from Be Framework implementation" },
    "version": "1.0",
    "descriptor": [
      {
        "id": "LoanApplication",
        "type": "semantic",
        "doc": { "value": "Loan application processing workflow" },
        "descriptor": [
          {
            "id": "applicantId",
            "type": "semantic",
            "doc": { "value": "Unique identifier for loan applicant" }
          },
          {
            "id": "requestedAmount", 
            "type": "semantic",
            "doc": { "value": "Loan amount requested by applicant" }
          },
          {
            "id": "creditScore",
            "type": "semantic", 
            "doc": { "value": "Applicant's credit score" }
          }
        ],
        "rt": ["ApprovedLoan", "RejectedLoan", "PendingReview"]
      },
      {
        "id": "ApprovedLoan",
        "type": "semantic",
        "doc": { "value": "Loan that has been approved for processing" },
        "rt": ["FinalizedLoan", "LoanCancellation"]
      },
      {
        "id": "RejectedLoan",
        "type": "semantic",
        "doc": { "value": "Loan application that was rejected" },
        "rt": []
      },
      {
        "id": "PendingReview",
        "type": "semantic", 
        "doc": { "value": "Loan requiring manual review" },
        "rt": ["ApprovedLoan", "RejectedLoan"]
      }
    ]
  }
}
```

### Extraction Process

**State Transition Analysis:**
- `#[Be]` attributes become `rt` arrays
- Union types in `$being` properties define possible transitions
- Class relationships form ALPS descriptor hierarchy

**Semantic Discovery:**
- Constructor parameters with `#[Input]` become ALPS descriptors
- Variable names link to semantic definitions (Semantic Variable Names)
- Class and property documentation becomes ALPS documentation

**Validation Integration:**
- `validates/` folder contents inform ALPS constraints
- Business rules become semantic documentation
- Type information enhances ALPS type definitions

## Protocol Generation from ALPS

### RESTful API Generation
```bash
$ ray-extract --to-openapi src/LoanProcessing/ --output loan-api.openapi.json
```

```yaml
openapi: 3.0.0
info:
  title: Loan Processing API
  version: 1.0.0
paths:
  /loan-applications:
    post:
      summary: Submit loan application
      requestBody:
        content:
          application/json:
            schema:
              type: object
              properties:
                applicantId:
                  type: string
                requestedAmount:
                  type: number
                creditScore:
                  type: integer
      responses:
        '200':
          description: Application processed
          content:
            application/json:
              schema:
                oneOf:
                  - $ref: '#/components/schemas/ApprovedLoan'
                  - $ref: '#/components/schemas/RejectedLoan'
                  - $ref: '#/components/schemas/PendingReview'
```

### GraphQL Schema Generation
```bash
$ ray-extract --to-graphql src/LoanProcessing/ --output loan-schema.graphql
```

```graphql
type LoanApplication {
  applicantId: String!
  requestedAmount: Float!
  creditScore: Int!
  being: LoanApplicationResult!
}

union LoanApplicationResult = ApprovedLoan | RejectedLoan | PendingReview

type ApprovedLoan {
  amount: Float!
  terms: LoanTerms!
  being: ApprovedLoanResult!
}

union ApprovedLoanResult = FinalizedLoan | LoanCancellation
```

## Specification-Driven Development

### Design-First Workflow
1. **Business Analysis:** Define workflows in ALPS
2. **Code Generation:** Generate Be Framework skeleton
3. **Implementation:** Add business logic to generated structure
4. **Validation:** Verify implementation matches ALPS specification
5. **API Generation:** Automatically generate protocol-specific APIs

### Implementation-First Workflow  
1. **Rapid Development:** Implement in Be Framework directly
2. **Specification Extraction:** Generate ALPS from implementation
3. **Documentation:** Use ALPS as living API documentation
4. **Client Generation:** Generate client SDKs from ALPS
5. **Contract Testing:** Validate implementation against extracted specification

## Tool Integration

### CLI Commands
```bash
# Bidirectional generation
ray-generate --from-alps workflow.alps.json --output src/
ray-extract --to-alps src/ --output generated.alps.json

# Protocol generation
ray-extract --to-openapi src/ --output api.openapi.json
ray-extract --to-graphql src/ --output schema.graphql
ray-extract --to-grpc src/ --output service.proto

# Validation and testing
ray-validate --alps-compliance src/ workflow.alps.json
ray-test --contract-testing src/ api-tests/
```

### IDE Integration
```typescript
// VS Code extension
ray.generateFromALPS({
  source: './design.alps.json',
  target: './src/generated/',
  namespace: 'App\\Workflow'
});

ray.extractToALPS({
  source: './src/workflow/',
  target: './docs/specification.alps.json',
  includeDocumentation: true
});
```

## Quality Assurance

### Specification Compliance
```bash
$ ray-validate --alps-compliance src/OrderWorkflow/ order-workflow.alps.json
✓ All state transitions match ALPS specification
✓ All semantic descriptors are implemented
✓ No orphaned states detected
✓ Business logic preserves semantic contracts
```

### Round-trip Validation
```bash
# Test bidirectional generation integrity
$ ray-extract --to-alps src/ --output extracted.alps.json
$ ray-generate --from-alps extracted.alps.json --output src-regenerated/
$ diff -r src/ src-regenerated/
# Should show only business logic differences, not structural changes
```

## Benefits of Bidirectional Generation

### For Architects
- **Design Validation:** ALPS specifications can be implemented and tested
- **Implementation Verification:** Code automatically generates specification
- **Protocol Agnostic:** Same design works for REST, GraphQL, events

### For Developers  
- **Guided Implementation:** ALPS provides clear structure for development
- **Living Documentation:** Code changes automatically update specifications
- **Contract Certainty:** Implementation and specification cannot diverge

### For Product Teams
- **Business Alignment:** ALPS captures business workflows precisely
- **Change Impact:** Modifications show immediate specification effects
- **Client Coordination:** Generated APIs provide clear integration contracts

## Conclusion

The bidirectional relationship between ALPS and Be Framework represents a fundamental advancement in specification-driven development. By treating implementations as living specifications and specifications as implementable designs, we achieve perfect alignment between business intent and technical reality.

This approach eliminates the traditional gap between design and implementation, creating a unified system where:
- Specifications are always current
- Implementations are always compliant
- APIs are always consistent
- Documentation is always accurate

The result is not just better software development—it's a new way of thinking about the relationship between design and implementation, where both inform and enhance each other in a continuous cycle of refinement and clarity.

---

*Next: Explore practical examples of ALPS-Ray bidirectional generation in real-world applications.*