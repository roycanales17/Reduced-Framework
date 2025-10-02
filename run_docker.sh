#!/bin/bash

# Check if Docker is running
if ! docker info >/dev/null 2>&1; then
  echo "❌ please start the docker first"
  exit 1
fi

# If Docker is running, bring up the stack
docker compose up -d --build
