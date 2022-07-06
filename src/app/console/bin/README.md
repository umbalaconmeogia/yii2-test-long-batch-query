
1. Copy and rename file db-example.conf to db.conf.
2. You should set value for ***DB_USER***, ***DB_NAME*** variablies to postgres database. If you implemented your environment by docker, then you should set postgres container name for ***DB_CONTAINER*** variable.

   ```
   DB_CONTAINER=pg_yii2_skeleton
   DB_USER=yii2_skeleton
   DB_NAME=yii2_skeleton
   ```
