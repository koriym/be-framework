<img src="https://ray-di.github.io/images/logo.svg" width=160  alt="logo">

# Ray.Compiler

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ray-di/Ray.Compiler/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Compiler/?branch=1.x)
[![codecov](https://codecov.io/gh/ray-di/Ray.Compiler/branch/1.x/graph/badge.svg?token=KCQXtu01zc)](https://codecov.io/gh/ray-di/Ray.Compiler)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Compiler/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Compiler)
[![Continuous Integration](https://github.com/ray-di/Ray.Compiler/actions/workflows/continuous-integration.yml/badge.svg?branch=1.x)](https://github.com/ray-di/Ray.Compiler/actions/workflows/continuous-integration.yml)

Pre-compile Ray.Di bindings to PHP code for maximum performance. The compiled injector runs faster than the standard injector by avoiding runtime reflection and binding resolution.

## Installation

```bash
composer require ray/compiler
```

## Usage

Ray.Compiler provides two main components:

1. **`Compiler`**: Compiles Ray.Di bindings into PHP code.
2. **`CompiledInjector`**: High-performance injector that executes pre-compiled code.

### Basic Usage

Pre-compile your dependencies:

```php
use Ray\Compiler\Compiler;

$compiler = new Compiler();
// Compile Ray.Di bindings to PHP files
$compiler->compile(
    $module,    // AbstractModule: Your application's module
    $scriptDir  // string: Directory path where compiled PHP files will be generated
);
```

Use the compiled injector:

```php
use Ray\Compiler\CompiledInjector;

$injector = new CompiledInjector($scriptDir);
$instance = $injector->getInstance(YourInterface::class);
```

### Compiler Integration

Create a compile script:

```php
try {
    $scripts = (new Compiler())->compile(
        new AppModule(),
        __DIR__ . '/di'
    );
    printf("Compiled %d files.\n", count($scripts));
} catch (CompileException $e) {
    fprintf(STDERR, "Compilation failed: %s\n", $e->getMessage());
    exit(1);
}
```

Add compile script to your `composer.json`:

```json
{
    "scripts": {
        "post-install-cmd": ["php bin/compile.php"]
    }
}
```

## Docker Integration

Use multi-stage builds to maintain path consistency:

```dockerfile
# Build stage
FROM php:8.2-cli-alpine as builder

# Set working directory
WORKDIR /app

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

# Copy application code
COPY . .

# Create non-root user
RUN adduser -D appuser
USER appuser

# Compile DI code
RUN php bin/compile.php

# Production stage
FROM php:8.2-cli-alpine

# Create non-root user
RUN adduser -D appuser

# Set working directory
WORKDIR /app

# Copy only necessary files from builder
COPY --from=builder /app/vendor/ ./vendor/
COPY . .
COPY --from=builder /app/tmp/di/ ./tmp/di/

# Switch to non-root user
USER appuser
# Start command or other configurations can be added here
```

## Docker Best Practices

When building your Docker images, it’s important to exclude unnecessary files to speed up builds, reduce image size, and prevent sensitive files from being included in the image. Below is a recommended `.dockerignore` file. Adjust it to fit your project’s requirements:

```dockerignore
# Ignore Git files
.git/

# Ignore dependency directories
/vendor/
/node_modules/

# Ignore compiled DI files
/tmp/di/

# Ignore environment-specific files
.env
.env.local
.env.*.local

# Ignore documentation and tests
/docs/
/tests/

# Ignore IDE-specific files
.idea/
.vscode/

# Ignore log files
*.log

# Ignore OS-specific files
.DS_Store
Thumbs.db
```

## Version Control

Compiled DI code is considered an environment-specific build artifact and **should not** be committed to version control. This approach ensures that your repository remains clean and build artifacts do not cause merge conflicts or unexpected behavior across different environments.

Add the compile directory to your `.gitignore`:

```gitignore
/tmp/di/
```
