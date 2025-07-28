#!/bin/bash

# usage
# cd to directory containing docker-compose.yml
# bin/restartContainers.sh

# EG:
# cd ~/src/php8/docker
# bin/restartContainers.sh

clear; printf "\033[3J"
docker compose stop
docker compose up --detach nginx
docker compose up --detach php
docker compose up --detach mariadb
