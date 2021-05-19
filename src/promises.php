<?php
namespace src;


use Carbon\Carbon;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Factory;
use React\Http\Browser;
use React\Http\Client\Client;
use React\Promise\Deferred;
use React\Stream\WritableResourceStream;
use function React\Promise\all;

require __DIR__.'/../vendor/autoload.php';
$dotenv = Dotenv::createImmutable('..');
$dotenv->load();

$loop = Factory::create();
$client = new Browser($loop);
$timer = 0;

function getCountryPopulation($code)
{
    $deferred = new Deferred();
    $GLOBALS['client']->get("https://world-population.p.rapidapi.com/population?country_name=$code", [
        "x-rapidapi-host" => "world-population.p.rapidapi.com",
        "x-rapidapi-key" => $_ENV['api_key']
    ])->then(function(ResponseInterface $response) use ($deferred) {
        $res = json_decode((string)$response->getBody());
        $body = $res->body->population;
        $deferred->resolve($body);
    }, function($e) use ($deferred) {
        $error = $e->getResponse();
        $deferred->reject($error->getReasonPhrase());
    });
    return $deferred->promise();
}

function getCovidInfo($code)
{
    $deferred = new Deferred();
    $GLOBALS['client']->get("https://covid-19-data.p.rapidapi.com/country?name=Belgium", [
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

function printOutput($country, $covid, $population)
{
    $out = "COVID Numbers for: \033[31m$country\033[0m \n\n";
    $perc = round(($covid->confirmed/$population) * 100);
    $out .= "\033[92mConfimed infections:\033[0m ".$covid->confirmed." / ".$population." (".$perc."%)\n";
    $perc = round(($covid->recovered/$covid->confirmed) * 100);
    $out .= "\033[94mRecovery rate:\033[0m ".$covid->recovered." / ".$covid->confirmed." (".$perc."%)\n";
    $perc = round(($covid->deaths/$covid->confirmed) * 100);
    $out .= "\033[90mDeath rate:\033[0m ".$covid->deaths. " / ".$covid->confirmed." (".$perc."%)\n";
    $perc = round(($covid->critical/$covid->confirmed) * 100);
    $out .= "\033[31mIn critical state:\033[0m ".$covid->critical. " / ".$covid->confirmed." (".$perc."%)\n";
    echo $out;
}

function registerTimer($loop)
{
    $loop->addPeriodicTimer(1, function () use (&$seconds) {
        $GLOBALS['timer']++;
        $seconds = $GLOBALS['timer'];
        echo "$seconds second passed\n";
    });
}

//$countryPromise = all([getCountryInfo("Belgium")]);
registerTimer($loop);
$countries = ["Belgium", "Italy", "France"];
foreach ($countries as $country)
{
    echo "Loop for $country\n";
    $promises = all(["covid" => getCovidInfo($country), "population" => getCountryPopulation($country), "country" => $country]);
    $promises->then(function($values) {
        printOutput($values['country'], $values['covid'], $values['population']);
    }, function($error) {
        echo $error.PHP_EOL;
    });
/*    getCovidInfo($country)
        ->then(function ($covid) use ($country){
            getCountryPopulation($country)
            ->then(function($population) use ($country, $covid) {
                printOutput($country, $covid, $population);
            });
        });*/
}


$loop->run();