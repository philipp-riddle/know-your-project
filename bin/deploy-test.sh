#!/bin/bash

php bin/console doctrine:schema:update --force --env=test && php bin/console cache:clear --env=test