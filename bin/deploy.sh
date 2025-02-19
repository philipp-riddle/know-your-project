#!/bin/bash

echo "=== DEPLOYING ==="

echo "Backend installations..."
composer install
php bin/console doctrine:schema:update --force

echo "Frontend installations..."
nodenv shell 23 # this is the version of node that we are using; otherwise the build will fail
npm ci # only updates npm packages - does not update any

echo "Building frontend..."
npm run build