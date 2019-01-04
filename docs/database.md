# Database 

## Create database

```bash
mysql -u root -p -e 'CREATE DATABASE mfts';
```

## Doctrine commands

### List entities

```bash
./vendor/bin/doctrine orm:info
```

### Create schema

```bash
./vendor/bin/doctrine orm:schema-tool:create --dump-sql
```

### Update schema

```bash
./vendor/bin/doctrine orm:schema-tool:update --dump-sql
```

