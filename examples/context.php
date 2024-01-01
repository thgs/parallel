#!/usr/bin/env php
<?php
require dirname(__DIR__).'/vendor/autoload.php';

use Revolt\EventLoop;
use function Amp\delay;
use function Amp\Parallel\Context\contextFactory;

$timer = EventLoop::repeat(0.25, function () {
    static $i;
    $i = $i ? ++$i : 1;
    $nth = $i . ([1 => 'st', 2 => 'nd', 3 => 'rd'][$i] ?? 'th');
    print "Demonstrating how alive the parent is for the {$nth} time.\n";
});
delay(2);

class Kernel
{
    private $context;

    public function __construct()
    {
        // Create a new child process or thread that does some blocking stuff.
        $this->context = contextFactory()->start(__DIR__ . "/contexts/blocking.php");
    }

    public function moduleCall($interface, $method, ...$arguments)
    {
        try {
            // call
            $this->context->send(['interface', 'method', $arguments[0]]); // Data sent to child process, received on line 9 of contexts/blocking.php

            $result = $this->context->receive();
//            printf("Result: %s\n", $result);
        } finally {
//            EventLoop::cancel($timer);
        }
    }

    public function close()
    {
        $this->context->send('__exit');
        printf("Process ended with value %d!\n", $this->context->join());
    }
}

$kernel = new Kernel();
$i = 0;
while ($i <= 10) {
    $kernel->moduleCall('int', 'sayHello', 'Name' . $i);
    $i++;
}
print "closing!";
$kernel->close();