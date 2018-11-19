# contredanse/mfts-server

Backend server for materforthespine application. 

## Installation

### env file

```
cp .env.example .env
```

## Conversions

### Convert paxton.xml menu

```bash
$ php ./bin/console.php convert:menu /web/www/mfts/src/data/xml/paxton.xml
```

### Generate video covers

```bash
$ php ./bin/console.php make:covers /web/www/mfts/src/data/xml/paxton.xml
```

### Convert videos (webm/mp4)

```bash
$ php ./bin/console.php convert:videos /web/www/mfts/src/data/xml/paxton.xml

```

