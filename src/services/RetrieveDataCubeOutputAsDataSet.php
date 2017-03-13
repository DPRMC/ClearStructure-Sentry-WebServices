<?php
namespace DPRMC\ClearStructure\Sentry\Services;
use Exception;
use SoapFault;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\SentrySoapFaultFactory;

class RetrieveDataCubeOutputAsDataSet extends Service{

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
     * When passing parameters into a Sentry data cube, you need to specify the
     * data type in the request. This array is every data type they accept.
     * @var array
     */
    protected $validDataTypes = [
        'dataCubeBooleanParameter' => 'boolean',
        'dataCubeDateTimeParameter' => 'datetime',
        'dataCubeDecimalParameter' => 'decimal',
        'dataCubeDoubleParameter' => 'double',
        'dataCubeIntegerParameter' => 'integer',
        'dataCubeStringParameter' => 'string'
    ];

    public function __construct(string $location, string $user, string $pass, string $dataCubeName, string $culture='en-US', bool $debug = false) {
        parent::__construct($location,
                            $user,
                            $pass,
                            $debug);
        $this->dataCubeName = $dataCubeName;
        $this->culture = $culture;
    }

    public function run(){
        $arguments = [
            'userName' => $this->user,
            'password' => $this->pass,
            'dataCubeName' => $this->dataCubeName,
            'culture' => $this->culture
        ];
        try{
            // $response comes back as a php stdClass with a public property called: RetrieveDataCubeOutputWithDefaultsAsExcelResult;
            $response = $this->soapClient->RetrieveDataCubeOutputAsDataSet($arguments);
            $data = $response->RetrieveDataCubeOutputWithDefaultsAsExcelResult;
            $fileName = md5($data) . '.xls';
            $absolutePathToFile = $this->destinationDir . DIRECTORY_SEPARATOR . $fileName;
            $bytesWritten = file_put_contents($absolutePathToFile, $data);
            if($bytesWritten === false){
                throw new Exception("Unable to write the spreadsheet to disk at: " . $absolutePathToFile);
            }
            return $absolutePathToFile;
        } catch(SoapFault $e) {
            throw SentrySoapFaultFactory::make($e);
        } catch(Exception $e) {
            throw $e;
        }
    }


    //$aParams[] = $this->__getDataCubeXmlParameter('as_of_date', $asOfDate, 'datetime');
    protected function __getDataCubeXmlParameter(string $name, string $value = '', string $dataType) {
        if ( empty($name) ):
            throw new \Exception("The name parameter was empty. You need to pass something to: __getDataCubeXmlParameter");
        endif;

        // Don't test for an empty value. We can accept an empty value. That can happen.
        // A dataType value is required.
        if (empty($dataType)):
            throw new \Exception("The dataType parameter was empty. You need to pass something to: __getDataCubeXmlParameter");
        endif;

        return array('name' => $name, 'value' => $value, 'datatype' => $dataType);
    }

    /**
     * A wrapper function to make the main code more readable. Returns true if the data
     * type passed in, is one that Sentry's Web Services will understand.
     * @param string $dataType
     * @return bool
     */
    protected function isValidDataType(string $dataType): bool{
        return in_array($dataType,$this->validDataTypes);
    }


    protected function __getDataCubeXmlParameters(string $dataCubeName, array $dataCubeParameters) {

        $sDataCubeParameters = '';
        foreach ($dataCubeParameters as $key => $aParameter):
            switch ($aParameter['datatype']):
                case 'boolean':
                    $wrapperName = 'dataCubeBooleanParameter';
                    break;

                case 'string':
                    $wrapperName = 'dataCubeStringParameter';
                    break;

                case 'datetime':
                    $wrapperName = 'dataCubeDateTimeParameter';
                    break;

                case 'decimal':
                    $wrapperName = 'dataCubeDecimalParameter';
                    break;

                default:
                    throw new \Exception("aParameter['datatype'] was " . $aParameter['datatype'] . " which isn't caught in the switch statement in __getDataCubeXmlParameters()");
                    break;
            endswitch;
            $sDataCubeParameters .= '<dataCubeParameter name="' . $aParameter['name'] . '"><' . $wrapperName . '>' . $aParameter['value'] . '</' . $wrapperName . '></dataCubeParameter>';
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
     * The xml that Sentry's Web Services wants requires a data type wrapper tag around each parameters.
     * Given a user specified data type, this method returns the appropriate wrapper name.
     * @param string $dataType
     * @return string
     * @throws Exception
     */
    protected function getWrapperNameFromDataType(string $dataType): string {
        $wrapperName = array_search($dataType, $this->validDataTypes);
        if( $wrapperName === false ){
            throw new Exception("The data type [" . $dataType . "] was not found in validDataTypes");
        }
        return $wrapperName;
    }


}