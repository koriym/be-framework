<?php

use Ray\Compiler\Compiler;
use Ray\Compiler\FakeCarModule;

require dirname(__DIR__) . '/vendor/autoload.php';

$scripts = (new Compiler())->compile(
    new FakeCarModule(),
    __DIR__ . '/di'
);

printf('Compiled %d files.', count($scripts));
