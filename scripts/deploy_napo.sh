#!/bin/sh

# Update Napo site.
echo 'Clearing caches for Napo site'
drush cr -l napo -y

echo 'Runing updb for Napo site'
drush updb -l napo -y

echo 'Clearing caches for Napo site'
drush cr -l napo -y

echo 'Importing configuration for Napo site'
drush cim -l napo -y

echo 'Clearing caches for Napo site'
drush cr -l napo -y
