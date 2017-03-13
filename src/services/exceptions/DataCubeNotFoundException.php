<?php
namespace DPRMC\ClearStructure\Sentry\Services\Exceptions;
use SoapFault;
use Exception;

class DataCubeNotFoundException extends SoapFault{

    protected $missingDataCubeName;

    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message,
                            $code,
                            $previous);
        $this->parseMissingDataCubeNameFromMessage($message);
    }

    protected function parseMissingDataCubeNameFromMessage(string $message){
        $matches = [];
        preg_match("/DataCube \((.*)\) could not be found\./",$message,$matches);
        $this->missingDataCubeName = $matches[1];
    }

    public function getMissingDataCubeName(){
        return $this->missingDataCubeName;
    }
}