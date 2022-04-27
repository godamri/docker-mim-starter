#!/bin/bash

git clone https://github.com/godamri/docker-mim-starter.git ./
rm ./get.sh
rm -rf ./.git
cd ./appsrc && cp .environment.example .environment
cd ./..