<?php
/**
 * Created by PhpStorm.
 * User: miljanrakita
 * Date: 2/4/19
 * Time: 7:18 PM
 */


set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////


$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug, [
    'storage' => 'custom',
    'class' => new \InstagramAPI\Settings\Storage\Components\RedisStorage(),
    'redis_host' => '127.0.0.1',
    'redis_port' => 6379,
    'dbtablename' => 'account',
]);

try {
    $loginResponse = $ig->login($username, $password);

    if ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) {
        $twoFactorIdentifier = $loginResponse->getTwoFactorInfo()->getTwoFactorIdentifier();

        // The "STDIN" lets you paste the code via terminal for testing.
        // You should replace this line with the logic you want.
        // The verification code will be sent by Instagram via SMS.
        $verificationCode = trim(fgets(STDIN));
        $ig->finishTwoFactorLogin($username, $password, $twoFactorIdentifier, $verificationCode);
    }
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
