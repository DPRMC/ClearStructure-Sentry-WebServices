<?php
namespace DPRMC\ClearStructure\Sentry\Services\Exceptions;
use SoapFault;
use Exception;

/**
 * Class AuthPasswordEncryptionException
 * @package DPRMC\ClearStructure\Sentry\Services\Exceptions
 */
class AuthPasswordEncryptionException extends SoapFault{

    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct("The password you used was not encrypted properly. Use the Data Protector page on Sentry's website to encrypt the password you send. " . $message,
                            $code,
                            $previous);
    }
}