#!/bin/bash

echo "=== DEPLOYING ==="

echo "Backend installations..."
composer install
php bin/console doctrine:schema:update --force

echo "Frontend installations..."
npm ci # only updates npm packages - does not update any

echo "Building frontend..."
npm run build