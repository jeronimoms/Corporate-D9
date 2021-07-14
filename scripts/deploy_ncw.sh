#!/bin/sh

# Update NCW site.
echo 'Clearing caches for NCW site'
drush cr -l default -y

echo 'Runing updb for NCW site'
drush updb -l default -y

echo 'Clearing caches for NCW site'
drush cr -l default -y

echo 'Importing configuration for NCW site'
drush cim -l default -y

echo 'Clearing caches for NCW site'
drush cr -l default -y
