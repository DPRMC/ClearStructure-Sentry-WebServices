<?php
namespace DPRMC\ClearStructure\Sentry\Services\Exceptions;
use SoapFault;

/**
 * Class SentrySoapFaultFactory
 * @package DPRMC\ClearStructure\Sentry\Services\Exceptions
 */
class SentrySoapFaultFactory {

    /**
     * Sentry throws a SoapFault object as a response to a Web Services request for a multitude
     * of reasons. This function parses the message from Sentry, and returns a custom Exception
     * that simplifies error handling on our end.
     * @param SoapFault $e
     * @return AccountNotFoundException|DataCubeNotFoundException|ErrorFetchingHeadersException|SoapFault
     */
    public static function make(SoapFault $e){
        if( preg_match("/Error Fetching http headers/",$e->getMessage()) === 1 ):
            return new ErrorFetchingHeadersException($e->getMessage(), $e->getCode(), $e->getPrevious());
        elseif( preg_match("/This account \(.*\) was not found\./",$e->getMessage()) === 1 ):
            return new AccountNotFoundException($e->getMessage(), $e->getCode(), $e->getPrevious());
        elseif( preg_match("/DataCube \(.*\) could not be found\./",$e->getMessage()) === 1 ):
            return new DataCubeNotFoundException($e->getMessage(), $e->getCode(), $e->getPrevious());
        elseif( preg_match("/Password did not match\./",$e->getMessage()) === 1 ):
            return new AuthBadPasswordException($e->getMessage(), $e->getCode(), $e->getPrevious());
        elseif( preg_match("/RijndaelCryptographyUtil\.Decrypt/",$e->getMessage()) === 1 ):
            return new AuthPasswordEncryptionException($e->getMessage(), $e->getCode(), $e->getPrevious());
        elseif( preg_match("/Could not authenticate the user\./",$e->getMessage()) === 1 ):
            return new AuthBadUserException($e->getMessage(), $e->getCode(), $e->getPrevious());
        elseif( preg_match("/The following culture string is not valid: (.*)\. Please use a culture like 'en-US' or 'en-GB'\./",$e->getMessage()) === 1 ):
            return new InvalidCultureStringException($e->getMessage(), $e->getCode(), $e->getPrevious());
        elseif( preg_match("/There is no import data to process\./",$e->getMessage()) === 1 ):
            return new NoImportDataToProcessException($e->getMessage(), $e->getCode(), $e->getPrevious());
        elseif( preg_match("/Data at the root level is invalid\./",$e->getMessage()) === 1 ):
            return new BadImportDataSubmittedException($e->getMessage(), $e->getCode(), $e->getPrevious());
        else:
            return $e;
        endif;
    }
}