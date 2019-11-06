<?php

namespace InstagramAPI\Exception;

use Throwable;

/**
 * Means that you have become throttled by Instagram's API server
 * because of too many requests. You must slow yourself down!
 */
class ThrottledException extends RequestException
{
    public function __construct($message = "", $code = 429, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
