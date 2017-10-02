<?php
namespace DPRMC\ClearStructure\Sentry\Services;
use SoapFault;
use Exception;
class ExportAccount extends Service{

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
    public function __construct($location, $user, $pass, $debug = false, string $accountNumber) {
        parent::__construct($location,
                            $user,
                            $pass,
                            $debug);
        $this->accountNumber = $accountNumber;
    }

    public function run() {
        $parameters = [
            'userName' => $this->user,
            'password' => $this->pass,
            'accountNumber' => $this->accountNumber
        ];
        try{
            return $this->soapClient->ExportAccount($parameters);
        } catch(SoapFault $e) {
            if( preg_match("/Error Fetching http headers/",$e->getMessage()) === 1 ):
                throw new ErrorFetchingHeadersException($e->getMessage(), $e->getCode(), $e->getPrevious());
            elseif( preg_match("/This account \(.*\) was not found\./",$e->getMessage()) === 1 ):
                throw new AccountNotFoundException($e->getMessage(), $e->getCode(), $e->getPrevious());
            else:
                var_dump($e->getMessage());
                throw $e;
            endif;
        } catch(Exception $e) {
            throw $e;
        }

    }


}