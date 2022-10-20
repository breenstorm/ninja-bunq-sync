<?php

require "vendor/autoload.php";

use InvoiceNinja\Sdk\InvoiceNinja;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function log($text) {
    file_put_contents("./log.txt",$text."\n",FILE_APPEND);
}

$body = file_get_contents('php://input');
$data = json_decode($body);

file_put_contents("lastrequest.json",$data);

try {
    $ninja = new InvoiceNinja($_ENV['NINJA_TOKEN']);
    $ninja->setUrl($_ENV['NINJA_URL']);
} catch (\Exception $e) {
    die($e);
}

log("Getting clients... ");
$clients = $ninja->clients->all(["per_page"=>9999999]);
log("Got ".sizeof($clients["data"])." clients");

