<?php

require "vendor/autoload.php";

use InvoiceNinja\Sdk\InvoiceNinja;
use Dotenv\Dotenv;

function findBestMatchIndex($needle,$haystack) {
    $bestscore = 0;
    $bestmatch = null;
    foreach ($haystack as $key => $item) {
        similar_text($needle,$item,$thisscore);
        if ($thisscore>$bestscore) {
            $bestscore = $thisscore;
            $bestmatch = $key;
        }
    }
    return $bestmatch;
}

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function logwrite($text) {
    file_put_contents("./log.txt",$text."\n",FILE_APPEND);
}

$body = file_get_contents('php://input');
$data = json_decode($body);

try {
    $ninja = new InvoiceNinja($_ENV['NINJA_TOKEN']);
    $ninja->setUrl($_ENV['NINJA_URL']);
} catch (\Exception $e) {
    die($e);
}

logwrite("Getting clients... ");
$clients = $ninja->clients->all(["per_page"=>9999999]);
logwrite("Got ".sizeof($clients["data"])." clients");

$thisclient = $data->NotificationUrl->object->Payment->counterparty_alias->display_name;
logwrite("Looking for client ".$thisclient);
$client = findBestMatchIndex($thisclient,array_map(function($elm) { return $elm["name"]; },$clients["data"]));
logwrite("Best matching client has index ".$client);
logwrite($clients["data"][$client]["name"]);

$invoices = $ninja->invoices->all(["archived"=>false]);
logwrite("Found ".sizeof($invoices)." invoices");