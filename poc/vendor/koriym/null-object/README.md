# PHP NullObject

[![codecov](https://codecov.io/gh/koriym/Koriym.NullObject/branch/1.x/graph/badge.svg?token=CxrX5mQTBF)](https://codecov.io/gh/koriym/Koriym.NullObject)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Aop/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Aop)
![Continuous Integration](https://github.com/koriym/Koriym.NullObject/workflows/Continuous%20Integration/badge.svg)


Generates a NullObject from an interface.
It was created for testing and AOP.


## Installation

    composer require --dev koriym/null-object

## Getting Started

Instantiate a Null Object from an Interface.

```php
interface FooInterface
{
   public function do(): void;
}
```
```php
$nullObject = $this->nullObject->newInstance(FooInterface::class);
assert($nullObject instanceof FooInterface);
$nullObject->do(); // nothing's going to happen

```

`newInstance()` defines a class with `eval`, but use `save()` to save the generated code to a file.

```php
$class = $this->nullObject->save(FooInterface::class, '/path/to/saveDirectory');
assert(new $class instanceof FooInterface);

```
## On the fly

It is also possible to create a null object by simply adding a `Null` suffix to the interface by registering autoloader, If this doesn't sound too wild to you.

```php
$loader = require __DIR__ . '/vendor/koriym/null-object/autoload.php';
spl_autoload_register($loader);
```

or add it to `autoload-dev` in composer.json.

```php
    "autoload-dev": {
        "files": ["./vendor/koriym/null-object/autoload.php"]
    }
```

You can create Null Object as follows.

```php
$nullClass = FooInterface::class . 'Null'; // add Null suffix to the interface
$foo = new $nullClass;  // instantiate a NullObject
assert($foo instanceof FooInterface);
```

## Note

Inherited interfaces are not yet supported.
