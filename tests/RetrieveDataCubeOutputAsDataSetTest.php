<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use DPRMC\ClearStructure\Sentry\Services\RetrieveDataCubeOutputAsDataSet;


class RetrieveDataCubeOutputAsDataSetTest extends TestCase {


    /**
     * @test
     * @group datacube
     */
    public function validResponseShouldContainRows() {
        ini_set("default_socket_timeout", 6000);

        $parameters   = [];
        $parameters[] = RetrieveDataCubeOutputAsDataSet::getDataCubeXmlParameter('security_id', getenv('SENTRY_SECURITY_ID'), 'integer');

        $service = new RetrieveDataCubeOutputAsDataSet(
            getenv('SENTRY_UAT_LOCATION'),
            getenv('SENTRY_USER'),
            getenv('SENTRY_PASS'),
            getenv('SENTRY_DATA_CUBE_NAME'),
            'en-US',
            $parameters,
            TRUE);

        $response = $service->run();
//        print_r($response);
//        print_r($_SERVER);
//        $host = gethostname();
//        $ip   = gethostbyname($host);
//
//        var_dump($host);
//        var_dump($ip);
        $ipChicken = file_get_contents('http://ipchicken.com');
        var_dump($ipChicken);

        $this->assertTrue(!empty($response[ 'rows' ]));
    }

    /**
     * @test
     * @group datacube
     */
    public function invalidDataTypeShouldThrowException() {
        $this->expectException(\Exception::class);
        $invalidDataType = 'not_a_real_datatype';
        $parameters      = [];
        $parameters[]    = RetrieveDataCubeOutputAsDataSet::getDataCubeXmlParameter('security_id', getenv('SENTRY_SECURITY_ID'), $invalidDataType);

        $service = new RetrieveDataCubeOutputAsDataSet(
            getenv('SENTRY_UAT_LOCATION'),
            getenv('SENTRY_USER'),
            getenv('SENTRY_PASS'),
            getenv('SENTRY_DATA_CUBE_NAME'),
            'en-US',
            $parameters,
            TRUE);

        $service->run();
    }


}