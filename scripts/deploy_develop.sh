#!/bin/sh

# Change the branch to develop.
git checkout develop

# Download last changes.
git pull

# Init submodules.
git submodule init

# Update submodules wth last changes.
git submodule update --recursive

# Update the Drupal installation.
composer install

# Update Osha site.
echo 'Runing updb for Osha site'
drush updb -l default -y

echo 'Clearing caches for Osha site'
drush cr -l default -y

echo 'Importing configuration for Osha site'
drush cim -l default -y

echo 'Clearing caches for Osha site'
drush cr -l default -y

# Update Oira site.
echo 'Runing updb for Oira site'
drush updb -l oira -y

echo 'Clearing caches for Oira site'
drush cr -l oira -y

echo 'Importing configuration for Oira site'
drush cim -l oira -y

echo 'Clearing caches for Oira site'
drush cr -l oira -y

# Update Vesafe site.
echo 'Runing updb for Vesafe site'
drush updb -l vesafe -y

echo 'Clearing caches for Vesafe site'
drush cr -l vesafe -y

echo 'Importing configuration for Vesafe site'
drush cim -l vesafe -y

echo 'Clearing caches for Vesafe site'
drush cr -l vesafe -y
