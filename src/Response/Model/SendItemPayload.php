<?php

namespace InstagramAPI\Response\Model;

class SendItemPayload extends \InstagramAPI\Response
{
    public $client_context;
    public $message;
    /** @var string */
    public $item_id;
    /** @var string */
    public $timestamp;
    /** @var string */
    public $thread_id;
}