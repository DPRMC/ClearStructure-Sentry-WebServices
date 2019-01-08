<?php

namespace DPRMC\ClearStructure\Sentry\Services;

use SoapClient;


/**
 * Class Service
 * @package DPRMC\ClearStructure\Sentry
 */
abstract class Service {
    /**
     * @var string  The ClearStructure url that you will send requests to.
     *              Typically of the format: https://<sentrysite>/WebServices/DataReporterService.asmx
     *              If you want to send requests to your UAT site, then pass that URL here.
     */
    protected $location;

    /**
     * @var string  A username of an active Sentry account.
     */
    protected $user;

    /**
     * @var string  The encrypted password for the aforementioned username.
     *              Use the tool provided by ClearStructure to properly encrypt your password.
     *              https://<sentrysite>.clearstructure.com/DataProtectorPage.aspx
     *              You paste your plain text password into the (invisible, in my IE)
     *              text box above the "Encrypt Text" submit button.
     */
    protected $pass;

    /**
     * @var string  This is the first param required for php's SoapClient object.
     */
    protected $wsdl;

    /**
     * @var string  The target namespace of the SOAP service. Not needed if you are in WSDL mode.
     */
    protected $uri;


    /**
     * @var string  An option in the php SoapClient constructor.
     *              The soap_version option should be one of either SOAP_1_1 or SOAP_1_2
     *              to select SOAP 1.1 or 1.2, respectively. If omitted, 1.1 is used.
     */
    protected $soapVersion;

    /**
     * @var bool    This option enables tracing of request so faults can be backtraced. This defaults to FALSE
     */
    protected $trace;

    /**
     * @var string  The user agent string sent to ClearStructure on each request.
     */
    protected $userAgent = 'DPRMC Web Services';

    /**
     * @var SoapClient  The SoapClient object we use to send requests to ClearStructure.
     */
    protected $soapClient;

    protected $sentryTimeZone = 'US/Central';

    /**
     * Service constructor.
     * @param string $location
     * @param string $user
     * @param string $pass
     * @param boolean $debug
     */
    public function __construct( string $location, string $user, string $pass, $debug = FALSE ) {
        $this->location = $location;
        $this->user     = $user;
        $this->pass     = $pass;
        $this->trace    = $debug ? TRUE : FALSE;

        $this->wsdl = $this->location . '?WSDL';
        $this->uri  = 'gibberish'; // Not needed if operating in WSDL mode.

        $this->soapVersion = 'SOAP_1_2';

        $this->instantiateSoapClient();
    }

    /**
     * Create a new instance of php's SoapClient.
     */
    protected function instantiateSoapClient() {
        $this->soapClient = new SoapClient( $this->wsdl, $this->getSoapClientOptions() );
    }

    /**
     * Returns an array of options needed by php's SoapClient.
     * @return array    An array of options. If working in WSDL mode, this parameter is optional.
     *                  If working in non-WSDL mode, the location and uri options must be set, where
     *                  location is the URL of the SOAP server to send the request to, and uri is the
     *                  target namespace of the SOAP service.
     */
    protected function getSoapClientOptions(): array {
        return [
            'location'           => $this->location,
            'uri'                => $this->uri,
            'trace'              => $this->trace,
            'user_agent'         => $this->userAgent,
            'exceptions'         => TRUE,
            'connection_timeout' => 60,
        ];
    }


    public function getLastRequestHeaders() {
        return $this->soapClient->__getLastRequestHeaders();
    }

    public function getLastRequest() {
        return $this->soapClient->__getLastRequest();
    }

    public function getLastResponse() {
        return $this->soapClient->__getLastResponse();
    }
}