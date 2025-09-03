# tmp/

This directory contains temporary debug and test files used during development.

## Files

- `debug_logger_open.php` - Debug script for testing Logger->open method with xdebug-debug
- `hello.php` - Simple test script for xdebug-debug functionality

## Usage

These files are used with xdebug-debug for forward trace debugging:

```bash
# Example: Debug Logger->open method
./vendor/bin/xdebug-debug --context="Testing Logger open method" --break="/Users/akihito/git/private-be/v0/src/SemanticLog/Logger.php:32" --exit-on-break --steps=20 --json -- php tmp/debug_logger_open.php

# Example: Simple test
./vendor/bin/xdebug-debug --exit-on-break tmp/hello.php
```

## Note

Files in this directory can be deleted when no longer needed for debugging.