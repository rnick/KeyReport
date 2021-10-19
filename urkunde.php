/**
 * 
 */
<?php

require_once 'KeyReport.php';

# get query parameter
$get = $_SERVER['QUERY_STRING'];

# set template url
$tplURL = "http://www.itrn.de/demo/report/tpl/urkunde/tpl.php";

# forward parameter to template
$tplURL = $tplURL . "?" . $get;

# test for configured browser timeouts
$browser_timeout = $JSONData->browser_timeout ?? 30000;
$sleep_pdf = $JSONData->sleep_pdf ?? 3;

# set orientation
$page_orientation = 'portrait';

// create instance of Report
$report = new KeyReport($tplURL, "", $browser_timeout, $sleep_pdf, $page_orientation);

// create pdf file
// $PDFfile contains the absolute path to the pdf
$PDFfile = $report->createPDF();

// read filename of report
$report_name = basename($PDFfile);

// send download header
$mime_type = 'application/pdf';

header("Content-Disposition: attachment; filename=$report_name;");
header("Content-Type: $mime_type");
header('Content-Length: ' . filesize($PDFfile));

// read and send pdf content
readfile($PDFfile);

// delete file
unlink($PDFfile);
