
# Dev env

## Initial dev env

## Import DB into local env

### Create database

```mysql
CREATE USER test_long_batch_query IDENTIFIED BY 'test_long_batch_query';
GRANT ALL ON test_long_batch_query.* TO test_long_batch_query;
CREATE DATABASE test_long_batch_query DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
```

Drop database
```mysql
DROP DATABASE test_long_batch_query;
```
