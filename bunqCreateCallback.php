<?php
use bunq\Context\ApiContext;
use bunq\Context\BunqContext;
use bunq\Model\Generated\Endpoint\Company;
use bunq\Model\Generated\Endpoint\MonetaryAccount;
use bunq\Model\Generated\Endpoint\User;
use bunq\Model\Generated\Endpoint\UserCompany;
use bunq\Model\Generated\Endpoint\UserPerson;
use bunq\Model\Generated\Endpoint\NotificationFilterUrlMonetaryAccount;
use bunq\Model\Generated\Object\NotificationFilterUrl;
use bunq\Model\Core\NotificationFilterUrlMonetaryAccountInternal;
use bunq\Util\BunqEnumApiEnvironmentType;

require_once(__DIR__ . '/vendor/autoload.php');

if (!isset($argv[1])) {
    die ("Supply api key as param\n");
}

$environmentType = BunqEnumApiEnvironmentType::PRODUCTION(); //SANDBOX or PRODUCTION
$apiKey = $argv[1]; // Replace with your API-key
$deviceDescription = gethostname(); // Replace with your device description
$permittedIps = []; // List the real expected IPs of this device or leave empty to use the current IP

$apiContext = ApiContext::create(
    $environmentType,
    $apiKey,
    $deviceDescription,
    $permittedIps
);

BunqContext::loadApiContext($apiContext);

$userCompany = UserCompany::get();

var_dump($userCompany);

//$monetaryAccountList = MonetaryAccount::listing();
//
//foreach ($monetaryAccountList as $monetaryAccount) {
//    printf($monetaryAccount->getMonetaryAccountBank->getDescription() . PHP_EOL);
//}
//
//$companyList = Company::listing();
//var_dump($companyList);
//
//foreach ($companyList as $company) {
//    printf($company->getUserCompany()->getName() . PHP_EOL);
//}

//$user = BunqContext::getUserContext()->getUserPerson();
//$primaryMonetaryAccount = BunqContext::getUserContext()->getPrimaryMonetaryAccount();
//
//print_r($primaryMonetaryAccount);

//$notificationFilter = new NotificationFilterUrl('MUTATION', 'https://ninja.breenstorm.nl/callbacks/bunq.php');
//$createdNotificationFilter = NotificationFilterUrlMonetaryAccountInternal::createWithListResponse($primaryMonetaryAccount->getId(), [$notificationFilter])->getValue();
//
//print_r($createdNotificationFilter);
?>