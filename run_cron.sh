#!/bin/sh

LOG_FILE="/var/log/cron/cron.log"
START_TIME=$(date '+%Y-%m-%d %H:%M:%S')

# Add header
{
  echo "==============================="
  echo "[$START_TIME]"
  echo ""
} >> "$LOG_FILE"

# Run artisan command with timing + memory stats
# Prepend \n in the time format so a blank line appears before Statistics
/usr/bin/time -f "\n\nStatistics:\n - memory usage: %M KB\n - time duration: %E" \
  /usr/local/bin/php /var/www/html/artisan cron:scheduler \
  >> "$LOG_FILE" 2>&1

# End timestamp
END_TIME=$(date '+%Y-%m-%d %H:%M:%S')
{
  echo ""
  echo "[$END_TIME]"
  echo ""
} >> "$LOG_FILE"
