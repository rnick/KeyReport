#!/bin/bash

curl -v -H "Content-Type: application/json" -d "@../data/beispiel-radar.json" -X POST http://127.0.0.1/intern/erep/report.php --output report-curl-radar.pdf 
