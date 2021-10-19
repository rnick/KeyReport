<?php

$url = "http://localhost/intern/erep/report.php";

$JSONFile = "../data/beispiel-tiqcon.json";
$payload = file_get_contents($JSONFile);

$ch = curl_init($url);
# Setup request to send json via POST.

curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
# Return response instead of printing.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
# Send request.
$result = curl_exec($ch);
curl_close($ch);
# Print response.

$mime_type = "application/pdf";

header("Content-Disposition: attachment; filename=report-web.pdf;");
header("Content-Type: $mime_type");
//header('Content-Length: ' . filesize($PDFfile));

print($result);
