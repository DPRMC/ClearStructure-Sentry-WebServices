<?php
namespace DPRMC\ClearStructure\Sentry\Services;
use Exception;
use SoapFault;
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
     * @param string $dataSet
     * @param bool $sortTransactionsByTradeDate
     * @param bool $createTrades
     * @param string $culture
     * @param bool $debug
     */
    public function __construct(string $location, string $user, string $pass, string $dataSet, bool $sortTransactionsByTradeDate, bool $createTrades, string $culture = 'en-US', $debug = false) {
        parent::__construct($location,
                            $user,
                            $pass,
                            $debug);
        $this->dataSet = $dataSet;
        $this->sortTransactionsByTradeDate = $sortTransactionsByTradeDate;
        $this->createTrades = $createTrades;

    }


    public function run() {
        ini_set('memory_limit',
                -1);
        $arguments = ['userName' => $this->user,
                      'password' => $this->pass,
                      'dataSet' => $this->dataSet,
                      'sortTransactionsByTradeDate' => $this->sortTransactionsByTradeDate,
                      'createTrades' => $this->createTrades,
                      'culture' => $this->culture];
        try {
            $response = $this->soapClient->ImportData($arguments);
            return $response;
        } catch (SoapFault $e) {
            throw SentrySoapFaultFactory::make($e);
        } catch (Exception $e) {
            throw $e;
        }
    }

}