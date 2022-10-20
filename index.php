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

class DumpHTTPRequestToFile {
    public function execute($targetFile) {
        $data = sprintf(
            "%s %s %s\n\nHTTP headers:\n",
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            $_SERVER['SERVER_PROTOCOL']
        );

        foreach ($this->getHeaderList() as $name => $value) {
            $data .= $name . ': ' . $value . "\n";
        }

        $data .= "\nRequest body:\n";

        file_put_contents(
            $targetFile,
            $data . file_get_contents('php://input') . "\n"
        );

        echo("Done!\n\n");
    }

    private function getHeaderList() {
        $headerList = [];
        foreach ($_SERVER as $name => $value) {
            if (preg_match('/^HTTP_/',$name)) {
                // convert HTTP_HEADER_NAME to Header-Name
                $name = strtr(substr($name,5),'_',' ');
                $name = ucwords(strtolower($name));
                $name = strtr($name,' ','-');

                // add to list
                $headerList[$name] = $value;
            }
        }

        return $headerList;
    }
}


(new DumpHTTPRequestToFile)->execute('./dumprequest-'.microtime(true).'.txt');

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function logwrite($text) {
    file_put_contents("./log.txt",$text."\n",FILE_APPEND);
}

$body = file_get_contents('php://input');
$data = json_decode($body);

file_put_contents("request-".microtime(true).".json",var_export($data,true));

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
$client = findBestMatchIndex(array_map(function($elm) { return $elm["name"]; },$clients["data"]),$thisclient);
logwrite("Best matching client has index ".$client);