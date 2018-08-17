<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use DPRMC\ClearStructure\Sentry\Services\RetrieveDataCubeOutputWithDefaultsAsExcel;
use org\bovigo\vfs\vfsStream;

/**
 * Class RetrieveDataCubeOutputWithDefaultsAsExcelTest
 * phpunit --filter RetrieveDataCubeOutputWithDefaultsAsExcelTest
 */
class RetrieveDataCubeOutputWithDefaultsAsExcelTest extends TestCase {
    public function testValidDataCubeRequest() {
        $downloadFilePath = vfsStream::url('downloads/excel.xls');
        $service          = new RetrieveDataCubeOutputWithDefaultsAsExcel(getenv('UAT_LOCATION'), getenv('USER'), getenv('PASS'), getenv('REPORT_NAME'));
        $response         = $service->run();
        file_put_contents($downloadFilePath, $response->RetrieveDataCubeOutputWithDefaultsAsExcelResult);
        $this->assertFileExists($downloadFilePath);
    }
}