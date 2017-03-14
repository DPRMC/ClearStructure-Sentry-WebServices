<?php
namespace DPRMC\ClearStructure\Sentry\Services;
use Exception;
use SimpleXMLElement;
use SoapFault;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\SentrySoapFaultFactory;


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

    public function run(){
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

            /**
             * @todo Parse the data(any) result next.
             */




            return [
                'holdingsSchema' => $holdingsSchema,
                'transactionsSchema' => $transactionsSchema
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
}