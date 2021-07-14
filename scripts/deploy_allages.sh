#!/bin/sh

# Update Allages site.
echo 'Clearing caches for Allages site'
drush cr -l allages -y

echo 'Runing updb for Allages site'
drush updb -l allages -y

echo 'Clearing caches for Allages site'
drush cr -l allages -y

echo 'Importing configuration for Allages site'
drush cim -l allages -y

echo 'Clearing caches for Allages site'
drush cr -l allages -y
