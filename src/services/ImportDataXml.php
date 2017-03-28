<?php
namespace DPRMC\ClearStructure\Sentry\Services;
use Exception;
use SoapFault;
use SimpleXMLElement;
use stdClass;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\SentrySoapFaultFactory;

/**
 * Class ImportDataXml
 * @package DPRMC\ClearStructure\Sentry\Services
 */
class ImportDataXml extends Service {
    protected $dataSet;
    protected $sortTransactionsByTradeDate;
    protected $createTrades;
    protected $culture;

    /**
     * ImportData constructor.
     * @param string $location      The URL of Clear Structure's server where you will send the request. Unique to each Sentry customer.
     * @param string $user          The username of the Sentry user account that will be used for authentication.
     * @param string $pass          The encrypted password of the Sentry user mentioned above. Contact Clear Structure for details on their Data Protection tool.
     * @param string $dataSetName   If this were an standard data file import of an Excel sheet, this would be the tab name from the spreadsheet.
     * @param array $dataSet        An associative array of data to be imported into Sentry. Top level is numerically indexed. Sub-arrays are name-value pairs.
     * @param bool $sortTransactionsByTradeDate Only used when importing transactions. Whether to order transactions by trade date.
     * @param bool $createTrades                Only used when importing trades. Whether to create an actual Trade Item or just a transaction
     * @param string $culture                   Example: en-US
     * @param bool $debug                       Not currently used.
     */
    public function __construct(string $location,
                                string $user,
                                string $pass,
                                string $dataSetName,
                                array $dataSet,
                                bool $sortTransactionsByTradeDate,
                                bool $createTrades,
                                string $culture = 'en-US',
                                $debug = false) {
        parent::__construct($location,
                            $user,
                            $pass,
                            $debug);


        $this->dataSet = $this->formatDataSetAsXml($dataSetName,
                                                   $dataSet);

        $this->sortTransactionsByTradeDate = $sortTransactionsByTradeDate;
        $this->createTrades = $createTrades;
        $this->culture = $culture;
    }


    /**
     * @return SimpleXMLElement $response->ImportDataXmlResult->any is filled with XML that you can parse.
     * @throws Exception
     * @throws Exceptions\AccountNotFoundException
     * @throws Exceptions\DataCubeNotFoundException
     * @throws Exceptions\ErrorFetchingHeadersException
     * @throws SoapFault
     */
    public function run() {
        ini_set('memory_limit', -1);
        $arguments = ['userName' => $this->user,
                      'password' => $this->pass,
                      'xmlString' => $this->dataSet,
                      'sortTransactionsByTradeDate' => $this->sortTransactionsByTradeDate,
                      'createTrades' => $this->createTrades,
                      'cultureString' => $this->culture];

        try {
            $response = $this->soapClient->ImportDataXml($arguments);
            $xml = $this->parseXmlFromResponse($response);

            $this->parseResultFromXmlInResponse($xml);
            return $xml;

        } catch (SoapFault $e) {
            throw SentrySoapFaultFactory::make($e);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Given a data set name, and a data set array, this function will format those into
     * valid XML that Sentry's ImportDataXml service can digest.
     * @param string $dataSetName The type of import we are attempting. For example: Security_Pricing_Update
     * @param array $dataSet An array of data that will get formatted as XML to be sent to ImportDataXml.
     * @return string               The formatted XML generated from the data set name, and data set array.
     */
    protected function formatDataSetAsXml(string $dataSetName, array $dataSet): string {
        $xmlElement = new SimpleXMLElement('<root/>');
        foreach ($dataSet as $i => $row) {
            foreach ($row as $name => $value) {
                $xmlElement->{$dataSetName}[$i]->{$name} = $value;
            }
        }
        $xml = trim($xmlElement->asXML());
        return $xml;
    }

    /**
     * Sentry returns a response that gets turned into a stdClass by php automatically. Inside
     * that stdClass is an XML string that holds details about the ImportDataXml attempt.
     * @param stdClass $response
     * @return SimpleXMLElement
     * @throws Exception
     */
    protected function parseXmlFromResponse(stdClass $response): SimpleXMLElement{
        if( ! isset($response->ImportDataXmlResult->any) ){
            throw new Exception("The response from Sentry was not formatted properly. If there actually was a valid response, then you need to add code to catch this variation.");
        }

        $xml = new SimpleXMLElement($response->ImportDataXmlResult->any);
        return $xml;
    }

    protected function parseResultFromXmlInResponse(SimpleXMLElement $xml){
        var_dump($xml->time);

        var_dump($xml->tables);
    }
}