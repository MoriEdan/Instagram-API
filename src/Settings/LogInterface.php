<?php
/**
 * Created by PhpStorm.
 * User: miki
 * Date: 2019-05-22
 * Time: 19:52
 */

namespace InstagramAPI\Settings;


interface LogInterface
{

    /**
     * @param array $request
     * @param array $response
     * @param $pk
     * @throws \Exception
     * @return mixed
     */
    function log(array $request, array $response, $pk);

}