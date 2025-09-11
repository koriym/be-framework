---
layout: page
title: "1. Overview: A Different Way to Think About Code"
---

# Overview: A Different Way to Think About Code

## First, Look at This

```php
// Traditional way to delete a user
$user = User::find($id);
$user->delete();

// A different way to delete a user
$activeUser = User::find($id);
$deletedUser = new DeletedUser($activeUser);
```

**DeletedUser?**

If that feels strange, you're not alone. Why would deletion create something new?

This discomfort reveals something important—an assumption about programming so deep we rarely question it.

## The Difference

Traditional programming focuses on **actions**:
```php
$user->validate();
$user->save();
$user->notify();
```

Be Framework focuses on **existence**:
```php
$rawData = new UserInput($_POST);
$validatedUser = new ValidatedUser($rawData);
$savedUser = new SavedUser($validatedUser);
```

One tells objects what to DO.
The other defines what can BE.

## Why This Matters

When you focus on DOING:
- You constantly check if actions are allowed
- You handle endless error cases
- You fight against invalid states

When you focus on BEING:
- Invalid states cannot exist
- Objects carry their own validity
- Existence itself is the proof

The difference is in the types themselves:
```php
// Traditional: generic types
function processUser(User $user) { }

// Be Framework: specific states of being
function processUser(ValidatedUser $user) { }
function saveUser(SavedUser $user) { }
function archiveUser(DeletedUser $user) { }
```

Each type represents a specific stage of existence, not just data.

## What You'll Learn

This manual will show you how to:

1. **Define existence** instead of commanding actions
2. **Make invalid states impossible** instead of checking for them
3. **Let objects transform naturally** instead of forcing changes
4. **Trust existence** instead of defending against errors

## Ready?

Let's start with the foundation: [Input Classes →](./01-input-classes.md)

You'll build your first Being—and discover why `DeletedUser` makes perfect sense.
