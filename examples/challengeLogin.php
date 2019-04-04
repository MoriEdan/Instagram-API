<?php
/**
 * Created by PhpStorm.
 * User: miki
 * Date: 2019-03-25
 * Time: 15:43
 */

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/storage/RedisStorage.php';
require __DIR__.'/storage/util/RedisKeys.php';

/////// CONFIG ///////
$username = '';
$pk = '';
$password = '';
$debug = false;
$redisUrl = 'redis://localhost:6379';
$proxy = 'http://46.219.14.37:30686';
$truncatedDebug = false;
/////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug,[
    'storage' => 'custom',
    'class' => new RedisStorage(),
    'redis_url' => $redisUrl,
    'dbtablename' => 'account',
]);

// need to set some bad proxy so we are thrown on the challenge.
$ig->setProxy($proxy);
$ig->client->setVerifySSL(false);

try {
    $loginResponse = $ig->login($username,$pk, $password);

    if ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) {
        $twoFactorIdentifier = $loginResponse->getTwoFactorInfo()->getTwoFactorIdentifier();

        echo "TWO-FACTOR";
    }
    echo 'User: '.$ig->userLookup('miljan_rakita')->getUser();

} catch (\InstagramAPI\Exception\ChallengeRequiredException $e ) {

    $apiPath = $e->getResponse()->getChallenge()->getApiPath();

    preg_match('#/challenge/([0-9]*?)/(.*?)/#', $apiPath, $reg);
    $pk = $reg[1];
    $nonce = $reg[2];

    $challenge = $ig->challenge->getInfo($pk, $nonce);

    $step_name = $challenge->getStepName();
    $step_data = $challenge->getStepData();

    // The "STDIN" lets you paste the code via terminal for testing.
    // You should replace this line with the logic you want.
    // The verification code will be sent by Instagram via SMS.
    $verifyMethod = trim(fgets(STDIN));
    $ig->challenge->selectVerifyMethod($verifyMethod);

    $code = trim(fgets(STDIN));
    $ig->challenge->verify($code);

    echo 'User: '.$ig->userLookup('miljan_rakita')->getUser();
}
catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
