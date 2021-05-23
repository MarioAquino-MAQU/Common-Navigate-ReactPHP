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

function getCovidInfo($code)
{
    $deferred = new Deferred();
    $client = new Browser($GLOBALS['loop']);

    $client->get("https://covid-19-data.p.rapidapi.com/country?name=$code", [
        "x-rapidapi-host" => "covid-19-data.p.rapidapi.com",
        "x-rapidapi-key" => $_ENV['api_key']
    ])->then(function(ResponseInterface $response) use ($deferred) {
        $res = json_decode((string)$response->getBody());
        $body = $res[0];
        $deferred->resolve($body);
    }, function($e) use ($deferred) {
        $error = $e->getResponse();
        $deferred->reject($error->getReasonPhrase());
    });
    return $deferred->promise();
}

getCovidInfo('Belgium')->then(function($data) {
    var_dump($data);
}, function($error) {
    echo $error.PHP_EOL;
});

//echo "Do other stuff".PHP_EOL;

$loop->run();
