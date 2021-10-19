<?php

require_once 'EvalReport.php';

// get JSON from POST data
$chartData = file_get_contents('php://input');
$chartData = utf8_encode($chartData);

// decode JSON and log if we have a corrupt JSON file
$JSONData = json_decode($chartData);

$haveError = json_last_error_msg();
if ($haveError != 'No error') {
    // test for configured tremplate URL
    error_log('JSON Error: ' . $haveError);
    $tplURL = 'http://127.0.0.1/erep-tpl/error/tpl.html';
} else {
    $tplURL = $JSONData->template_url ?? '';
}


#error_log("loading template $tplURL");

// test for configured browser timeout
$browser_timeout = $JSONData->browser_timeout ?? 30000;
$sleep_pdf = $JSONData->sleep_pdf ?? 3;

$page_orientation = $JSONData->page_orientation ?? 'landscape';


// create instance of Report
$report = new EvalReport($tplURL, $chartData, $browser_timeout, $sleep_pdf, $page_orientation);

// create pdf file
// PDFfile contains the ablsolute path to the pdf
$PDFfile = $report->createPDF();

// read filename of report
$report_name = basename($PDFfile);

// send download header
$mime_type = 'application/pdf';

header("Content-Disposition: attachment; filename=$report_name;");
header("Content-Type: $mime_type");
header('Content-Length: ' . filesize($PDFfile));

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST');

header("Access-Control-Allow-Headers: X-Requested-With");

// read and send pdf content
readfile($PDFfile);

// delete file
unlink($PDFfile);
