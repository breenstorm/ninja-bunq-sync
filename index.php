<?php

require "vendor/autoload.php";

$body = file_get_contents('php://input');
$data = json_decode($body);

file_put_contents("lastrequest.json",$data);
