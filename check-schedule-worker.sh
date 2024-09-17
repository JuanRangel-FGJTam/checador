#!/bin/bash

# Get the current date and time
current_date=$(date +"%Y-%m-%d %H:%M:%S")

# Get the modification time of the file (worker in this case)
file_mod_date=$(stat -c %y /var/www/html/storage/logs/worker | cut -d'.' -f1)

# Compare dates
if [[ "$current_date" > "$file_mod_date" ]]; then
    exit 1
else
    exit 0
fi