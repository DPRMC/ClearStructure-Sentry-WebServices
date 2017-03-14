# ClearStructure-Sentry-WebServices
A php library that enables Sentry users to access the Web Services API.

## Getting started
This is a private repository, so [special steps are needed](https://getcomposer.org/doc/05-repositories.md#using-private-repositories) if you want to use [Composer](https://getcomposer.org) to include this library in your php project.

## Clear Structure
Clear Structure is a financial technology company that created a portfolio management platform called Sentry.

[Clear Structure Company Website](https://clearstructure.com/)

## Examples

### RetrieveDataCubeOutputAsDataSet
> The following code block makes use of the dummy values for location, user, pass, dataCubeName, as well as the params we pass in the request.
 
 > Your location URL should look similar. The only difference will be the sub-domain. Log into your Sentry Web Interface like you normally do. Your sub-domain will show in the URL bar of your browser.

```php
$location = 'https://sentry1234.clearstructure.com/WebServices/DataReporterService.asmx';
$user = 'jdoe';
$pass = '12345'
$debug = true;
$dataCubeName = 'my_portfolios_data_cube';
$cultureString = 'en-US';

$params = [];
$params[] = RetrieveDataCubeOutputAsDataSet::getDataCubeXmlParameter('start_date','1/1/2017','datetime');
$params[] = RetrieveDataCubeOutputAsDataSet::getDataCubeXmlParameter('as_of_date','1/31/2017','datetime');

try{
    $service = new RetrieveDataCubeOutputAsDataSet(
        $location,
        $user,
        $pass,
        $dataCubeName,
        $cultureString,
        $params,
        $debug);
    $result = $service->run();

    $schema = $result['schema'];
    $rows = $result['rows'];

    foreach($rows as $row){
        $this->line($row->account_number);
    }
} catch(Exception $e) {
    $this->error($e->getMessage() . " " . $e->getFile() . ':' . $e->getLine());
}
```
