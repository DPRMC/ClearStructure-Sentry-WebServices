<?php
namespace DPRMC\ClearStructure\Sentry\Services;
use Exception;
use SoapFault;
use SimpleXMLElement;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\SentrySoapFaultFactory;

class ImportData extends Service {
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
     * @param array $dataSet    An associative array of data to be imported into Sentry. Top level is numerically indexed. Sub-arrays are name-value pairs.
     * @param bool $sortTransactionsByTradeDate
     * @param bool $createTrades
     * @param string $culture
     * @param bool $debug
     */
    public function __construct(
        string $location,
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
        //$this->dataSet = $this->formatXmlForDataSet($dataSetName, $dataSet);
        //$this->dataSet = $this->formatDataSet($dataSetName, $dataSet);
        $this->dataSet = $this->formatDataSet($dataSetName, $dataSet);
        print("\n\n" . $this->dataSet);

        $this->sortTransactionsByTradeDate = $sortTransactionsByTradeDate;
        $this->createTrades = $createTrades;

    }


    public function run() {
        ini_set('memory_limit',
                -1);
        /*$arguments = ['userName' => $this->user,
                      'password' => $this->pass,
                      'dataSet' => $this->dataSet,
                      'sortTransactionsByTradeDate' => $this->sortTransactionsByTradeDate,
                      'createTrades' => $this->createTrades,
                      'cultureString' => $this->culture];*/

        $arguments = ['userName' => $this->user,
                      'password' => $this->pass,
                      'dataSet' => $this->dataSet,
                      'sortTransactionsByTradeDate' => "false",
                      'createTrades' => "false",
                      'cultureString' => "en-US"];
        try {
            $response = $this->soapClient->ImportData($arguments);
            return $response;
        } catch (SoapFault $e) {
            throw SentrySoapFaultFactory::make($e);
        } catch (Exception $e) {
            throw $e;
        }
    }



    protected function formatDataSet(string $dataSetName, array $dataSet):string {
        return '<Params></Params>';
        $output = '';
        $output = '<Params>';
        $output = '<Param Name="' . $dataSetName . '">' . $dataSetName . ' DataSet</Param>';
        $output = '</Params>';
        /*foreach($dataSet as $row){

        }*/

        return $output;
    }


}