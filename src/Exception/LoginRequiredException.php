<?php

namespace InstagramAPI\Exception;

use Throwable;

/**
 * Used when the server requires us to login again, and also used as a locally
 * triggered exception when we know for sure that we aren't logged in.
 */
class LoginRequiredException extends RequestException
{
    public function __construct($message = "", $code = 404, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
