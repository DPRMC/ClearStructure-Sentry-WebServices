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
     * @param string $dataSetName If this were an standard data file import of an Excel sheet, this would be the tab name from the spreadsheet.
     * @param array $dataSet An associative array of data to be imported into Sentry. Top level is numerically indexed. Sub-arrays are name-value pairs.
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
        $debug = FALSE) {
        parent::__construct($location,
                            $user,
                            $pass,
                            $debug);
        //$this->dataSet = $this->formatXmlForDataSet($dataSetName, $dataSet);
        $this->dataSet = $this->formatDataSet($dataSetName, $dataSet);

        $this->sortTransactionsByTradeDate = $sortTransactionsByTradeDate;
        $this->createTrades                = $createTrades;

    }


    public function run() {
        ini_set('memory_limit', -1);

        $arguments = [ 'userName'                    => $this->user,
                       'password'                    => $this->pass,
                       'dataSet'                     => $this->dataSet,
                       'sortTransactionsByTradeDate' => FALSE,
                       'createTrades'                => FALSE,
                       'cultureString'               => 'en-US' ];
        try {
            $response = $this->soapClient->ImportData($arguments);
            return $response;
        } catch ( SoapFault $e ) {
            throw SentrySoapFaultFactory::make($e);
        } catch ( Exception $e ) {
            throw $e;
        }
    }

    /**
     * @param string $dataSetName
     * @param array $dataSet
     * @return string
     */
    protected function formatXmlForDataSet(string $dataSetName, array $dataSet): string {
        $xml = '<?xml version="1.0"?>
<xs:schema id="NewDataSet" xmlns="" xmlns:xs="http://www.w3.org/2001/XMLSchema[w3.org]" xmlns:msdata="urn:schemas-microsoft-com:xml-msdata">
  <xs:element name="NewDataSet" msdata:IsDataSet="true" msdata:UseCurrentLocale="true">
    <xs:complexType>
      <xs:choice minOccurs="0" maxOccurs="unbounded">
        <xs:element name="Security_Pricing_Update">
          <xs:complexType>
            <xs:sequence>
              <xs:element name="scheme_name" type="xs:string" minOccurs="0" />
              <xs:element name="scheme_identifier" type="xs:string" minOccurs="0" />
              <xs:element name="market_data_authority_name" type="xs:string" minOccurs="0" />
              <xs:element name="as_of_date" type="xs:datetime" minOccurs="0" />
              <xs:element name="action" type="xs:string" minOccurs="0" />
              <xs:element name="price" type="xs:decimal" minOccurs="0" />
            </xs:sequence>
          </xs:complexType>
        </xs:element>
      </xs:choice>
    </xs:complexType>
  </xs:element>
';
        $xml .= '<NewDataSet>';
        foreach ( $dataSet as $i => $pair ) {
            $xml .= '<' . $this->formatLabelForXml($dataSetName) . '>';
            foreach ( $pair as $name => $value ):
                $xml .= '<' . $this->formatLabelForXml($name) . '>' . $value . '</' . $this->formatLabelForXml($name) . '>';
            endforeach;
            $xml .= '</' . $this->formatLabelForXml($dataSetName) . '>';
        }
        $xml .= '</xs:schema>';
        return $xml;
    }

    protected function formatDataSet(string $dataSetName, array $dataSet): string {
        $dataSetToBeSerialized = [ $dataSetName => $dataSet ];
        return serialize($dataSetToBeSerialized);
    }

    /**
     *
     * @param string $string
     * @return mixed|string
     */
    private function formatLabelForXml(string $string) {
        $string = str_replace(' ', 'x0020', $string);
        return $string;
    }

}