#!/bin/sh
# This script starts the crawl process

# Move to the project directory
echo "==== Moving to the project directory ===="
cd /kunden/336675_81549/projekte/wp-data-dashboard/symfony

# Check if the `symfony` command exists
echo "==== Checking if the symfony command exists ===="
if ! [ -x "$(command -v symfony)" ]; then
	echo 'Error: symfony is not installed.' >&2
	exit 1
fi

# Start the crawl
echo "==== Starting the crawl ===="
symfony console app:crawl:themes
