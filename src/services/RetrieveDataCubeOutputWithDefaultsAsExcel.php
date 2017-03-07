<?php
namespace DPRMC\ClearStructure\Sentry\Services;

class RetrieveDataCubeOutputWithDefaultsAsExcel extends Service{

    /**
     * @var string  The name of your data cube that you want to pull from Sentry.
     */
    protected $dataCubeName;

    /**
     * @var string  The Culture for Dates. i.e. 'en-US'
     *              Required by ClearStructure.
     */
    protected $culture;

    public function __construct($location, $user, $pass, $debug = false, $dataCubeName, $culture='en-US') {
        parent::__construct($location,
                            $user,
                            $pass,
                            $debug);
        $this->dataCubeName = $dataCubeName;
        $this->culture = $culture;
    }

    public function run() {
        $parameters = [
            'userName' => $this->user,
            'password' => $this->pass,
            'dataCubeName' => $this->dataCubeName,
            'culture' => $this->culture
        ];
        try{
            return $this->soapClient->RetrieveDataCubeOutputWithDefaultsAsExcel($parameters);
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