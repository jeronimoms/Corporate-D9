#!/bin/sh

# Update Vesafe site.
echo 'Clearing caches for Vesafe site'
drush cr -l vesafe -y

echo 'Runing updb for Vesafe site'
drush updb -l vesafe -y

echo 'Clearing caches for Vesafe site'
drush cr -l vesafe -y

echo 'Importing configuration for Vesafe site'
drush cim -l vesafe -y

echo 'Clearing caches for Vesafe site'
drush cr -l vesafe -y
