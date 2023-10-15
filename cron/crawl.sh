#!/bin/sh
# This script starts the crawl process

# Move to the current directory of this script
echo "==== Moving to the project directory ===="
cd "$(dirname "$0")"
cd ..
pwd

# Start the crawl
echo "==== Starting the crawl ===="
env -i /usr/bin/php82 -f ./bin/console app:crawl:themes
