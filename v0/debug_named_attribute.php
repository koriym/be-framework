<?php

require 'vendor/autoload.php';
require 'tests/BecomingTest.php';

use Ray\Di\Di\Named;

// テストクラスのリフレクションを取得
$reflection = new ReflectionClass('Be\Framework\BecomingTestWithUnion');
$constructor = $reflection->getConstructor();
$param = $constructor->getParameters()[1]; // unionParam

echo "Parameter name: " . $param->getName() . "\n";

// Named属性を取得
$namedAttributes = $param->getAttributes(Named::class);
echo "Named attributes count: " . count($namedAttributes) . "\n";

if (!empty($namedAttributes)) {
    $namedInstance = $namedAttributes[0]->newInstance();
    echo "Named instance: " . get_class($namedInstance) . "\n";
    echo "Named value: " . $namedInstance->value . "\n";
} else {
    echo "No Named attributes found!\n";
}

// 全属性を表示
echo "\nAll attributes:\n";
foreach ($param->getAttributes() as $attr) {
    echo "- " . $attr->getName() . "\n";
    $instance = $attr->newInstance();
    echo "  Instance: " . get_class($instance) . "\n";
    if (property_exists($instance, 'value')) {
        echo "  Value: " . $instance->value . "\n";
    }
}