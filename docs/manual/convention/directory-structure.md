# Be Framework Directory Structure

> "Organization follows ontology. Code structure reflects the nature of being."

## Be Framework Directory Structure

```
src/
├── Input/               # 🌟 What this application CAN DO
├── Being/               # How transformations happen
├── Final/               # 🌟 What this application PRODUCES
├── Reason/              # Capabilities and services
├── Semantic/            # 🌟 What information this application HANDLES
├── Tag/                # Contextual markers
├── Module/              # Ray.DI modules and bindings
├── Exception/           # Domain exceptions
└── App/                 # Technical services
```

## The Power of Visibility

**Input/ folder = Complete application capabilities**
- See every possible action in one place
- Understand what the application can do at a glance
- Perfect onboarding for new developers

**Semantic/ folder = Complete information model with constraints**
- See every concept the application handles
- Understand the domain vocabulary immediately
- **ID + Meaning + Validation + Error handling in one place**
- Semantic exceptions tell users exactly what went wrong
- No hidden data structures or scattered validation rules

## Simple Rule: Global First, Domain When Complex

**Start with everything global**:
- Input/, Final/, Semantic/, Tag/, Module/ - Always global
- Being/, Reason/ - Global until complex (10+ classes)

**When Being/ or Reason/ gets complex, organize by domain**:
```
src/
├── Input/               # Still global
├── Being/               # Now domain-organized
│   ├── User/
│   └── Order/
├── Final/               # Still global
├── Reason/              # Now domain-organized
│   ├── User/
│   └── Order/
├── Semantic/            # Still global
├── Tag/                 # Still global
└── Module/              # Still global
```

## Input Class Naming

**Pattern**: `{Purpose}{Action}Input.php` → `{Result}Final.php`

```php
UserRegistrationInput.php → RegisteredUser.php
UserDeletionInput.php → DeletedUser.php
OrderCreationInput.php → CreatedOrder.php
```

## Integration with External Frameworks

```php
// External Framework Controller (Laravel)
class UserController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $input = new UserRegistrationInput($request->validated());
        $result = $this->becoming($input);
        return $this->toJsonResponse($result);
    }
}

// Be Framework Domain uses App/ services
class BeingUser
{
    public function __construct(
        #[Input] string $email,
        #[Inject] UserRepository $repository    // From App/Repository/
    ) {
        // Domain logic using App/ services
    }
}
```

## Key Principles

1. **Start Global**: Begin with everything global and simple
2. **Split When Complex**: Only organize Being/ and Reason/ by domain when needed
3. **Keep Discovery Global**: Always maintain Input/, Final/, Semantic/, Tag/, Module/ visibility
4. **Simple Rule**: Don't over-organize early - complexity should drive structure

---

*"The way we organize code shapes the way we think about the domain. Let your directories tell the story of becoming."*
