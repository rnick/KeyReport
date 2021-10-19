<?php

require_once 'conf/database.php';
require_once 'ReportObject.php';

class DBReportLoader
{
    var $db = null;

    public function LoadReport($sessionId)
    {
        if ($sessionId === null) {
            throw new Exception("SessionId not set");
        }
        $query = "select * from t_pdfcreation where session = '$sessionId'";

        $this->db  = ERS_Database::dbConnect();
        $data = ERS_Database::run_queryresult($this->db, $query);
        
        $result = new ReportObject();

        $result->SessionId = $sessionId;
        $result->ChartJson = $data->chartjson;
        $result->TableJson = $data->tablejson;
        $result->HeaderData = $data->headerjson;
        $result->Columns = $data->coljson;

        return $result;
    }
}
