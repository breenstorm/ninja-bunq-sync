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

$searchiban = $_ENV['BUNQ_IBAN'];
if ($searchiban == $data->NotificationUrl->object->Payment->alias->iban) {
    logwrite("Mutation is for configured IBAN");
    logwrite("Getting clients... ");
    $clients = $ninja->clients->all(["per_page"=>9999999]);
    logwrite("Got ".sizeof($clients["data"])." clients");

    $thisclient = $data->NotificationUrl->object->Payment->counterparty_alias->display_name;
    logwrite("Looking for client ".$thisclient);
    $transactionclient_id = findBestMatchIndex($thisclient,array_map(function($elm) { return $elm["name"]; },$clients["data"]));
    $transactionclient = $clients["data"][$transactionclient_id]["name"];
    logwrite("Best matching client has index ".$transactionclient_id." and name ". $transactionclient);
    $transactionamount = floatval($data->NotificationUrl->object->Payment->amount->value);
    $transactiondesc = $data->NotificationUrl->object->Payment->description;

    logwrite("Desccription: ".$transactiondesc);
    logwrite("Amount: ".number_format($transactionamount,2,",",""));

    $invoices = $ninja->invoices->all(["status"=>"active"]);
    logwrite("Found ".sizeof($invoices["data"])." invoices");
    $candidates = [];
    foreach ($invoices["data"] as $invoice) {
        $invoiceclient = null;
        foreach ($clients["data"] as $client) {
            if ($client["id"]==$invoice["client_id"]) {
                $invoiceclient = $client["name"];
            }
        }
        $invoiceid = $invoice["id"];
        $invoicenum = $invoice["number"];
        $invoiceamount = floatval($invoice["amount"]);
        logwrite($invoicenum." Euro ".number_format($invoiceamount,2,",","")." for ".$invoiceclient);
        $found = false;
        if (($invoiceamount==$transactionamount) && (strpos($transactiondesc,$invoicenum)!==false)) {
            logwrite("Found invoice match by amount and description");
            $found = true;
        } elseif (($invoiceamount==$transactionamount) && ($invoiceclient==$transactionclient)) {
            logwrite("Found invoice match by amount and client");
            $found = true;
        }
        if ($found) {
            $candidates[] = (Object)[
                "invoice"=>$invoiceid,
                "client"=>$invoiceclient
            ];
        }
    }
    logwrite("Found ".sizeof($candidates)." possible matches");
    if (sizeof($candidates)==1) {
        logwrite("Certain about match. Applying payment.");
        //TODO: Create and apply payment
    } else {
        logwrite("No conclusive match found. Not applying payment.");
    }
}

