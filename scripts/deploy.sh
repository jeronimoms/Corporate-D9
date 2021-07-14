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
