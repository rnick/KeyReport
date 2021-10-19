<?php

require_once 'EvalReport.php';
require_once 'classes/SingleReportDB.php';

$db = new DBReportLoader();

// get JSON from POST data
// $sessionId = file_get_contents('php://input');
// $sessionId = "testrecord";
$sessionId = key($_GET);

$reportData = $db->LoadReport($sessionId);

// decode JSON and log if we have a corrupt JSON file
$JSONData = json_decode($reportData->HeaderData);
$JSONData->chartData = $reportData->ChartJson;
$JSONData->tableData = $reportData->TableJson;
$JSONData->columns = $reportData->Columns;

$chartData = json_encode($JSONData, true);

$haveError = json_last_error_msg();
if ($haveError != 'No error') {
    error_log('JSON Error: ' . $haveError);
}

// test for configured tremplate URL
$tplURL = $JSONData->template_url ?? '';
//$tplURL = "http://127.0.0.1/erep-tpl/single/tpl.html";

// test for configured browser timeout
$browser_timeout = $JSONData->browser_timeout ?? 30000;
$sleep_pdf = $JSONData->sleep_pdf ?? 3;

// create instance of Report
$report = new EvalReport($tplURL, $chartData, $browser_timeout, $sleep_pdf, 'landscape');

// create pdf file
// PDFfile contains the ablsolute path to the pdf
$PDFfile = $report->createPDF();

// read filename of report
$report_name = basename($PDFfile) .  ".pdf";

// send download header
$mime_type = 'application/pdf';

header("Content-Disposition: attachment; filename=$report_name;");
header("Content-Type: $mime_type");
header('Content-Length: ' . filesize($PDFfile));

// read and send pdf content
readfile($PDFfile);

// delete file
unlink($PDFfile);
