#!/bin/bash

# usage
# cd to directory containing docker-compose.yml
# bin/rebuildContainers.sh [DB root password] [DB user password]
# EG:
# cd ~/src/php8/docker
# bin/rebuildContainers.sh 123 1234

clear; printf "\033[3J"
docker-compose down --remove-orphans --volumes
docker-compose build --no-cache
MARIADB_ROOT_PASSWORD=$1 MARIADB_PASSWORD=$2 docker-compose up --force-recreate --detach
