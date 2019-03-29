<?php
/**
 * Created by PhpStorm.
 * User: miljanrakita
 * Date: 2/7/19
 * Time: 8:36 PM
 */

namespace InstagramAPI\Request;

use function Couchbase\defaultDecoder;
use InstagramAPI\Exception\InstagramException;
use InstagramAPI\Response;

class Challenge extends RequestCollection
{

    public function getInfo($pk, $nonce) {

        $this->ig->settings->set('pk', $pk);
        $this->ig->settings->set('nonce', $nonce);

        return $this->ig->request("challenge/$pk/$nonce/")
            ->setNeedsAuth(false)
            ->addParam('device_id', $this->ig->uuid)
            ->getResponse(new Response\ChallengeInfoResponse());
    }

    public function selectVerifyMethod($choice) {

        $pk = $this->ig->settings->get('pk');
        $nonce = $this->ig->settings->get('nonce');

        return $this->ig->request("challenge/$pk/$nonce/")
            ->setNeedsAuth(false)
            ->addPost('choice', $choice)
            ->addPost('device_id', $this->ig->uuid)
            ->getResponse(new Response\ChallengeSelectVerifyMethod());
    }


    public function verify($securityCode, $appRefreshInterval = 1800) {

        $pk = $this->ig->settings->get('pk');
        $nonce = $this->ig->settings->get('nonce');

        try {
            $response = $this->ig->request("challenge/$pk/$nonce/")
                ->setNeedsAuth(false)
                ->addPost('security_code', $securityCode)
                ->addPost('device_id', $this->ig->uuid)
                ->getResponse(new Response\LoginResponse());
        }catch (InstagramException $e) {

            $class = get_class($e);

            $reflection = new \ReflectionClass($class);

            $data = [
                'status' => 'fail',
                'type' => $reflection->getShortName(),
                'message' => $e->getMessage()
            ];

            echo json_encode($data).PHP_EOL;

            throw $e;

        }

        if (!$response->getLoggedInUser()) {

            $data = [
                'status' => 'fail',
                'exception_type' => 'TwoFactorException',
                'message' => $response->getMessage()
            ];

            echo json_encode($data).PHP_EOL;

            throw new \InstagramAPI\Exception\TwoFactorException('2FA Fuck');
        }


        echo json_encode([
            'status' => 'ok',
            'type' => 'account_logged_in'
        ]).PHP_EOL;

        $this->ig->_updateLoginState($response);
        $this->ig->_sendLoginFlow(true, $appRefreshInterval);

        return $response;
    }

    public function replay($choice) {

        $pk = $this->ig->settings->get('pk');
        $nonce = $this->ig->settings->get('nonce');

        return $this->ig->request("challenge/replay/$pk/$nonce/")
            ->setNeedsAuth(false)
            ->addPost('choice', $choice)
            ->addPost('device_id', $this->ig->uuid)
            ->getResponse(new Response\ChallengeSelectVerifyMethod());
    }

    public function reset() {

        $pk = $this->ig->settings->get('pk');
        $nonce = $this->ig->settings->get('nonce');

        return $this->ig->request("challenge/reset/$pk/$nonce/")
            ->setNeedsAuth(false)
            ->addPost('device_id', $this->ig->uuid)
            ->getResponse(new Response\ChallengeInfoResponse());
    }


    public function submitPhone($phone) {

        $pk = $this->ig->settings->get('pk');
        $nonce = $this->ig->settings->get('nonce');

        return $this->ig->request("challenge/$pk/$nonce/")
            ->setNeedsAuth(false)
            ->addPost('phone_number', $phone)
            ->addPost('device_id', $this->ig->uuid)
            ->getResponse(new Response\ChallengeSelectVerifyMethod());
    }
}