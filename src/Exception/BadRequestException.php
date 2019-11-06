<?php

namespace InstagramAPI\Exception;

use Throwable;

/**
 * Used for endpoint calls that fail with HTTP code "400 Bad Request", but only
 * if no other more serious exception was found in the server response.
 */
class BadRequestException extends EndpointException
{

    public function __construct($message = "", $code = 400, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
