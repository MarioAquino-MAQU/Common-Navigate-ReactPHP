<?php

namespace src;


use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Factory;
use React\Http\Browser;
use React\Promise\Deferred;
use React\Stream\ReadableResourceStream;

require __DIR__.'/../vendor/autoload.php';
$dotenv = Dotenv::createImmutable('..');
$dotenv->load();

$loop = Factory::create();

function getCovidInfo($code, $successCallback, $errorCallback)
{
    $client = new Browser($GLOBALS['loop']);

    $client->get("https://covid-19-data.p.rapidapi.com/country?name=$code", [
        "x-rapidapi-host" => "covid-19-data.p.rapidapi.com",
        "x-rapidapi-key" => $_ENV['api_key']
    ])->then(function(ResponseInterface $response) use ($successCallback) {
        $res = json_decode((string)$response->getBody());
        $body = $res[0];
        $successCallback($body);
    }, function($e) use ($errorCallback) {
        $error = $e->getResponse();
        $errorCallback($error->getReasonPhrase());
    });
}

getCovidInfo('Belgium', function($res) {
    var_dump($res);
}, function($error){
    echo $error.PHP_EOL;
});

//echo "Do other stuff".PHP_EOL;

$loop->run();
