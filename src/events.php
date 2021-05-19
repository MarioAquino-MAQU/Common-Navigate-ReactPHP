<?php
namespace src;

use React\EventLoop\Factory;

require __DIR__.'/../vendor/autoload.php';

/**
 * Execute something after X seconds
 * Similar to setTimeout() in javascript
 */
function staticTimer()
{
    $loop = Factory::create();

    echo "Program started\n";

    // One-off timers
    $loop->addTimer(2, function () {
        echo "This is ran after 2 seconds\n";
    });

    $loop->addTimer(3, function() {
        echo "This is ran after 3 seconds\n";
    });

    $loop->run();
}

/**
 * Execute something every X seconds
 * Similar to setInterval() in javascript
 */
function periodicTimer()
{
    $loop = Factory::create();
    $seconds = 0;

    // Periodic timers
    $loop->addPeriodicTimer(1, function () use (&$seconds) {
        $seconds++;
        echo "$seconds second passed\n";
    });

    $loop->run();

}

//staticTimer();
periodicTimer();
