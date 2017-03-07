<?php
use PHPUnit\Framework\TestCase;
use DPRMC\ClearStructure\Sentry\Services\RetrieveDataCubeOutputWithDefaultsAsExcel;
use org\bovigo\vfs\vfsStream;
/**
 * Class RetrieveDataCubeOutputWithDefaultsAsExcelTest
 * phpunit --filter RetrieveDataCubeOutputWithDefaultsAsExcelTest
 */
class RetrieveDataCubeOutputWithDefaultsAsExcelTest extends TestCase {

    public function testValidDataCubeRequest() {
        require('.env.php');

        $downloadFilePath = vfsStream::url('downloads/excel.xls');

        try{
            $service = new RetrieveDataCubeOutputWithDefaultsAsExcel($location,$user,$pass,true,'Drennen - NAV');
            $response = $service->run();

            file_put_contents($downloadFilePath,$response->RetrieveDataCubeOutputWithDefaultsAsExcelResult);

            //var_dump($response);
            $this->assertFileExists($downloadFilePath);
        } catch (SoapFault $e){
            echo "\n\nSOAP FAULT\n\n" . get_class($e);
            echo "\n" . $e->getFile() . ':' . $e->getLine();

            echo "\n\n\n" . $e->getMessage() . "\n\n\n";
            echo "\n" . $e->getTraceAsString();

            echo "\n\nREQUEST:\n" . $service->getLastRequest() . "\n\n\n";

            echo "\n\nRESPONSE\n" . $service->getLastResponse() . "\n\n\n";

            echo "RESPONSE HEADERS:\n" . $service->getLastRequestHeaders() . "\n\n\n";



        } catch(Exception $e) {
            echo "\n\n\n\n" . get_class($e);
            echo "\n\n\n" . $e->getMessage() . "\n\n\n";
        }


    }


}