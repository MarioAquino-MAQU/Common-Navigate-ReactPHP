<?php

namespace src;

use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use React\Socket\LimitingServer;
use React\Socket\Server;

require __DIR__.'/../vendor/autoload.php';

function socketServer()
{
    $loop = Factory::create();

    $server = new Server("0.0.0.0:6000", $loop);

    $server->on('connection', function(ConnectionInterface $connection) {
        echo "Connection received\n";
        $connection->write("Welcome!\n");

        $connection->on('data', function($data) use ($connection){
            echo "Connection wrote: $data\n";
            $connection->write("Message delivered!\n");
        });
    });

    echo "Listening on {$server->getAddress()}\n";

    $loop->run();
}

function limitingServer()
{
    $loop = Factory::create();
    $server = new Server("0.0.0.0:6000", $loop);
    $limitingserver = new LimitingServer($server, null);

    $limitingserver->on('connection', function(ConnectionInterface $connection) use ($limitingserver) {
        echo "Connection received\n";
        $connection->write("Welcome!\n");

        $connection->on('data', function($data) use ($connection, $limitingserver){
            foreach ($limitingserver->getConnections() as $clients) {
                $clients->write($data);
            }
            $connection->write("Message delivered!\n");
        });
    });
    echo "Listening on {$server->getAddress()}\n";
    $loop->run();
}

//socketServer();
//limitingServer();