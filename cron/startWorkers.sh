#!/bin/sh
# A script that stops all workers and restarts them
# This bash script is called by a cron job every 5 minutes

# Move to the current directory of this script
echo "==== Moving to the project directory ===="
cd "$(dirname "$0")"
cd ..
pwd

# Stop all running workers
echo "==== Stopping workers ===="
env -i /usr/bin/php82 -f ./bin/console messenger:stop-workers

# Wait for 5 seconds
echo "==== Waiting for 5 seconds ===="
sleep 5

# Start the workers
echo "==== Starting workers ===="
env -i /usr/bin/php82 -f ./bin/console messenger:consume async -vv --time-limit=295 --memory-limit=256M
