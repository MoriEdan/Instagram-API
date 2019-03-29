<?php
/**
 * Created by PhpStorm.
 * User: miki
 * Date: 2019-03-29
 * Time: 20:02
 */

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/storage/RedisStorage.php';
require __DIR__.'/storage/util/RedisKeys.php';

/////// CONFIG ///////

/*
$username = 'xsaritahdi';
$password = 'vanzemunci98';
$pk = '6808219125';

$username = 'miljan_rakita';
$password = 'l25M16j17M27j23d23';
$pk = '2128436070';
*/

$debug = false;
$redisUrl = 'redis://localhost:6379';
$truncatedDebug = false;
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug,[
    'storage' => 'custom',
    'class' => new RedisStorage(),
    'redis_url' => $redisUrl,
    'dbtablename' => 'account',
]);

//$ig->setVerifySSL(false);
//$ig->setProxy('http://110.227.188.91:63141/');

try{
    $loginResponse = $ig->login($username,$pk, $password);

    if ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) {
        $twoFactorIdentifier = $loginResponse->getTwoFactorInfo()->getTwoFactorIdentifier();

        // The "STDIN" lets you paste the code via terminal for testing.
        // You should replace this line with the logic you want.
        // The verification code will be sent by Instagram via SMS.
        $verificationCode = trim(fgets(STDIN));
        $ig->finishTwoFactorLogin($username, $pk, $password, $twoFactorIdentifier, $verificationCode);
    }

}catch (\InstagramAPI\Exception\InstagramException $e) {
    echo "Exception: ".$e->getMessage();
}