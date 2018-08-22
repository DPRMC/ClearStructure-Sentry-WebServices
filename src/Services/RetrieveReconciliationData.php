<?php
namespace DPRMC\ClearStructure\Sentry\Services;
use Exception;
use SoapFault;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\SentrySoapFaultFactory;

/**
 * Class RetrieveReconciliationData
 * @package DPRMC\ClearStructure\Sentry\Services
 */
class RetrieveReconciliationData extends Service{

    /**
     * @var string The Portfolio to retrieve data for
     */
    protected $accountNumber;

    /**
     * @var string The date of the holdings
     */
    protected $holdingsAsOfDate;

    /**
     * @var string The Start Date to retrieve Transactions
     */
    protected $transactionsStartDate;

    /**
     * @var string The Finish Date to retrieve Transactions
     */
    protected $transactionsFinishDate;

    /**
     * @var bool Whether the Dates are based on Trade Date or Settlement Date
     */
    protected $isTradeDate;

    /**
     * RetrieveReconciliationData constructor.
     * @param string $location
     * @param string $user
     * @param string $pass
     * @param string $accountNumber
     * @param string $holdingsAsOfDate
     * @param string $transactionsStartDate
     * @param string $transactionsFinishDate
     * @param bool $isTradeDate
     * @param bool $debug
     */
    public function __construct(string $location, string $user, string $pass, string $accountNumber, string $holdingsAsOfDate, string $transactionsStartDate, string $transactionsFinishDate, bool $isTradeDate, bool $debug) {
        parent::__construct($location,
                            $user,
                            $pass,
                            $debug);

        $this->accountNumber = $accountNumber;
        $this->holdingsAsOfDate = $this->formatDate($holdingsAsOfDate);
        $this->transactionsStartDate = $this->formatDate($transactionsStartDate);
        $this->transactionsFinishDate = $this->formatDate($transactionsFinishDate);
        $this->isTradeDate = $isTradeDate;

    }

    /**
     * @param string $sheetName
     * @return array
     * @throws Exceptions\AccountNotFoundException
     * @throws Exceptions\DataCubeNotFoundException
     * @throws Exceptions\ErrorFetchingHeadersException
     * @throws SoapFault
     */
    public function run(string $sheetName) {
        $arguments = ['userName' => $this->user,
                      'password' => $this->pass,
                      'accountNumber' => $this->accountNumber,
                      'holdingsAsOfDate' => $this->holdingsAsOfDate,
                      'transactionsStartDate' => $this->transactionsStartDate,
                      'transactionsFinishDate' => $this->transactionsFinishDate,
                      'isTradeDate' => $this->isTradeDate];

        try {
            // $response comes back as a php stdClass with a public
            // property called RetrieveReconciliationData
            $response = $this->soapClient->RetrieveReconciliationData($arguments);
            $schemaXml = $response->RetrieveReconciliationDataResult->schema;
            $holdingsSchema = $this->parseHoldingsSchema($schemaXml);
            $transactionsSchema = $this->parseTransactionsSchema($schemaXml);
            $anyXml = $response->RetrieveReconciliationDataResult->any;

            list($holdings, $transactions) = $this->parseAnyData($anyXml);

            return [
                'holdingsSchema' => $holdingsSchema,
                'transactionsSchema' => $transactionsSchema,
                'holdings' => $holdings,
                'transactions' => $transactions
            ];
        } catch (SoapFault $e) {
            throw SentrySoapFaultFactory::make($e);
        } catch (Exception $e) {
            throw $e;
        }
    }


    private function formatDate($date){
        $unixTime = strtotime($date);
        return date('c', $unixTime);
    }

    /**
     * @param $xml
     * @return array
     * @throws \Sabre\Xml\ParseException
     */
    private function parseHoldingsSchema($xml){
        $service = new \Sabre\Xml\Service();
        $schemaArray = $service->parse($xml);
        $rawHoldingsSchema = $schemaArray[0]['value'][0]['value'][0]['value'][0]['value'][0]['value'][0]['value'];
        $schema = [];
        $typePrefix = 'xs:';
        $lengthOfTypePrefix = strlen($typePrefix);
        foreach($rawHoldingsSchema as $i => $field){
            $schema[] = [
                'name' => $field['attributes']['name'],
                'type' => substr($field['attributes']['type'],$lengthOfTypePrefix)
            ];
        }
        return $schema;
    }

    /**
     * @param $xml
     * @return array
     * @throws \Sabre\Xml\ParseException
     */
    private function parseTransactionsSchema($xml){
        $service = new \Sabre\Xml\Service();
        $schemaArray = $service->parse($xml);
        $rawTransactionsSchema = $schemaArray[0]['value'][0]['value'][0]['value'][1]['value'][0]['value'][0]['value'];
        $schema = [];
        $typePrefix = 'xs:';
        $lengthOfTypePrefix = strlen($typePrefix);
        foreach($rawTransactionsSchema as $i => $field){
            $schema[] = [
                'name' => $field['attributes']['name'],
                'type' => substr($field['attributes']['type'],$lengthOfTypePrefix)
            ];
        }
        return $schema;
    }

    private function parseAnyData(string $xml): array{
        $service = new \Sabre\Xml\Service();
        $dataArray = $service->parse($xml);

        $rawData = $dataArray[0]['value'];

        $holdings = [];
        $transactions = [];
        foreach($rawData as $i => $row){
            switch( $this->getRowType($row['name'])):
                case 'holding':
                    $holdings[] = $this->parseRow($row['value']);
                    break;

                case 'transaction':
                    $transactions[] = $this->parseRow($row['value']);
                    break;

                default:
                    throw new Exception("The row was named [" . $row['name'] . "] and that isn't in my switch statement.");
                    break;
            endswitch;
        }
        return [$holdings, $transactions];
    }

    /**
     * @param string $value
     * @return string
     * @throws Exception
     */
    private function getRowType(string $value): string{
        if( $value == '{}Holding_Reconciliation'){
            return 'holding';
        } elseif($value == '{}Transaction_Reconciliation'){
            return 'transaction';
        } else{
            throw new Exception("The row was named [" . $value . "] and we can't parse that.");
        }
    }

    /**
     * The way the Sabre parser works, it adds the namespace before the name within a pair of
     * squiggly brackets. Sentry doesn't return a namespace, so we just get a couple of
     * squiggly brackets. Remove those and return the rest of the string as the name.
     * @param string $value
     * @return string
     */
    private function getName(string $value): string{
        return substr($value,2);
    }

    /**
     * @param array $row
     * @return array
     */
    private function parseRow(array $row): array{
        $parsedRow = [];
        foreach($row as $i => $node){
            $parsedRow[$this->getName($node['name'])] = $node['value'];
        }
        return $parsedRow;
    }
}