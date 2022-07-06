# Backup and restore database

1. Goto `console/bin/backup.sql`, run `*-backup.sh`. This will dump database to a sql file.
  On Linux, run `linux-backup.sh`
  On docker, run `docker-backup.sh`
2. Copyt sql file to another environtment (you cannot commit it to git).
3. Goto `console/bin/backup.sql`, run `*-restore.sh` to update translation.
  On Linux, run `linux-restore.sh`
  On docker, run `docker-restore.sh`
