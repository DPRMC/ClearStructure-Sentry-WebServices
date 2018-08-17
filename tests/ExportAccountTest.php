<?php

use PHPUnit\Framework\TestCase;
use DPRMC\ClearStructure\Sentry\Services\ExportAccount;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\AccountNotFoundException;
use Dotenv\Dotenv;

class ExportAccountTest extends TestCase {

    public function __construct(?string $name = NULL, array $data = [], string $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $dotenv = new Dotenv(__DIR__);
        $dotenv->load();
    }

    public function testValidExportRequest() {
        $service  = new ExportAccount(getenv('UAT_LOCATION'), getenv('USER'), getenv('PASS'), TRUE, getenv('ACCOUNT'));
        $response = $service->run();
        print_r($response);
        $this->assertEquals($response, TRUE);
    }

    public function testExportAccountThatDoesNotExist() {
        require( '.env.php' );
        $this->expectException(AccountNotFoundException::class);
        $accountNumber = "This account does not exist";
        $service       = new ExportAccount(getenv('UAT_LOCATION'), getenv('USER'), getenv('PASS'), TRUE, $accountNumber);
        $service->run();
    }


}