<?php

namespace DPRMC\ClearStructure\Sentry\Services;

use Exception;
use SoapFault;
use stdClass;
use SimpleXMLElement;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\SentrySoapFaultFactory;


/**
 * Class RetrieveDataReportAsDataSet
 * @package DPRMC\ClearStructure\Sentry\Services
 * @author Michael Drennen <mdrennen@deerparkrd.com>
 * @copyright 2017 Deer Park Road Management Corp
 */
class RetrieveDataReportAsDataSet extends Service {

    /**
     * @var string  The name of your data report that you want to pull from Sentry.
     */
    protected $dataReportName;

    /**
     * @var
     */
    protected $folderName;

    /**
     * @var stdClass
     */
    protected $sentry4dataXmlNode;

    /**
     * @var string  The Culture for Dates. i.e. 'en-US'
     *              Required by ClearStructure.
     */
    protected $culture;

    /**
     * @var array An array of associative arrays returned from the getDataCubeXmlParameter() function.
     */
    protected $parameters;


    /**
     * When passing parameters into a Sentry data cube, you need to specify the
     * data type in the request. This array is every data type they accept.
     * @var array
     */
    protected $validDataTypes = [ 'dataReportBooleanParameter'  => 'boolean',
                                  'dataReportDateTimeParameter' => 'datetime',
                                  'dataReportDecimalParameter'  => 'decimal',
                                  'dataReportDoubleParameter'   => 'double',
                                  'dataReportIntegerParameter'  => 'integer',
                                  'dataReportStringParameter'   => 'string' ];

    /**
     * Example usage:
     * $params[] = $this->getDataCubeXmlParameter('as_of_date', $asOfDate, 'datetime');
     * @param string $name The name you gave this parameter when you set up the data cube in Sentry's web interface.
     * @param string $value The value you want to pass in for this parameter.
     * @param string $dataType The data type you are telling Sentry to expect. These are defined in the $validDataTypes array.
     * @return array    A associative array that gets added to a master array of parameters, that in turn, gets
     *                  passed into the getDataCubeXmlParameters() function.
     */
    public static function getDataReportXmlParameter(string $name, string $value = '', string $dataType): array {
        return [ 'name'     => $name,
                 'value'    => $value,
                 'datatype' => $dataType ];
    }

    /**
     * RetrieveDataCubeOutputAsDataSet constructor.
     * @param string $location The url that you are going to send this request to. Every Sentry customer has a different URL.
     * @param string $user The user name of an active account you have with Sentry.
     * @param string $pass The password for that user name.
     * @param string $dataReportName The name of the data cube you created in Sentry's web interface.
     * @param string $culture The language string you have to pass. Use en-US as default.
     * @param array $parameters An array of associative arrays returned from the getDataCubeXmlParameter() function.
     * @param bool $debug
     */
    public function __construct(string $location,
                                string $user,
                                string $pass,
                                string $dataReportName,
                                string $folderName,
                                string $culture = 'en-US',
                                array $parameters = [],
                                bool $debug = FALSE) {
        parent::__construct($location,
                            $user,
                            $pass,
                            $debug);
        $this->dataReportName = $dataReportName;
        $this->folderName     = $folderName;
        $this->culture        = $culture;
        $this->parameters     = $parameters;

        $xml = $this->formatDataReportXml($this->dataReportName,
                                          $this->folderName,
                                          $this->parameters);

        print_r($xml);

        $this->sentry4dataXmlNode = $this->getDataCubeXml($xml);
    }


    /**
     * The front end for this class. Returns a nice array of data from Sentry. Exact format depends
     * on the data cube that you ask for.
     * @param array $sheetNames The names of the "tabs/worksheets/nodes" in the Sentry result set that we want.
     * @return array    Associative array with two keys: schema and rows. You probably want rows.
     * @throws Exception
     * @throws Exceptions\AccountNotFoundException
     * @throws Exceptions\DataCubeNotFoundException
     * @throws Exceptions\ErrorFetchingHeadersException
     * @throws SoapFault
     */
    public function pull(array $sheetNames = [ 'data_node' ]): array {
        ini_set('memory_limit',
                -1);
        $arguments = [ 'userName'           => $this->user,
                       'password'           => $this->pass,
                       'dataReportName'     => $this->dataReportName,
                       'folderName'         => $this->folderName,
                       'sentry4dataXmlNode' => $this->sentry4dataXmlNode,
                       'culture'            => $this->culture ];
        try {
            // $response comes back as a php stdClass with a public
            // property called: RetrieveDataCubeOutputAsDataSetResult;
            $response = $this->soapClient->RetrieveDataReportAsDataSet($arguments);

            $schema = new SimpleXMLElement($response->RetrieveDataReportAsDataSetResult->schema);
            $any    = new SimpleXMLElement($response->RetrieveDataReportAsDataSetResult->any);

            $data = [];
            foreach ( $sheetNames as $i => $sheetName ):
                $rows = [];
                foreach ( $any->NewDataSet->{$sheetName} as $index => $xmlRecord ) :
                    $rows[] = $xmlRecord;
                endforeach;
                $data[ $sheetName ] = $rows;
            endforeach;

            return [
                'schema' => $schema,
                'data'   => $data,
            ];
        } catch ( SoapFault $e ) {
            throw SentrySoapFaultFactory::make($e);
        } catch ( Exception $e ) {
            throw $e;
        }
    }


