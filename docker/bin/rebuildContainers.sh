#!/bin/bash

# usage
# cd to directory containing docker-compose.yml
# bin/rebuildContainers.sh
# EG:
# cd ~/src/php8/docker
# bin/rebuildContainers.sh

clear; printf "\033[3J"
docker compose down --remove-orphans --volumes
docker compose build --no-cache
docker compose up --force-recreate --detach
