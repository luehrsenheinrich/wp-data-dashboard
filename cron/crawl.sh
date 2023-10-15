#!/bin/sh
# This script starts the crawl process

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

# Start the crawl
echo "==== Starting the crawl ===="
symfony console app:crawl:themes
