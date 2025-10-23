Exporter : pg_dump -U postgres bipdb > bipdb.sql dans bip_api_db

Importer : psql -h 127.0.0.1 -U postgres -d bip_api_db -f bipdb.sql
