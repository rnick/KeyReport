#!/bin/bash

curl -v -H "Content-Type: application/json; charset=utf-8" -d "@../data/beispiel-radar.json" -X POST http://192.168.219.100/report.php --output report-curl-server-radar.pdf 
