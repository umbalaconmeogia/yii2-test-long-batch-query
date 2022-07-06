#!/bin/bash

# Assign the variables.
USER='govbid';
HOST='183.91.21.41';
LOCAL_PATH='/home/lab/app/202007.gov-bid/src/app/console/bin/backup.sql/';
REMOTE_PATH='/home/govbid/govbid/';

# Get the most recent `.zip` file and assign it to `RECENT`.
RECENT=$(ls -lrt ${LOCAL_PATH} | awk '/.zip/ { f=$NF }; END { print f }');

# Run the actual SCP command.
scp -C -i /home/lab/backup-srv-govbid_private_key -P 2222 ${LOCAL_PATH}${RECENT} ${USER}@${HOST}:${REMOTE_PATH}${RECENT};
