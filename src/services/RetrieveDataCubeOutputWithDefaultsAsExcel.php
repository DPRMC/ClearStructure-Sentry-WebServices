<?php
namespace DPRMC\ClearStructure\Sentry\Services;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\SentrySoapFaultFactory;
use Exception;
use SoapFault;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\ErrorFetchingHeadersException;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\AccountNotFoundException;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\DataCubeNotFoundException;


/**
 * Class RetrieveDataCubeOutputWithDefaultsAsExcel
 * @package DPRMC\ClearStructure\Sentry\Services
 */
class RetrieveDataCubeOutputWithDefaultsAsExcel extends Service{

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
     * @var string  The path to a directory where you want the resulting Excel spreadsheet saved.
     */
    protected $destinationDir;

    /**
     * RetrieveDataCubeOutputWithDefaultsAsExcel constructor.
     * @param string $location  The URL of the Sentry web services API. It's where we will send this request. This url is different for every Sentry customer.
     * @param string $user  A valid/active Sentry user account.
     * @param string $pass  An encrypted version of your Sentry password. Use their Data Protector page to properly encrypt the password.
     * @param string $dataCubeName  The name of your data cube that you created in Sentry.
     * @param string $culture   American? Use en-US.
     * @param string $destination   An absolute or relative path to the destination directory. The php function realpath() is used to turn it into an absolute path.
     * @param bool $debug   I don't think we need this anymore.
     */
    public function __construct(string $location, string $user, string $pass, string $dataCubeName, string $culture='en-US', string $destination='', bool $debug = false) {
        parent::__construct($location,
                            $user,
                            $pass,
                            $debug);
        $this->dataCubeName = $dataCubeName;
        $this->culture = $culture;
        $this->destinationDir = $this->setAbsolutePathToDestinationDirectory($destination);
    }

    /**
     * @return mixed
     * @throws AccountNotFoundException
     * @throws DataCubeNotFoundException
     * @throws ErrorFetchingHeadersException
     * @throws Exception
     * @throws SoapFault
     */
    public function run() {
        $parameters = [
            'userName' => $this->user,
            'password' => $this->pass,
            'dataCubeName' => $this->dataCubeName,
            'culture' => $this->culture
        ];
        try{
            // $response comes back as a php stdClass with a public property called: RetrieveDataCubeOutputWithDefaultsAsExcelResult;
            $response = $this->soapClient->RetrieveDataCubeOutputWithDefaultsAsExcel($parameters);
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


    /**
     * @param string $destinationDirectory
     * @return string   The absolute path to the newly downloaded spreadsheet.
     * @throws Exception
     */
    protected function setAbsolutePathToDestinationDirectory(string $destinationDirectory): string{
        $path = realpath($destinationDirectory);
        if( $path === false ){
            throw new Exception("Unable to find the destination dir at: " . $path);
        }
        return $path;
    }
}