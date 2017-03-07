<?php
use PHPUnit\Framework\TestCase;
use DPRMC\ClearStructure\Sentry\Services\ExportAccount;
use DPRMC\ClearStructure\Sentry\Services\AccountNotFoundException;

class ExportAccountTest extends TestCase {

    public function testValidExportRequest() {
        require('.env.php');

        try{
            $service = new ExportAccount($location,$user,$pass,true,'SBF');
            $response = $service->run();
            print_r($response);
            $this->assertEquals($response, true);
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

    public function testExportAccountThatDoesNotExist(){
        require('.env.php');
        $this->expectException(AccountNotFoundException::class);
        $accountNumber = "This account does not exist";
        $service = new ExportAccount($location,$user,$pass,true,$accountNumber);
        $service->run();
    }


}