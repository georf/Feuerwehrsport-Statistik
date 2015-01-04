#!/bin/bash
sleep $((RANDOM % $1))m
sleep $((RANDOM % $2))
/var/www/sites/de/feuerwehrsport-statistik/www/tools/facebook.php --$3