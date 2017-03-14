<?php
namespace DPRMC\ClearStructure\Sentry\Services;

use Exception;
use SoapFault;
use SimpleXMLElement;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\SentrySoapFaultFactory;

/**
 * Class RetrieveDataCubeOutputWithDefaultsAsDataSet
 * @package DPRMC\ClearStructure\Sentry\Services
 * @author Michael Drennen <mdrennen@deerparkrd.com>
 * @copyright 2017 Deer Park Road Management Corp
 */
class RetrieveDataCubeOutputWithDefaultsAsDataSet extends Service {

    /**
     * @var string  The name of your data cube that you want to pull from Sentry.
     */
    protected $dataCubeName;

    /**
     * @var string  The Culture for Dates. i.e. 'en-US'
     *              Required by ClearStructure.
     */
    protected $culture;

    /**
     * @var array An array of associative arrays returned from the getDataCubeXmlParameter() function.
     */
    protected $parameters;


    /**
     * RetrieveDataCubeOutputAsDataSet constructor.
     * @param string $location The url that you are going to send this request to. Every Sentry customer has a different URL.
     * @param string $user The user name of an active account you have with Sentry.
     * @param string $pass The password for that user name.
     * @param string $dataCubeName The name of the data cube you created in Sentry's web interface.
     * @param string $culture The language string you have to pass. Use en-US as default.
     * @param bool $debug
     */
    public function __construct(string $location, string $user, string $pass, string $dataCubeName, string $culture = 'en-US', bool $debug = false) {
        parent::__construct($location,
                            $user,
                            $pass,
                            $debug);
        $this->dataCubeName = $dataCubeName;
        $this->culture = $culture;
    }


    /**
     * The front end for this class. Returns a nice array of data from Sentry. Exact format depends
     * on the data cube that you ask for.
     * @return array    Associative array with two keys: schema and rows. You probably want rows.
     * @throws Exception
     * @throws Exceptions\AccountNotFoundException
     * @throws Exceptions\DataCubeNotFoundException
     * @throws Exceptions\ErrorFetchingHeadersException
     * @throws SoapFault
     */
    public function run(): array {
        ini_set('memory_limit',-1);
        $arguments = ['userName' => $this->user,
                      'password' => $this->pass,
                      'dataCubeName' => $this->dataCubeName,
                      'culture' => $this->culture,
                     ];
        try {
            // $response comes back as a php stdClass with a public
            // property called: RetrieveDataCubeOutputAsDataSetResult;
            $response = $this->soapClient->RetrieveDataCubeOutputWithDefaultsAsDataSet($arguments);

            $schema = new SimpleXMLElement($response->RetrieveDataCubeOutputWithDefaultsAsDataSetResult->schema);
            $any = new SimpleXMLElement($response->RetrieveDataCubeOutputWithDefaultsAsDataSetResult->any);
            $rows = [];
            foreach($any->NewDataSet->data_node as $index => $xmlRecord){
                $rows[] = $xmlRecord;
            }

            return [
                'schema' => $schema,
                'rows' => $rows
            ];
        } catch (SoapFault $e) {
            throw SentrySoapFaultFactory::make($e);
        } catch (Exception $e) {
            throw $e;
        }
    }
}