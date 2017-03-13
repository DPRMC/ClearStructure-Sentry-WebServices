<?php
namespace DPRMC\ClearStructure\Sentry\Services\Exceptions;
use SoapFault;
use Exception;

class InvalidCultureStringException extends SoapFault{

    /**
     * @var string The invalid culture string that was passed in the Web Services request.
     */
    protected $invalidCultureString;

    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message,
                            $code,
                            $previous);
    }

    protected function parseInvalidCultureString(string $message){
        $matches = [];
        preg_match("/DataCube \((.*)\) could not be found\./",$message,$matches);
        $this->invalidCultureString = $matches[1];
    }

    public function getInvalidCultureString(){
        return $this->invalidCultureString;
    }
}