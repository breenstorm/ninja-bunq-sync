<?php

require_once(__DIR__ . '/vendor/autoload.php');

use bunq\Context\ApiContext;
use bunq\Context\BunqContext;
use bunq\Model\Generated\Endpoint\MonetaryAccount;
use bunq\Model\Generated\Endpoint\User;
use bunq\Model\Generated\Endpoint\UserPerson;
use bunq\Model\Generated\Endpoint\NotificationFilterUrlMonetaryAccount;
use bunq\Model\Generated\Object\NotificationFilterUrl;
use bunq\Model\Core\NotificationFilterUrlMonetaryAccountInternal;
use bunq\Util\BunqEnumApiEnvironmentType;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$environmentType = BunqEnumApiEnvironmentType::PRODUCTION(); //SANDBOX or PRODUCTION
$apiKey = $_ENV['BUNQ_API_KEY']; // Replace with your API-key
$deviceDescription = 'Prutsproject on Laptop'; // Replace with your device description
$permittedIps = []; // List the real expected IPs of this device or leave empty to use the current IP

$apiContext = ApiContext::create(
    $environmentType,
    $apiKey,
    $deviceDescription,
    $permittedIps
);

BunqContext::loadApiContext($apiContext);

$monetaryAccountList = MonetaryAccount::listing();

foreach ($monetaryAccountList as $monetaryAccount) {
    printf($monetaryAccount->getMonetaryAccountBank->getDescription() . PHP_EOL);
}

//$user = BunqContext::getUserContext()->getUserPerson();
$primaryMonetaryAccount = BunqContext::getUserContext()->getPrimaryMonetaryAccount();
//
//print_r($primaryMonetaryAccount);

$notificationFilter = new NotificationFilterUrl('MUTATION', 'https://ninja.breenstorm.nl/callbacks/bunq/index.php');
$createdNotificationFilter = NotificationFilterUrlMonetaryAccountInternal::createWithListResponse($primaryMonetaryAccount->getId(), [$notificationFilter])->getValue();

print_r($createdNotificationFilter);
?>