<?php

namespace DPRMC\ClearStructure\Sentry\Services;

use DPRMC\ClearStructure\Sentry\Services\Exceptions\AccountNotFoundException;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\ErrorFetchingHeadersException;
use SoapFault;
use Exception;

class ExportAccount extends Service {

    /**
     * @var string  The account number (assigned by Sentry) of a portfolio.
     *              This is not the integer id. This is the short name of the portfolio.
     */
    protected $accountNumber;

    /**
     * ExportAccount constructor.
     * @param string $location
     * @param string $user
     * @param string $pass
     * @param bool $debug
     * @param string $accountNumber
     */
    public function __construct($location, $user, $pass, $debug, string $accountNumber) {
        parent::__construct($location,
                            $user,
                            $pass,
                            $debug);
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return mixed
     * @throws AccountNotFoundException
     * @throws ErrorFetchingHeadersException
     * @throws SoapFault
     */
    public function run() {
        $parameters = [
            'userName'      => $this->user,
            'password'      => $this->pass,
            'accountNumber' => $this->accountNumber,
        ];
        try {
            return $this->soapClient->ExportAccount($parameters);
        } catch ( SoapFault $e ) {
            if ( preg_match("/Error Fetching http headers/", $e->getMessage()) === 1 ):
                throw new ErrorFetchingHeadersException("You might need to add this code above this function call: [ini_set(\"default_socket_timeout\", 6000);] " . $e->getMessage(), $e->getCode(), $e->getPrevious());
            elseif ( preg_match("/This account \(.*\) was not found\./", $e->getMessage()) === 1 ):
                throw new AccountNotFoundException($e->getMessage(), $e->getCode(), $e->getPrevious());
            else:
                throw $e;
            endif;
        } catch ( Exception $e ) {
            throw $e;
        }

    }


}