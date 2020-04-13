<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use DPRMC\ClearStructure\Sentry\Services\RetrieveDataCubeOutputAsDataSet;


class RetrieveDataCubeOutputAsDataSetTest extends TestCase {


    /**
     * @test
     * @group datacube
     */
    public function invalidDataTypeShouldThrowException() {
        $this->expectException(\Exception::class);
        $invalidDataType = 'not_a_real_datatype';
        $parameters      = [];
        $parameters[]    = RetrieveDataCubeOutputAsDataSet::getDataCubeXmlParameter('as_of_date', getenv('SENTRY_AS_OF_DATE'), $invalidDataType);

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


    /**
     * @test
     * @group datacube
     */
    public function validResponseShouldContainRows() {
        //ini_set("default_socket_timeout", 6000);

        $parameters   = [];
        $parameters[] = RetrieveDataCubeOutputAsDataSet::getDataCubeXmlParameter('as_of_date', getenv('SENTRY_AS_OF_DATE'), 'datetime');


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
        $this->assertIsArray($response[ 'data' ][ 'data_node_Coupons' ]);

        // The 3 parameters, should be (by default) your _User_Login_Name_, the _Culture_Date_Format_, and whatever parameter
        // that was defined above, in this case 'as_of_date'.
        $this->assertCount(3, $response[ 'data' ][ 'parameters' ]);
    }

}