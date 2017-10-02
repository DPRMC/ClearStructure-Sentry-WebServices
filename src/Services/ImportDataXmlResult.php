<?php
namespace DPRMC\ClearStructure\Sentry\Services;

class ImportDataXmlResult{
    protected $result = [];
    public function __construct(array $result) {
        $this->result = $result;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool{
        foreach($this->result as $i => $table){
            if(! empty($table['errors']) ){
                return true;
            }
        }
        return false;
    }

    /**
     * @return int
     */
    public function totalImported(): int{
        $totalImported = 0;
        foreach($this->result as $i => $table){
            $totalImported += $table['rows_imported'];
        }
        return $totalImported;
    }

    /**
     * @return float
     */
    public function totalRuntime(): float{
        $totalRuntime = 0;
        foreach($this->result as $i => $table){
            $totalRuntime += $table['run_time'];
        }
        return $totalRuntime;
    }
}