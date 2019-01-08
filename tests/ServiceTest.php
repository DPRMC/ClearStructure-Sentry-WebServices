<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use DPRMC\ClearStructure\Sentry\Services\RetrieveDataCubeOutputAsDataSet;


class ServiceTest extends TestCase {


    /**
     * @test
     * @group service
     */
    public function validResponseShouldContainRows() {
        ini_set( "default_socket_timeout", 6000 );
        $parameters   = [];
        $parameters[] = RetrieveDataCubeOutputAsDataSet::getDataCubeXmlParameter( 'security_id', getenv( 'SENTRY_SECURITY_ID' ), 'integer' );

        $service = new RetrieveDataCubeOutputAsDataSet(
            getenv( 'SENTRY_UAT_LOCATION' ),
            getenv( 'SENTRY_USER' ),
            getenv( 'SENTRY_PASS' ),
            getenv( 'SENTRY_DATA_CUBE_NAME' ),
            'en-US',
            $parameters,
            TRUE );

        $response = $service->pull( [ 'data_node' ] );


        $lastRequestHeaders = $service->getLastRequestHeaders();

        //print_r( $lastRequestHeaders );

        $lastRequest = $service->getLastRequest();

        //print_r( $lastRequest );

        $lastResponse = $service->getLastResponse();

        //print_r( $lastResponse );

        $this->assertNotEmpty( $lastRequestHeaders );
        $this->assertNotEmpty( $lastRequest );
        $this->assertNotEmpty( $lastResponse );
    }


}