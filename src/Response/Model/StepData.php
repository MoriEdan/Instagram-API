<?php
/**
 * Created by PhpStorm.
 * User: miljanrakita
 * Date: 2/11/19
 * Time: 10:58 PM
 */

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class StepData extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'choice'             => 'int',
        'fb_access_token'    => 'string',
        'big_blue_token'     => 'string',
        'email'              => 'string',
        'phone_number'       => 'string',
        'security_code'      => 'string',
        'sms_resend_delay'     => 'int',
        'resend_delay'         => 'int',
        'phone_number_preview' => 'string',
        'contact_point'        => 'string',
        'form_type'            => 'string',
        'phone_number_formatted' => 'string',
    ];
}