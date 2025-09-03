<?php

class TestClass {
    public function method1() {
        return 'method1 result';
    }
    
    public function method2() {
        return 'method2 result'; 
    }
    
    public function unused() {
        return 'never called';
    }
}

$obj = new TestClass();
echo "Testing coverage collection:\n";
echo $obj->method1() . "\n";
echo $obj->method2() . "\n";
echo "Script completed\n";