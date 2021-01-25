#!/bin/sh
# Change the branch to testing.
git checkout testing

# Download last changes.
git pull

# Init submodules.
git submodule init

# Update submodules wth last changes.
git submodule update --recursive

# Update the Drupal installation.
composer install

# Update the databases.
drush updb -l default -y
drush updb -l allages -y
drush updb -l oira -y
drush updb -l vesafe -y
drush updb -l napo -y

# Import the configuration.
drush cim -l default -y
drush cim -l allages -y
drush cim -l oira -y
drush cim -l vesafe -y
drush cim -l napo -y

# Clear the cache.
drush cr -l default -y
drush cr -l allages -y
drush cr -l oira -y
drush cr -l vesafe -y
drush cr -l napo -y
