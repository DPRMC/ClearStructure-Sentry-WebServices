<?php
namespace DPRMC\ClearStructure\Sentry\Services;
use Exception;
use SoapFault;

use DPRMC\ClearStructure\Sentry\Services\Exceptions\SentrySoapFaultFactory;

/**
 * Class ImportExcel
 * @package DPRMC\ClearStructure\Sentry\Services
 */
class ImportExcel extends Service {
    protected $stream;
    protected $sortTransactionsByTradeDate;
    protected $createTrades;
    protected $culture;


    /**
     * ImportExcel constructor.
     * @param string $location
     * @param string $user
     * @param string $pass
     * @param string $stream
     * @param bool $sortTransactionsByTradeDate
     * @param bool $createTrades
     * @param string $culture
     * @param bool $debug
     */
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


    /**
     * @return mixed
     * @throws Exceptions\AccountNotFoundException
     * @throws Exceptions\DataCubeNotFoundException
     * @throws Exceptions\ErrorFetchingHeadersException
     * @throws SoapFault
     */
    public function run(string $sheetName) {
        ini_set('memory_limit',
                -1);
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