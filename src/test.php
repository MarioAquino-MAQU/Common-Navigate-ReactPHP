<?php
namespace src;

use React\EventLoop\Factory;
use React\Stream\ReadableResourceStream;
use React\Stream\WritableResourceStream;

require __DIR__.'/../vendor/autoload.php';

$loop = Factory::create();
$in = new ReadableResourceStream(STDIN, $loop);
$out = new WritableResourceStream(STDOUT, $loop);

$in->on('data', function($data) use ($out) {
    $out->write(strtoupper($data.PHP_EOL));
});

$loop->run();

