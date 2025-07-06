# Semantic Variable Names: When Names Carry Contracts

> "In the beginning was the Word, and the Word was with God, and the Word was God."
>
> In Ontological Programming, names are not labels—they are contracts.

## Abstract

This paper introduces Semantic Variable Names, a natural extension of Ontological Programming where variable names themselves carry validation contracts. By establishing a convention where names like `$email` universally represent valid email addresses, we eliminate redundant validation code while maintaining type safety at the boundaries of our systems. This approach, implemented through a simple folder convention, demonstrates how thoughtful naming can reduce complexity while increasing reliability.

---

## 1. The Observation

In every codebase, certain patterns emerge:

```php
// The eternal repetition
function processUser($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Invalid email');
    }
    // ...
}

function sendNotification($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Invalid email');
    }
    // ...
}
```

A question arises: If `$email` always means email address, why do we validate it everywhere?

---

## 2. The Proposition

What if names could carry their own contracts?

```php
$email      // Always a valid email
$age        // Always non-negative
$price      // Always positive
$url        // Always a valid URL
```

Not through magic, but through convention. Not through complexity, but through simplicity.

But this requires discipline: **one word, one meaning throughout the entire system**. The variable `$email` must mean "valid email address" everywhere—not "contact info" in one place and "username" in another. Similarly, the concept of "email address" must always be represented by `$email`—not sometimes `$emailAddress` or `$mail`.

This is the foundation of semantic consistency.

---

## 3. The Implementation

### 3.1 The Convention

A folder speaks louder than configuration:

```
validates/
├── ValidEmail.php
├── NonNegativeAge.php
├── PositivePrice.php
└── ValidUrl.php
```

### 3.2 The Contract

Each validator follows a simple pattern:

```php
// validates/ValidEmail.php
final class ValidEmail
{
    public function validate(string $email): Errors|true
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return (new Errors())->add('email', ValidationError::INVALID_FORMAT);
        }
        return true;
    }
}
```

The magic: The parameter name in `validate()` determines which variables this validator guards.

### 3.3 The Discovery

The framework reads the intention:

```php
class ValidatorDiscovery
{
    public static function discover(): array
    {
        $validators = [];
        foreach (glob('validates/*.php') as $file) {
            $class = basename($file, '.php');
            $method = new ReflectionMethod($class, 'validate');
            $param = $method->getParameters()[0];
            
            // The parameter name is the rule
            $validators[$param->getName()] = $class;
        }
        return $validators;
    }
}
```

---

## 4. The Harmony with Sacred Rule

In Ontological Programming, the Sacred Rule states: property names flow through metamorphosis. Semantic Variable Names extend this principle:

```php
// First appearance: validated
class UserInput {
    public function __construct(
        #[Input] string $email  // Automatically validated
    ) {}
    
    public readonly string $email;
}

// Subsequent appearances: trusted
class UserNotification {
    public function __construct(
        string $email  // From UserInput::$email, already valid
    ) {}
}
```

**Validation happens once, at the boundary. Trust flows inward.**

---

## 5. Beyond Simple Types

### 5.1 Multi-Parameter Validation

```php
// validates/StrongPassword.php
final class StrongPassword
{
    public function validate(
        string $password,
        string $passwordConfirm
    ): Errors|true {
        $errors = new Errors();
        
        if (strlen($password) < 8) {
            $errors->add('password', ValidationError::TOO_SHORT);
        }
        
        if ($password !== $passwordConfirm) {
            $errors->add('passwordConfirm', ValidationError::MISMATCH);
        }
        
        return $errors->isEmpty() ? true : $errors;
    }
}
```

When both parameters appear together, validation triggers.

### 5.2 Object Validation

```php
// validates/ActiveUser.php
final class ActiveUser
{
    public function validate(User $usr): Errors|true
    {
        if (!$usr->isActive()) {
            return (new Errors())->add('usr', UserError::INACTIVE);
        }
        return true;
    }
}
```

Even dependency-injected objects follow the same principle.

---

## 6. The Philosophy

### 6.1 Names as Promises

In traditional programming, names are arbitrary:

```php
$x = "john@example.com";  // What is x?
$data = -5;               // What kind of data?
```

With Semantic Variable Names, names make promises:

```php
$email = "john@example.com";  // I promise: this is valid
$age = 25;                    // I promise: this is non-negative
```

### 6.2 The Principle of Semantic Consistency

For this system to work, we must adhere to a fundamental law:

