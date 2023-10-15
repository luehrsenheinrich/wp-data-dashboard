#!/bin/sh
# A script that stops all workers and restarts them
# This bash script is called by a cron job every 5 minutes

# Move to the current directory of this script
echo "==== Moving to the project directory ===="
cd "$(dirname "$0")"
cd ..
pwd

# Check if the `symfony` command exists
echo "==== Checking if the symfony command exists ===="
if ! [ -x "$(command -v symfony)" ]; then
	echo "====Error: symfony is not installed." >&2
	exit 1
else
	echo "==== Symfony command exists ===="
fi

# Stop all running workers
echo "==== Stopping workers ===="
symfony console messenger:stop-workers

# Wait for 5 seconds
echo "==== Waiting for 5 seconds ===="
sleep 5

# Start the workers
echo "==== Starting workers ===="
symfony console messenger:consume async -vv --time-limit=295 --memory-limit=256M
