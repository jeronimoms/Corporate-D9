#!/bin/sh

# Insert the tag name.
echo 'Enter the tag name:'
read tag_name

# Change to the tag name.
git checkout $tag_name

# Download last changes.
git pull origin $tag_name

# Init submodules.
git submodule init

# Update submodules wth last changes.
git submodule update --recursive

# Update the Drupal installation.
composer install

# Update Vesafe site.
echo 'Runing updb for Vesafe site'
drush updb -l vesafe -y

echo 'Clearing caches for Vesafe site'
drush cr -l vesafe -y

echo 'Importing configuration for Vesafe site'
drush cim -l vesafe -y

echo 'Clearing caches for Vesafe site'
drush cr -l vesafe -y
