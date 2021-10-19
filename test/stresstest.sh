#!/bin/bash

[ -d ../out/ ] || mkdir ../out

curl -v -H "Content-Type: application/json" -d "@../data/beispiel-tiqcon.json" -X POST http://10.238.38.71/erep/report.php --output ../out/report-curl-init.pdf  && echo "first report created" &

exit 0

for reportno in {0001..0500}; do
    # start max 10 threads
    while [ `jobs | wc -l` -ge 50 ]
    do
        sleep 1
    done

    curl -s -H "Content-Type: application/json" -d "@../data/beispiel-tiqcon.json" -X POST http://10.238.38.71/erep/report.php --output ../out/report-curl-$reportno.pdf && echo "Report $reportno created" &

done 

while [ 1 ]; do fg 2> /dev/null; [ $? == 1 ] && break; done
