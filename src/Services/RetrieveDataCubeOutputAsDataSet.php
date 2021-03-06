<?php

namespace DPRMC\ClearStructure\Sentry\Services;

use Exception;
use SoapFault;
use stdClass;
use SimpleXMLElement;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\SentrySoapFaultFactory;


/**
 * Class RetrieveDataCubeOutputAsDataSet
 * @package   DPRMC\ClearStructure\Sentry\Services
 * @author    Michael Drennen <mdrennen@deerparkrd.com>
 * @copyright 2017 Deer Park Road Management Corp
 * @see       https://github.com/DPRMC/ClearStructure-Sentry-WebServices#retrievedatacubeoutputasdataset Documentation
 *            for this class.
 */
class RetrieveDataCubeOutputAsDataSet extends Service {

    /**
     * @var string  The name of your data cube that you want to pull from Sentry.
     */
    protected $dataCubeName;

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
     * @var stdClass
     */
    protected $sentry4dataXmlNode;

    /**
     * When passing parameters into a Sentry data cube, you need to specify the
     * data type in the request. This array is every data type they accept.
     * @var array
     */
    protected $validDataTypes = [ 'dataCubeBooleanParameter'  => 'boolean',
                                  'dataCubeDateTimeParameter' => 'datetime',
                                  'dataCubeDecimalParameter'  => 'decimal',
                                  'dataCubeDoubleParameter'   => 'double',
                                  'dataCubeIntegerParameter'  => 'integer',
                                  'dataCubeStringParameter'   => 'string' ];

    /**
     * @var int The value for ini's default_socket_timeout. I set it arbitrarily large here, because I was
     * consistently getting errors because Sentry's system was slow to respond.
     */
    protected $defaultSocketTimeout = 9999999;


    /**
     * Example usage:
     * $params[] = $this->getDataCubeXmlParameter('as_of_date', $asOfDate, 'datetime');
     *
     * @param string $name The name you gave this parameter when you set up the data cube in Sentry's web
     *                         interface.
     * @param string $value The value you want to pass in for this parameter.
     * @param string $dataType The data type you are telling Sentry to expect. These are defined in the $validDataTypes
     *                         array.
     *
     * @return array    A associative array that gets added to a master array of parameters, that in turn, gets
     *                  passed into the getDataCubeXmlParameters() function.
     */
    public static function getDataCubeXmlParameter(string $name, string $value, string $dataType): array {
        return [ 'name'     => $name,
                 'value'    => $value,
                 'datatype' => $dataType ];
    }

    /**
     * RetrieveDataCubeOutputAsDataSet constructor.
     *
     * @param string $location The url that you are going to send this request to. Every Sentry customer has a
     *                             different URL.
     * @param string $user The user name of an active account you have with Sentry.
     * @param string $pass The password for that user name.
     * @param string $dataCubeName The name of the data cube you created in Sentry's web interface.
     * @param string $culture The language string you have to pass. Use en-US as default.
     * @param array $parameters An array of associative arrays returned from the getDataCubeXmlParameter() function.
     * @param bool $debug
     * @throws Exception
     */
    public function __construct(string $location, string $user, string $pass, string $dataCubeName, string $culture = 'en-US', array $parameters = [], bool $debug = FALSE) {
        parent::__construct($location,
                            $user,
                            $pass,
                            $debug);
        $this->dataCubeName = $dataCubeName;
        $this->culture      = $culture;
        $this->parameters   = $parameters;

        $xml                      = $this->formatDataCubeXml($this->dataCubeName,
                                                             $this->parameters);
        $this->sentry4dataXmlNode = $this->getDataCubeXml($xml);
    }

