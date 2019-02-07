<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use DPRMC\ClearStructure\Sentry\Services\RetrieveDataCubeOutputAsDataSet;


class RetrieveDataCubeOutputAsDataSetTest extends TestCase {


    /**
     * @test
     * @group datacube1
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

        $response = $service->pull([ 'data_node', 'data_node_Coupons', 'parameters' ]);

        $this->assertTrue(!empty($response[ 'data' ][ 'data_node' ]));
        $this->assertTrue(!empty($response[ 'data' ][ 'data_node_Coupons' ]));
        $this->assertCount(3, $response[ 'data' ][ 'parameters' ]);
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