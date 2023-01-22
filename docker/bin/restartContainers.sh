#!/bin/bash

# usage
# cd to directory containing docker-compose.yml
# bin/restartContainers.sh [DB root password] [DB user password]
# use same passwords as when initially calling rebuildContainers.sh

# EG:
# cd ~/src/php8/docker
# bin/restartContainers.sh 123 1234

clear; printf "\033[3J"
docker-compose stop
docker-compose up --detach nginx
MARIADB_PASSWORD=$2 docker-compose up --detach php
MARIADB_ROOT_PASSWORD=$1 MARIADB_PASSWORD=$2 docker-compose up --detach mariadb
