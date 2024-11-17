#!/bin/bash

# usage
# cd to directory containing docker-compose.yml
# bin/rebuildContainers.sh
# EG:
# cd ~/src/php8/docker
# bin/rebuildContainers.sh

clear; printf "\033[3J"
docker compose down --remove-orphans
docker compose build
docker compose up --detach
