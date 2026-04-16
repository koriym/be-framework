# LDD Reference Example — Register User

This directory holds the reference artifact for Log-Driven Development (LDD) in
Be Framework: a hand-authored `$been` semantic log that **specifies** a
metamorphosis. The JSON is not a trace captured from a run — it is a spec
written first, from which the PHP classes are generated.

## What this example says

One metamorphosis step:

```text
UnverifiedEmail  --#[Be(RegisteredUser::class)]-->  RegisteredUser
```

Along the way, two pieces of evidence are recorded as semantic events:

1. `email_format_asserted` — the email format was validated.
2. `user_inserted` — a row was created in the user store and received id `42`.

The final object `RegisteredUser` carries these events on `public readonly Been
$been` — proof of what made this object what it is.

## Files

- [`been.json`](been.json) — the authored specification, in SemanticLogger format.
- Generated classes live under [`tests/FakeApp/Ldd/RegisterUser/`](../../../FakeApp/Ldd/RegisterUser/):
  - `UnverifiedEmail.php` — source class with `#[Be(RegisteredUser::class)]`
  - `RegisteredUser.php` — destination class whose constructor does the work
  - `EmailVerifier.php`, `UserRepository.php` — fake services used via `#[Inject]`
  - `Context/EmailFormatAssertedContext.php`, `Context/UserInsertedContext.php` — the two event contexts
- [`tests/Integration/LddLoopTest.php`](../../LddLoopTest.php) — runs the generated code
  and asserts the produced log is semantically equivalent to `been.json`.

## The LDD loop this example closes

1. A human (or an AI) authors `been.json` describing what ought to be.
2. A skill (`.claude/skills/ldd-from-log.md`) reads the JSON and writes PHP.
3. The PHP runs through `Becoming` and emits its own semantic log.
4. The emitted log matches `been.json` (structurally; ids and timestamps ignored).

When the loop closes, `been.json` is simultaneously the specification, the
example, and the test. There is nothing for an external unit test to verify
that the `$been` does not already carry.
