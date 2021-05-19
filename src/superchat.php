<?php
namespace src;

use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use React\Socket\LimitingServer;
use React\Socket\Server;
require __DIR__.'/../vendor/autoload.php';

$users = [];

function init()
{
    $loop = Factory::create();
    $server = new Server("0.0.0.0:6000", $loop);
    $limitingserver = new LimitingServer($server, null);

    $limitingserver->on('connection', function(ConnectionInterface $connection) use ($limitingserver) {
        echo "Connection received\n";
        $connection->write("\033[90mWelcome!\nEnter your name and confirm with enter:\033[0m ");

        $connection->on('data', function($data) use ($connection, $limitingserver){
            if(\src\nameCheck($connection->getRemoteAddress(), $data)) {
                foreach ($limitingserver->getConnections() as $client) {
                    if($client->getRemoteAddress() !== $connection->getRemoteAddress()) {
                        $client->write("\033[94m{$GLOBALS['users'][$connection->getRemoteAddress()]}: ".$data."\033[0m");
                    } else {
                        $client->write("\033[90mMessage send!\033[0m\n");
                    }
                    //$clients->write($data);
                    //var_dump($clients->getLocalAddress(), $clients->getRemoteAddress());
                }
            } else {
                $connection->write("\033[90mUsername confirmed!\033[0m\n");
            }
        });

        $connection->on('close', function() use($connection, $limitingserver) {
            foreach ($limitingserver->getConnections() as $client) {
                if($client->getRemoteAddress() !== $connection->getRemoteAddress()) {
                    $client->write("\033[94m{$GLOBALS['users'][$connection->getRemoteAddress()]}\033[90m left the chat\033[0m\n");
                } else {
                    $client->write("\033[90mYou left the chat.\033[0m\n");
                    unset($GLOBALS['users'][$client->getRemoteAddress()]);
                }
            }
        });
    });
    echo "Listening on {$server->getAddress()}\n";
    $loop->run();
}

function nameCheck($identifier, $data) {
    if(!isset($GLOBALS['users'][$identifier])) {
        $data = preg_replace("/[\n\r]/","",$data); ;
        $GLOBALS['users'][$identifier] = $data;
        return false;
    }
    return true;
}

init();
