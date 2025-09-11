# 10. Semantic Logging

> "Every transformation tells a story. Semantic logging captures that narrative."

Semantic logging in Be Framework automatically captures the complete metamorphosis journey, providing deep observability into object transformations.

## What is Semantic Logging?

Traditional logging captures events. **Semantic logging captures meaning** - the ontological journey of objects through their transformations.

Be Framework automatically logs every metamorphosis without any code changes required.

## Automatic Log Structure

### Open Context (Transformation Begins)
```json
{
  "open": {
    "context": {
      "fromClass": "UserInput",
      "beAttribute": "#[Be(RegisteredUser::class)]",
      "immanentSources": {
        "email": "user@example.com"
      },
      "transcendentSources": {
        "UserRepository": "App\\Repository\\UserRepository"
      }
    }
  }
}
```

### Close Context (Transformation Completes)
```json
{
  "close": {
    "context": {
      "be": "FinalDestination",
      "properties": {
        "userId": "user_123",
        "email": "user@example.com"
      }
    }
  }
}
```

## Configuration and Usage

### Enabling Semantic Logging
Be Framework automatically uses [Koriym.SemanticLogger](https://github.com/koriym/Koriym.SemanticLogger) for structured semantic logging.

**TBD** - Configuration details for enabling/disabling log output

### Schema-Validated Logs
Semantic logs follow JSON schema for type safety and AI analysis:

- **Type-safe structured logging** with validation
- **AI-native analysis** capabilities  
- **Hierarchical workflow context** (intent → events → result)

### Custom Log Context
**TBD** - How to add custom context to metamorphosis logs

### Log Processing
**TBD** - Integration with monitoring tools and log aggregation

## Log Analysis Examples

```bash
# Find failed transformations
jq '.close.context.be == "DestinationNotFound"' logs/semantic.log

# Follow specific user journeys
jq '.open.context.immanentSources.email == "user@example.com"' logs/semantic.log
```

## The Power of Ontological Observability

Semantic logging transforms debugging from **"what happened?"** to **"what became?"**

Instead of tracking method calls, you track the natural evolution of objects through their intended forms - providing unprecedented insight into your application's true behavior.

---

*"In traditional logging, we track events. In semantic logging, we witness becoming."*
