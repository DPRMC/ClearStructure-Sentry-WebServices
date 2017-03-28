<?php
namespace DPRMC\ClearStructure\Sentry\Services;
use Exception;
use SoapFault;
use SimpleXMLElement;
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
     * @param string $location
     * @param string $user
     * @param string $pass
     * @param string $dataSetName If this were an standard data file import of an Excel sheet, this would be the tab name from the spreadsheet.
     * @param array $dataSet An associative array of data to be imported into Sentry. Top level is numerically indexed. Sub-arrays are name-value pairs.
     * @param bool $sortTransactionsByTradeDate
     * @param bool $createTrades
     * @param string $culture
     * @param bool $debug
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
    }


    /**
     * @return mixed
     * @throws Exception
     * @throws Exceptions\AccountNotFoundException
     * @throws Exceptions\DataCubeNotFoundException
     * @throws Exceptions\ErrorFetchingHeadersException
     * @throws SoapFault
     */
    public function run() {
        ini_set('memory_limit',
                -1);
        $arguments = ['userName' => $this->user,
                      'password' => $this->pass,
                      'dataSet' => $this->dataSet,
                      'sortTransactionsByTradeDate' => $this->sortTransactionsByTradeDate,
                      'createTrades' => $this->createTrades,
                      'cultureString' => $this->culture];

        try {
            $response = $this->soapClient->ImportDataXml($arguments);
            return $response;
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
}