<?php
namespace DPRMC\ClearStructure\Sentry\Services;
use Exception;
use SoapFault;
use SimpleXMLElement;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\SentrySoapFaultFactory;

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
        $this->dataSet = $this->formatDataSetAsXml($dataSetName, $dataSet);


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
                      'xml' => $this->dataSet,
                      'sortTransactionsByTradeDate' => false,
                      'createTrades' => false,
                      'cultureString' => "en-US"];
        try {
            $response = $this->soapClient->ImportDataXml($arguments);
            return $response;
        } catch (SoapFault $e) {
            throw SentrySoapFaultFactory::make($e);
        } catch (Exception $e) {
            throw $e;
        }
    }

    protected function formatDataSetAsXml(string $dataSetName, array $dataSet):string {
        $xml = '';
        $xmlElement = new SimpleXMLElement('<root/>');
        foreach($dataSet as $i => $row){
            foreach($row as $name => $value){
                $xmlElement->{$dataSetName}[$i]->{$name} = $value;
            }
        }
        $xml = trim($xmlElement->asXML());
        var_dump($xml);
        return $xml;
    }

    protected function formatDataSetAsXml_BAK(string $dataSetName, array $dataSet):string {


        $xml = new SimpleXMLElement('<' . $dataSetName . '/>');

        $this->array_to_xml($dataSet, $xml);

//        array_walk_recursive($dataSet,[$xml, 'addChild']);
        $xml = trim($xml->asXML());
        var_dump($xml);
        return $xml;
    }


    /**
     * @param array $dataSet
     * @param SimpleXMLElement $xml
     */
    protected function array_to_xml($dataSet, &$xml) {
        foreach($dataSet as $key => $value) {
            if(is_array($value)) {
                $key = is_numeric($key) ? "item$key" : $key;
                $subnode = $xml->addChild("$key");
                $this->array_to_xml($value, $subnode);
            }
            else {
                $key = is_numeric($key) ? "item$key" : $key;
                $xml->addChild("$key","$value");
            }
        }
    }


    /*protected function formatDataSetAsXml(string $dataSetName, array $dataSet):string {

        $output = '<xs:schema id="NewDataSet" xmlns="" xmlns:xs="http://www.w3.org/2001/XMLSchema[w3.org]" xmlns:msdata="urn:schemas-microsoft-com:xml-msdata">
    <xs:element name="NewDataSet" msdata:IsDataSet="true" msdata:UseCurrentLocale="true">
        <xs:complexType>
            <xs:choice minOccurs="0" maxOccurs="unbounded">
                <xs:element name="' . $dataSetName . '">
                ';

        foreach($dataSet as $row){
            $output .= '<xs:complexType>
                        <xs:sequence>';

            foreach($row as $name => $value){

            }

            $output .= '</xs:sequence>
                    </xs:complexType>';
        }

        $output .= '
                </xs:element>
            </xs:choice>
        </xs:complexType>
    </xs:element>
</xs:schema>';

        return $output;
    }*/


}