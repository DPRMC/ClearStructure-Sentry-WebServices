<?php

use PHPUnit\Framework\TestCase;
use DPRMC\ClearStructure\Sentry\Services\ExportAccount;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\AccountNotFoundException;

class ExportAccountTest extends TestCase {

    public function testValidExportRequest() {
        $service  = new ExportAccount(
            getenv('UAT_LOCATION'),
            getenv('USER'),
            getenv('PASS'), TRUE,
            getenv('ACCOUNT'));
        $response = $service->run();
        print_r($response);
        $this->assertEquals($response, TRUE);
    }

    public function testExportAccountThatDoesNotExist() {
        $this->expectException(AccountNotFoundException::class);
        $accountNumber = "This account does not exist";
        $service       = new ExportAccount(
            getenv('UAT_LOCATION'),
            getenv('USER'),
            getenv('PASS'), TRUE, $accountNumber);
        $service->run();
    }


}