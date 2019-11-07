<?php


namespace InstagramAPI\Events;


class LoginRequiredEvent extends InstagramEvent
{
    private $message;

    /**
     * LoginRequiredEvent constructor.
     * @param $message
     */
    public function __construct($pk, $message)
    {
        parent::__construct($pk);
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }
}