> "Same word, same meaning. Same meaning, same word."

This is not a suggestion—it is a requirement. Breaking this principle breaks the entire system:

```php
// NEVER DO THIS
class User {
    public readonly string $email;  // Means email address
}
class Log {
    public readonly string $email;  // Means notification target (could be phone!)
}
```

Every public property in your system should have exactly one meaning, defined in ALPS, used consistently everywhere.

### 6.3 Convention Over Configuration

No configuration files. No annotations. No registration. Just:

1. Name your validator
2. Define what it validates
3. Place it in `validates/`

The system understands.

### 6.3 Trust Boundaries

```
[External World]  →  [Validation Boundary]  →  [Trusted Interior]
     Chaos                  Order                   Peace
```

Once validated, values flow freely. The boundary guards; the interior trusts.

---

## 7. Practical Considerations

### 7.1 Performance

Validation occurs once per unique value origin:

```php
// Validated once
$input = new UserInput($email);  // ✓ Validated here

// Trusted thereafter
$service = new EmailService($input->email);  // No validation
$notifier = new Notifier($service->email);   // No validation
```

### 7.2 Debugging

When validation fails, the error points to the first appearance:

```
CannotExist: Parameter 'email' failed validation at UserInput::__construct
  ValidEmail: Invalid format
```

### 7.3 Extensibility

Adding a new semantic variable:

1. Create `validates/ValidPhoneNumber.php`
2. Implement `validate(string $phoneNumber)`
3. Done

---

## 8. Limitations and Honesty

This approach is not universal truth. It works best when:

- Variable names follow consistent conventions
- Teams agree on naming standards
- Validation rules are relatively stable

It may not suit:

- Legacy codebases with inconsistent naming
- Systems requiring context-dependent validation
- Teams preferring explicit over implicit

---

## 9. ALPS: The Single Source of Semantic Truth

### 9.1 The Problem of Scattered Definitions

Traditional approaches scatter semantic definitions across multiple locations:

```php
/**
 * @param string $email Valid email address
 */
```

```json
{
    "email": {
        "type": "string",
        "description": "Valid email address",
        "format": "email"
    }
}
```

```yaml
email:
  type: string
  description: Valid email address
```

The same meaning, repeated endlessly. This violates DRY at the semantic level.

### 9.2 ALPS as Semantic Framework

Application-Level Profile Semantics (ALPS) provides a solution:

```json
// alps/email.json
{
    "alps": {
        "doc": {"value": "Email semantic definition"},
        "descriptor": [{
            "id": "email",
            "type": "semantic",
            "doc": {"value": "Valid email address conforming to RFC 5322"},
            "href": "https://datatracker.ietf.org/doc/html/rfc5322"
        }]
    }
}
```

One definition. Universal understanding.

### 9.3 The Trinity of Separation

```
alps/
├── email.json      → What it means (semantic)
├── age.json        → What it means (semantic)
└── price.json      → What it means (semantic)

validates/
├── ValidEmail.php  → What makes it valid (logic)
├── NonNegativeAge.php
└── PositivePrice.php

[Your Code]         → What it is (existence)
```

Each layer has a single responsibility:
- **ALPS**: Defines meaning
- **Validates**: Enforces validity
- **Code**: Manifests existence

### 9.4 Automatic Propagation

From ALPS definitions, generate everything:

```php
class SemanticGenerator
{
    public function fromAlps(string $alpsFile): array
    {
        $alps = json_decode(file_get_contents($alpsFile), true);
        
        return [
            'openapi' => $this->toOpenApi($alps),
            'jsonschema' => $this->toJsonSchema($alps),
            'phpdoc' => $this->toPhpDoc($alps),
            'typescript' => $this->toTypeScript($alps),
            'graphql' => $this->toGraphQL($alps)
        ];
    }
}
```

### 9.6 The Law of Semantic Consistency

ALPS enables a crucial principle: **one word, one meaning; one meaning, one word**.

#### The Two Violations

1. **Semantic Overloading** - Same word, different meanings:
```php
// VIOLATION: $status means different things
class Order {
    public readonly string $status;  // "pending", "shipped", "delivered"
}
class User {
    public readonly string $status;  // "active", "suspended", "deleted"
}
```

2. **Semantic Duplication** - Same meaning, different words:
```php
// VIOLATION: All mean the same thing
class ProfileA {
    public readonly string $email;
}
class ProfileB {
    public readonly string $emailAddress;
}
class ProfileC {
    public readonly string $mail;
}
```

