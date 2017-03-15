# ClearStructure-Sentry-WebServices
A php library that enables Sentry users to access the Web Services API.

## Getting started
This is a private repository, so [special steps are needed](https://getcomposer.org/doc/05-repositories.md#using-private-repositories) if you want to use [Composer](https://getcomposer.org) to include this library in your php project.

## Clear Structure
Clear Structure is a financial technology company that created a portfolio management platform called Sentry.

[Clear Structure Company Website](https://clearstructure.com/)

## Ignored Services
From Clear Structure:
> All Sentry's API are all good except (RetrieveReconciliationData & ExportAccountInRange). These are outdated and they might not retrieve valid data.
### ExportAccount
I asked Clear Structure about this. They say the code works, but throws an out of memory exception every time because of the size of the result set. For that reason, I don't implement this service in my library.

### RetrieveReconciliationData 
I wrote the code for this before I got notice that this service was not supported. I'm not going to delete this class in case they enable it in the future. That being said, don't use it. There are other tools available to get reconciliation data.

## Examples

### RetrieveDataCubeOutputAsDataSet
> The following code block makes use of the dummy values for location, user, pass, dataCubeName, as well as the params we pass in the request.
 
 > Your location URL should look similar. The only difference will be the sub-domain. Log into your Sentry Web Interface like you normally do. Your sub-domain will show in the URL bar of your browser.
 
 This code example expects to be returned an array of SimpleXMLElement objects. And each of those objects has a property called **account_number**. You can see the foreach loop towards the bottom that simply echos each account number to a new line. 

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
        echo("\n" . $row->account_number);
    }
} catch(Exception $e) {
    $this->error($e->getMessage() . " " . $e->getFile() . ':' . $e->getLine());
}
```

### RetrieveDataCubeOutputWithDefaultsAsDataSet
This service is almost exactly the same as RetrieveDataCubeOutputAsDataSet. The difference here is that you don't pass in any parameters. The data cube will execute with whatever default values you have set via the Sentry Web Interface.
```php
$location = 'https://sentry1234.clearstructure.com/WebServices/DataReporterService.asmx';
$user = 'jdoe';
$pass = '12345'
$debug = true;
$dataCubeName = 'my_portfolios_data_cube';
$cultureString = 'en-US';

try{
    $service = new RetrieveDataCubeOutputWithDefaultsAsDataSet(
        $location,
        $user,
        $pass,
        $dataCubeName,
        $cultureString,
        $debug);
    $result = $service->run();

    $schema = $result['schema'];
    $rows = $result['rows'];

    foreach($rows as $row){
        echo("\n" . $row->account_number);
    }
} catch(Exception $e) {
    $this->error($e->getMessage() . " " . $e->getFile() . ':' . $e->getLine());
}
```