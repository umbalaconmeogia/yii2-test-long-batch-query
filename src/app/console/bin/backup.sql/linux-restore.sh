#!/bin/sh

# Load config values
source ../db.config

psql -U $DB_USER -d $DB_NAME -f ${DB_NAME}.sql