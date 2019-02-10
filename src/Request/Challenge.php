<?php
/**
 * Created by PhpStorm.
 * User: miljanrakita
 * Date: 2/7/19
 * Time: 8:36 PM
 */

namespace InstagramAPI\Request;

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

        $response = $this->ig->request("challenge/$pk/$nonce/")
            ->setNeedsAuth(false)
            ->addPost('security_code', $securityCode)
            ->addPost('device_id', $this->ig->uuid)
            ->getResponse(new Response\LoginResponse());

        if (!$response->getLoggedInUser()) {
            throw new \InstagramAPI\Exception\TwoFactorException('2FA Fuck');
        }

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