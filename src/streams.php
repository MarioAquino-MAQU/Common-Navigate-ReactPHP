<?php
namespace src;

use React\EventLoop\Factory;
use React\Stream\ReadableResourceStream;
use React\Stream\WritableResourceStream;

require __DIR__.'/../vendor/autoload.php';

function inOutStream()
{
    $loop = Factory::create();
    $in = new ReadableResourceStream(fopen(__DIR__.'/../resources/animals.txt', 'r'), $loop);
    $out = new WritableResourceStream(STDOUT, $loop);

    $in->on('data', function($data) use ($out) {
        $out->write(strtoupper($data.PHP_EOL));
    });

    $loop->run();
}

inOutStream();