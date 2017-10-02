<?php
namespace DPRMC\ClearStructure\Sentry\Services;
use Exception;
use SoapFault;

use DPRMC\ClearStructure\Sentry\Services\Exceptions\SentrySoapFaultFactory;

class ImportExcel extends Service {
    protected $stream;
    protected $sortTransactionsByTradeDate;
    protected $createTrades;
    protected $culture;


    public function __construct(
        string $location,
        string $user,
        string $pass,
        string $stream,
        bool $sortTransactionsByTradeDate,
        bool $createTrades,
        string $culture = 'en-US',
        $debug = false) {
        parent::__construct($location,
                            $user,
                            $pass,
                            $debug);

        $this->stream = $stream;
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
                      'stream' => $this->stream,
                      'sortTransactionsByTradeDate' => "false",
                      'createTrades' => "false",
                      'cultureString' => "en-US"];
        try {
            $response = $this->soapClient->ImportExcel($arguments);
            return $response;
        } catch (SoapFault $e) {
            throw SentrySoapFaultFactory::make($e);
        } catch (Exception $e) {
            throw $e;
        }
    }



}