#### The ALPS Registry Validator

```php
class SemanticConsistencyValidator
{
    private array $alpsRegistry = [];
    
    public function __construct()
    {
        // Load all ALPS definitions
        foreach (glob('alps/*.json') as $file) {
            $alps = json_decode(file_get_contents($file), true);
-            $this->alpsRegistry[$alps['descriptor'][0]['id']] = $alps;
+            $this->alpsRegistry[$alps['alps']['descriptor'][0]['id']] = $alps;
        }
    }
}
    }
    
    public function validateClass(string $className): void
    {
        $reflection = new ReflectionClass($className);
        
        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();
            
            if (!isset($this->alpsRegistry[$name])) {
                trigger_error(
                    "Property '{$name}' in {$className} has no ALPS definition. " .
                    "Every public property must have semantic meaning.",
                    E_USER_NOTICE
                );
            }
        }
    }
}
```

#### Enforcing Ubiquitous Language

With ALPS registry validation, the system ensures:

1. **Every public property has defined meaning** - No ambiguity
2. **Same name always means same thing** - No confusion
3. **Same concept uses same name** - No duplication

```php
// Build-time validation
foreach (get_declared_classes() as $class) {
    if (/* is domain class */) {
        $validator->validateClass($class);
    }
}
```

This creates a **ubiquitous language** where every term has precise, universal meaning throughout the system.

### 9.7 Real Example: User Profile

Without ALPS (repetition everywhere):

```php
/**
 * @property string $email Valid email address
 * @property int $age User age in years, must be non-negative
 * @property Money $balance Account balance, must be positive
 */
class UserProfile { }
```

```yaml
# OpenAPI
properties:
  email:
    type: string
    format: email
    description: Valid email address
  age:
    type: integer
    minimum: 0
    description: User age in years, must be non-negative
```

With ALPS (single source):

```json
// alps/user-profile.json
{
    "alps": {
        "descriptor": [
            {
                "id": "email",
                "href": "alps/email.json"
            },
            {
                "id": "age", 
                "href": "alps/age.json"
            },
            {
                "id": "balance",
                "href": "alps/money.json",
                "doc": {"value": "Account balance"}
            }
        ]
    }
}
```

The code becomes pure:

```php
class UserProfile {
    public readonly string $email;
    public readonly int $age;
    public readonly Money $balance;
}
```

No comments needed. ALPS carries the meaning.

## 10. Conclusion

Semantic Variable Names represent a quiet revolution. Not through adding complexity, but by recognizing patterns already present. Not through enforcing rules, but by honoring conventions.

When combined with ALPS, we achieve something profound: **meaning, validation, and existence unite in perfect harmony**.

But perhaps most importantly, we establish a **universal language** within our systems. When `$email` appears anywhere—in any class, any context—it means exactly one thing. This is not merely convenient; it is transformative. It eliminates entire categories of bugs born from misunderstanding.

In Ontological Programming, we ask "what can exist?" With Semantic Variable Names, we answer: "only what we name correctly." With ALPS, we add: "and everyone understands why." With semantic consistency validation, we ensure: "and the meaning never drifts."

The Tower of Babel fell because languages diverged. Our systems thrive when language converges.

---

---

## Appendix: Complete Setup

1. Create folder structure:
```
your-project/
├── alps/
│   ├── email.json
│   ├── age.json
│   └── price.json
├── validates/
│   ├── ValidEmail.php
│   ├── NonNegativeAge.php
│   └── PositivePrice.php
└── src/
```

2. Define semantics (ALPS):
```json
// alps/email.json
{
    "alps": {
        "descriptor": [{
            "id": "email",
            "type": "semantic",
            "doc": {"value": "Valid email address conforming to RFC 5322"},
            "href": "https://datatracker.ietf.org/doc/html/rfc5322"
        }]
    }
}
```

3. Define validation:
```php
// validates/ValidEmail.php
final class ValidEmail
{
    public function validate(string $email): Errors|true
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) 
            ? true 
            : (new Errors())->add('email', 'Invalid email format');
    }
}
```

4. Use in your code:
```php
class NewsletterSignup {
    public function __construct(
        #[Input] string $email  // Validated + Semantic meaning from ALPS
    ) {}
}
```

The framework handles everything else:
- Validation on first appearance
- Semantic understanding from ALPS
- Trust through Sacred Rule

---

*"Speak, and the world is. Name, and it exists. But name wisely—for names, once given, carry their truth forever."*
