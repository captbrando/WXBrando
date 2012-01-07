#!/bin/sh

cd /YOUR/LOCAL/PATH/WHERE/THIS/FILE/SITS
./buildstuff-cli.php
/usr/bin/scp -rq /var/www/weather/*.png USER@YOURWEBHOST:/PATH/TO/WEATHER
/usr/bin/scp -rq /var/www/weather/*.php USER@YOURWEBHOST:/PATH/TO/WEATHER
