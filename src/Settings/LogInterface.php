<?php
/**
 * Created by PhpStorm.
 * User: miki
 * Date: 2019-05-22
 * Time: 19:52
 */

namespace InstagramAPI\Settings;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface LogInterface
{

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param $pk
     * @return mixed
     */
    function log(RequestInterface $request, ResponseInterface $response, $pk);

}