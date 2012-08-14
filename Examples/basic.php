<?php
/**
 * You must run `composer install` in order to generate autoloader for this example
 */
require __DIR__ . '/../vendor/autoload.php';

// Provide your API SID (username) and API token
DEFINE('API_SID', '');
DEFINE('API_TOKEN', '');

$twilio = new Twilio\Twilio(API_SID, API_TOKEN);

$numbers = $twilio->account->available_phone_numbers->getList('US', 'Local', array('Contains' => 646));
var_dump($numbers);