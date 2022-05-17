<?php

namespace DPRMC\ClearStructure\Sentry\Services;

use Exception;
use SoapFault;
use DPRMC\ClearStructure\Sentry\Services\Exceptions\SentrySoapFaultFactory;

/**
 * Class RunBatch
 *
 * @package DPRMC\ClearStructure\Sentry\Services
 *
 *
 */
class RunBatch extends Service {

    protected string $workflowName;
    protected bool   $turnOffRecurance; // They misspell recurrence
    protected bool   $reschedule;


    /**
     * RunBatch constructor.
     *
     * @param string $location Location will be similar to: https://<sentrysite>/WebServices/SentryWorkflowService.asmx
     * @param string $user     An active Sentry user name.
     * @param string $pass     The password for the aforementioned Sentry user name.
     * @param bool   $debug
     */
    /**
     * RunBatch constructor.
     *
     * @param string $location         Location will be similar to: https://<sentrysite>/WebServices/SentryWorkflowService.asmx
     * @param string $user             The name of a user that has access to Sentry, and has sufficient permissions to run the workflow and its underlying processes.
     * @param string $pass             The password for the specified user, in Sentry's encrypted format. For assistance encrypting a password contact ClearStructure support.
     * @param string $workflowName     The name of the workflow to run, exactly as it appears in the user interface.
     * @param bool   $turnOffRecurance Set to true if you want to turn off the workflow's ability to have its scheduled time recalculated to a future time. Probably if you are calling a workflow from a web service, it is not being run by Sentry's own scheduler, in which case it should not have a run time, and should already be configured to not recur.
     * @param bool   $reschedule       Whether to calculate a new future run time for the workflow. This can only happen if the workflow is configured to recur, which is probably not the case if you are running it from a web service rather than from Sentry's own scheduler. You may, however, have a workflow that is run on a schedule by Sentry's scheduler, which you also run ad-hoc via a web service, without affecting the Sentry scheduler's schedule. In such a case, the workflow will have recurrence settings. You would set turnOffRecurance to false, and reschedule to false.
     * @param bool   $debug
     */
    public function __construct( string $location,
                                 string $user,
                                 string $pass,
                                 string $workflowName,
                                 bool   $turnOffRecurance,
                                 bool   $reschedule,
                                 bool   $debug = FALSE ) {
        parent::__construct( $location,
                             $user,
                             $pass,
                             $debug );
        $this->workflowName     = $workflowName;
        $this->turnOffRecurance = $turnOffRecurance;
        $this->reschedule       = $reschedule;
    }

    /**
     * @return mixed
     * @throws Exception
     * @throws Exceptions\AccountNotFoundException
     * @throws Exceptions\DataCubeNotFoundException
     * @throws Exceptions\ErrorFetchingHeadersException
     * @throws SoapFault
     */
    public function run() {
        ini_set( 'memory_limit',
                 -1 );
        $arguments = [ 'userName'         => $this->user,
                       'password'         => $this->pass,
                       'workflowName'     => $this->workflowName,
                       'turnOffRecurance' => $this->turnOffRecurance, // They misspell Recurrence
                       'reschedule'       => $this->reschedule ];
        try {
            $response = $this->soapClient->RunBatch( $arguments );
            return $response;
        } catch ( SoapFault $e ) {
            throw SentrySoapFaultFactory::make( $e );
        } catch ( Exception $e ) {
            throw $e;
        }
    }

}