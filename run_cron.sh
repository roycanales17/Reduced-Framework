#!/bin/sh
export PHP_IDE_CONFIG="serverName=cron_job"
export XDEBUG_CONFIG="idekey=PHPSTORM"

# Default log file for cron
LOG_FILE="/var/log/cron/cron.log"

# If running manually (stdout is a terminal), use a different log
if [ -t 1 ]; then
    LOG_FILE="./logs/cron/cron.log"
fi

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
