@ECHO OFF
for /f "delims= " %%x in (..\db.config) do (set "%%x")

psql -U %DB_USER% -d %DB_NAME% -f %DB_NAME%.sql