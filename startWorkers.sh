#!/bin/sh
# A script that stops all workers and restarts them
# This bash script is called by a cron job every 5 minutes

cd ~/symfony
symfony console messenger:stop-workers
symfony console messenger:consume async -vv --time-limit=295 --memory-limit=256M
