<?php

namespace FooName;

require dirname(__DIR__) . '/vendor/autoload.php';
$nullLoad = require dirname(__DIR__) . '/autoload.php';
spl_autoload_register($nullLoad);

require __DIR__ . '/FooInterface.php';

$null = new FooInterfaceNull();

echo $null instanceof FooInterface ? 'It works!' : 'It does not work.';
