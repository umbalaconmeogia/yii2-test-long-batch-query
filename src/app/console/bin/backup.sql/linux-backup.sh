#!/bin/sh

cd `dirname $0`

# Load config values
source ../db.config

DATE=`date "+%Y%m%d_%H%M%S"`

pg_dump -U $DB_USER --format=plain --clean $DB_NAME > ${DB_NAME}_${DATE}.sql

zip -r ${DB_NAME}_${DATE}.zip ${DB_NAME}_${DATE}.sql

rm -rf ${DB_NAME}_${DATE}.sql

# Contab
# 0 1 * * * /home/lab/app/202007.gov-bid/src/app/console/bin/backup.sql/linux-backup.sh
