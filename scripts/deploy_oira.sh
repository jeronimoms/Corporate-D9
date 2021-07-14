#!/bin/sh

# Update Oira site.
echo 'Clearing caches for Oira site'
drush cr -l oira -y

echo 'Runing updb for Oira site'
drush updb -l oira -y

echo 'Clearing caches for Oira site'
drush cr -l oira -y

echo 'Importing configuration for Oira site'
drush cim -l oira -y

echo 'Clearing caches for Oira site'
drush cr -l oira -y
