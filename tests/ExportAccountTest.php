<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use DPRMC\ClearStructure\Sentry\Services\ExportAccount;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\AccountNotFoundException;

class ExportAccountTest extends TestCase {

    /**
     * @test
     * @group account
     */
    public function validResponseShouldContainAny() {
        ini_set("default_socket_timeout", 6000);
        $service  = new ExportAccount(
            getenv('SENTRY_UAT_LOCATION'),
            getenv('SENTRY_USER'),
            getenv('SENTRY_PASS'), TRUE,
            getenv('SENTRY_ACCOUNT'));
        $response = $service->run();

        $this->assertObjectHasAttribute('ExportAccountResult', $response);
        $this->assertObjectHasAttribute('any', $response->ExportAccountResult);
    }

    /**
     * @test
     * @group account
     */
    public function validResponseWithShortSocketTimeoutShouldThrowException() {
        ini_set("default_socket_timeout", 1);
        $this->expectException(\SoapFault::class);
        $service  = new ExportAccount(
            getenv('SENTRY_UAT_LOCATION'),
            getenv('SENTRY_USER'),
            getenv('SENTRY_PASS'), TRUE,
            getenv('SENTRY_ACCOUNT'));
        $response = $service->run();
    }

    /**
     * @test
     * @group account
     */
    public function invalidResponseShouldThrowException() {
        ini_set("default_socket_timeout", 6000);
        $this->expectException(AccountNotFoundException::class);
        $service = new ExportAccount(
            getenv('SENTRY_UAT_LOCATION'),
            getenv('SENTRY_USER'),
            getenv('SENTRY_PASS'), TRUE,
            'This Account Does Not Exist');
        $service->run();
    }

    /**
     * @test
     * @group account
     */
    public function invalidLocationShouldThrowSoapFault() {
        ini_set("default_socket_timeout", 6000);
        $this->expectException(\SoapFault::class);
        $service = new ExportAccount(
            'this location does not exist',
            'user',
            'pass', TRUE,
            'account');
        $service->run();
    }


}