    /**
     * This function accepts two things. A user defined data cube name, and an associative array
     * of data cube parameters, which were return from the getDataCubeXmlParameter() function.
     * Given those things, it formats the xml that Sentry's web services system wants.
     *
     * @param string $dataReportName The name you gave to the data cube in Sentry's web interface.
     * @param string $folderName
     * @param array $dataReportParameters
     * @return string An xml string that gets sent to Sentry's web services system.
     * @throws Exception
     */
    protected function formatDataCubeXml(string $dataReportName, string $folderName, array $dataReportParameters) {

        $concatenatedDataReportParameters = '';
        foreach ( $dataReportParameters as $key => $parameter ):
            $tag                              = $this->getTagFromDataType($parameter[ 'datatype' ]);
            $concatenatedDataReportParameters .= '<dataReportParameter name="' . $parameter[ 'name' ] . '"><' . $tag . '>' . $parameter[ 'value' ] . '</' . $tag . '></dataReportParameter>';
        endforeach;

        return '<sentry4data xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://sentry.atlanticinfo.com/XML/namespace" xsi:schemaLocation="http://sentry.atlanticinfo.com/XML/namespace http://sentry.atlanticinfo.com/XML/namespace/Sentry4DataSchema.xsd" >
                <dataReports>
                    <dataReport name="' . $dataReportName . '" folderName="' . $folderName . '">
                        <dataReportParameters>' . $concatenatedDataReportParameters . '</dataReportParameters>
                    </dataReport>
                </dataReports>
            </sentry4data>';
    }

    protected function formatDataReportXml(string $dataReportName, string $folderName, array $dataReportParameters) {

        $concatenatedDataReportParameters = '';
        foreach ( $dataReportParameters as $key => $parameter ):
            $tag                              = $this->getTagFromDataType($parameter[ 'datatype' ]);
            $concatenatedDataReportParameters .= '<dataReportParameter name="' . $parameter[ 'name' ] . '"><' . $tag . '>' . $parameter[ 'value' ] . '</' . $tag . '></dataReportParameter>';
        endforeach;

        return '<sentry4data>
                <dataReports>
                    <dataReport name="' . $dataReportName . '" folderName="' . $folderName . '">
                        <dataReportParameters>' . $concatenatedDataReportParameters . '</dataReportParameters>
                    </dataReport>
                </dataReports>
            </sentry4data>';
    }

    /**
     * After we format the xml string according to Sentry's specifications, we need to convert
     * the string into a stdClass object before we send it off in the request.
     * @param string $xml An xml string returned from the formatDataCubeXml() function.
     * @return stdClass This object is the format that Sentry's Web Services wants.
     */
    protected function getDataCubeXml(string $xml): stdClass {
        $DOMDocument              = new stdClass;
        $DOMDocument->any         = $xml;
        $DOMDocument->textContent = $xml;
        return $DOMDocument;
    }

    /**
     * The xml that Sentry's Web Services wants requires a data type wrapper tag around each parameters.
     * Given a user specified data type, this method returns the appropriate wrapper name.
     * @param string $dataType
     * @return string
     * @throws Exception
     */
    private function getTagFromDataType(string $dataType): string {
        $wrapperName = array_search($dataType,
                                    $this->validDataTypes);
        if ( $wrapperName === FALSE ) {
            throw new Exception("The data type [" . $dataType . "] was not found in validDataTypes");
        }
        return $wrapperName;
    }

    /**
     * A wrapper function to make the main code more readable. Returns true if the data
     * type passed in, is one that Sentry's Web Services will understand.
     * @param string $dataType
     * @return bool
     */
    private function isValidDataType(string $dataType): bool {
        return in_array($dataType,
                        $this->validDataTypes);
    }
}