    /**
     * The front end for this class. Returns a nice array of data from Sentry. Exact format depends
     * on the data cube that you ask for.
     * @param array $sheetNames The names of the "tabs/worksheets/nodes" in the Sentry result set that we want.
     * @return array    Associative array with two keys: schema and data (rows of data). You probably want data.
     * @throws Exception
     * @throws Exceptions\AccountNotFoundException
     * @throws Exceptions\DataCubeNotFoundException
     * @throws Exceptions\ErrorFetchingHeadersException
     * @throws SoapFault
     */
    public function pull(array $sheetNames = [ 'data_node' ]): array {
        ini_set('memory_limit', -1);

        $existingDefaultSocketTimeout = ini_get( 'default_socket_timeout' );
        ini_set( 'default_socket_timeout', $this->defaultSocketTimeout );

        $arguments = [ 'userName'           => $this->user,
                       'password'           => $this->pass,
                       'dataCubeName'       => $this->dataCubeName,
                       'culture'            => $this->culture,
                       'sentry4dataXmlNode' => $this->sentry4dataXmlNode ];
        try {
            // $response comes back as a php stdClass with a public
            // property called: RetrieveDataCubeOutputAsDataSetResult;
            $response = $this->soapClient->RetrieveDataCubeOutputAsDataSet($arguments);

            $schema = new SimpleXMLElement($response->RetrieveDataCubeOutputAsDataSetResult->schema);
            $any    = new SimpleXMLElement($response->RetrieveDataCubeOutputAsDataSetResult->any);

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
            ini_set( 'default_socket_timeout', $existingDefaultSocketTimeout );
            throw SentrySoapFaultFactory::make($e);
        } catch ( Exception $e ) {
            ini_set( 'default_socket_timeout', $existingDefaultSocketTimeout );
            throw $e;
        }
    }

    /**
     * If for some reason you need to set a fixed socket timeout, use this method before you call run()
     * @param int $defaultSocketTimeoutInSeconds
     * @return $this
     */
    public function setDefaultSocketTimeout( int $defaultSocketTimeoutInSeconds ) {
        $this->defaultSocketTimeout = $defaultSocketTimeoutInSeconds;
        return $this;
    }


    /**
     * This function accepts two things. A user defined data cube name, and an associative array
     * of data cube parameters, which were return from the getDataCubeXmlParameter() function.
     * Given those things, it formats the xml that Sentry's web services system wants.
     * @return string An xml string that gets sent to Sentry's web services system.
     * @param string $dataCubeName The name you gave to the data cube in Sentry's web interface.
     * @param array $dataCubeParameters
     * @return string
     * @throws Exception
     */
    protected function formatDataCubeXml(string $dataCubeName, array $dataCubeParameters) {

        $sDataCubeParameters = '';
        foreach ( $dataCubeParameters as $key => $parameter ):
            $wrapperName         = $this->getWrapperNameFromDataType($parameter[ 'datatype' ]);
            $sDataCubeParameters .= '<dataCubeParameter name="' . $parameter[ 'name' ] . '"><' . $wrapperName . '>' . $parameter[ 'value' ] . '</' . $wrapperName . '></dataCubeParameter>';
        endforeach;

        return '
            <sentry4data xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://sentry.atlanticinfo.com/XML/namespace" xsi:schemaLocation="http://sentry.atlanticinfo.com/XML/namespace http://sentry.atlanticinfo.com/XML/namespace/Sentry4DataSchema.xsd" >
                <dataCubes>
                    <dataCube name="' . $dataCubeName . '">
                        <dataCubeParameters>' . $sDataCubeParameters . '</dataCubeParameters>
                    </dataCube>
                </dataCubes>
            </sentry4data>';
    }

    /**
     * After we format the xml string according to Sentry's specifications, we need to convert
     * the string into a stdClass object before we send it off in the request.
     *
     * @param string $xml An xml string returned from the formatDataCubeXml() function.
     *
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
     *
     * @param string $dataType
     *
     * @return string
     * @throws Exception
     */
    private function getWrapperNameFromDataType(string $dataType): string {
        $wrapperName = array_search($dataType,
                                    $this->validDataTypes);
        if ( FALSE === $wrapperName ) {
            throw new Exception("The data type [" . $dataType . "] was not found in validDataTypes");
        }

        return $wrapperName;
    }
}