<?php
namespace DPRMC\ClearStructure\Sentry\Services\Exceptions;
use SoapFault;
use Exception;

class AuthBadUserException extends SoapFault{

    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message,
                            $code,
                            $previous);
    }
}