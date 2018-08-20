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

        //string $location, string $user, string $pass, string $dataCubeName, string $culture = 'en-US', array $parameters = [], bool $debug = FALSE
        $parameters   = [];
        $parameters[] = RetrieveDataCubeOutputAsDataSet::getDataCubeXmlParameter('security_id', getenv('SECURITY_ID'), 'integer');

        $service = new RetrieveDataCubeOutputAsDataSet(
            getenv('UAT_LOCATION'),
            getenv('USER'),
            getenv('PASS'),
            getenv('DATA_CUBE_NAME'),
            'en-US',
            $parameters,
            TRUE);

        $response = $service->run();

        $this->assertTrue(!empty($response->rows));
    }


}