<?php
/**
 * Created by PhpStorm.
 * User: miki
 * Date: 2019-03-25
 * Time: 15:25
 */

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/storage/RedisStorage.php';
require __DIR__.'/storage/util/RedisKeys.php';
require __DIR__.'/storage/MongoLogger.php';

/////// CONFIG ///////
$username = '';
$pk = '';
$password = '';
$debug = false;
$redisUrl = '';
$proxy = '';
$truncatedDebug = false;
//////////////////////

$log = new MongoLogger();

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug,[
    'storage' => 'custom',
    'class' => new RedisStorage(),
    'redis_url' => $redisUrl,
    'dbtablename' => 'account',
], $log);

try {

    $loginResponse = $ig->login($username,$pk, $password);
    if ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) {
        $twoFactorIdentifier = $loginResponse->getTwoFactorInfo()->getTwoFactorIdentifier();

        // The "STDIN" lets you paste the code via terminal for testing.
        // You should replace this line with the logic you want.
        // The verification code will be sent by Instagram via SMS.
        $verificationCode = trim(fgets(STDIN));
        $user = $ig->finishTwoFactorLogin($username, $pk, $password, $twoFactorIdentifier, $verificationCode, '3');
    }

    echo 'User: '.$ig->userLookup('_lucido__')->getUser();

} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
