<?php

require_once '../EvalReport.php';

$chartData = file_get_contents('../data/beispiel-radar.json');

$JSONData = json_decode($chartData);
$haveError = json_last_error_msg();
if ($haveError != 'No error') {
    printf('JSON Error: %s', $haveError);
}

// test for configured tremplate URL
$tplURL = $JSONData->template_url ?? '';

// test for configured browser timeout
$browser_timeout = $JSONData->browser_timeout ?? 30000;

// create instance of Report
$report = new EvalReport($tplURL, $chartData, $browser_timeout);

// create pdf file
// PDFfile contains the ablsolute path to the pdf
$PDFfile = $report->createPDF();

rename($PDFfile, './report-cli-radar.pdf');
