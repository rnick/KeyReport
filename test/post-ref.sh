#!/bin/bash

curl -v -H "Content-Type: application/json" -d "@../data/beispiel-ref.json" -X POST http://127.0.0.1/erep/report.php --output report-curl-ref.pdf 
