<?php

namespace InstagramAPI\Events;

abstract class InstagramEvent
{
    private $pk;

    /**
     * InstagramEvent constructor.
     * @param $pk
     */
    public function __construct($pk)
    {
        $this->pk = $pk;
    }

    /**
     * @return mixed
     */
    public function getPk()
    {
        return $this->pk;
    }
}