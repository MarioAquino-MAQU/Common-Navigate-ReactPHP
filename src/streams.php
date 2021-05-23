<?php
namespace src;

use React\EventLoop\Factory;
use React\Stream\ReadableResourceStream;
use React\Stream\ThroughStream;
use React\Stream\WritableResourceStream;

require __DIR__.'/../vendor/autoload.php';

function inOutStream()
{
    $loop = Factory::create();
    $in = new ReadableResourceStream(fopen('../resources/files/animals.txt','r'), $loop);
    $out = new WritableResourceStream(STDOUT, $loop);

    $in->on('data', function($data) use ($out) {
        $out->write(strtoupper($data.PHP_EOL));
    });

    $in->on('end', function() use ($out) {
        $out->write("File operation ended\n");
    });

    $loop->run();
}
//inOutStream();

function throughStream()
{
    $loop = Factory::create();
    $in = new ReadableResourceStream(STDIN, $loop);
    $out = new WritableResourceStream(STDOUT, $loop);
    $through = new ThroughStream(function($data) {
        return strtoupper($data);
    });

    $in->pipe($through)
        ->pipe($out);

    $loop->run();
}

//throughStream();