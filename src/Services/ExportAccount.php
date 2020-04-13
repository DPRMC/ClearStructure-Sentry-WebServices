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
     * @var int The value for ini's default_socket_timeout. I set it arbitrarily large here, because I was
     * consistently getting errors because Sentry's system was slow to respond.
     */
    protected $defaultSocketTimeout = 9999999;

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

        $existingDefaultSocketTimeout = ini_get( 'default_socket_timeout' );
        ini_set( 'default_socket_timeout', $this->defaultSocketTimeout );

        $parameters = [
            'userName'      => $this->user,
            'password'      => $this->pass,
            'accountNumber' => $this->accountNumber,
        ];
        try {
            return $this->soapClient->ExportAccount($parameters);
        } catch
        ( SoapFault $e ) {
            ini_set( 'default_socket_timeout', $existingDefaultSocketTimeout );
            if ( preg_match("/Error Fetching http headers/", $e->getMessage()) === 1 ):
                throw new ErrorFetchingHeadersException("You might need to add this code above this function call: [ini_set(\"default_socket_timeout\", 6000);] " . $e->getMessage(), $e->getCode(), $e->getPrevious());
            elseif ( preg_match("/This account \(.*\) was not found\./", $e->getMessage()) === 1 ):
                throw new AccountNotFoundException($e->getMessage(), $e->getCode(), $e->getPrevious());
            else:
                throw $e;
            endif;
        } catch ( Exception $e ) {
            ini_set( 'default_socket_timeout', $existingDefaultSocketTimeout );
            throw $e;
        }

    }


}