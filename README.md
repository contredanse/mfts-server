# contredanse/mfts-server

![PHP 7.2+](https://img.shields.io/badge/php-7.2+-ff69b4.svg)
[![Build Status](https://travis-ci.org/contredanse/mfts-server.svg?branch=master)](https://travis-ci.org/contredanse/mfts-server)
[![Coverage](https://codecov.io/gh/contredanse/mfts-server/branch/master/graph/badge.svg)](https://codecov.io/gh/contredanse/mfts-server)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/contredanse/mfts-server/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/contredanse/mfts-server/?branch=master)
![PHPStan](https://img.shields.io/badge/style-level%207-brightgreen.svg?style=flat-square&label=phpstan)


Backend server for materforthespine application.

![Material for the spine](./docs/images/material-for-the-spine.png)

## Installation

### env file

```
cp .env.example .env
```

## Deploy

> See [./deploy.php](./deploy.php) file.

```bash
$ ./vendor/bin/dep deploy production
```

It assumes you have a 'deployer' user set up (see [here](https://www.digitalocean.com/community/tutorials/automatically-deploy-laravel-applications-deployer-ubuntu#step-3-%E2%80%94-configuring-the-deployer-user))

### Create deploy user 

```bash
$ sudo adduser deployer
$ sudo usermod -aG www-data deployer
$ sudo chfn -o umask=022 deployer
```

```bash
$ sudo chown deployer:www-data /var/www/www.domain.org
$ sudo chmod g+s /var/www/html /var/www/www.domain.org
```

Create a key
 
```bash
$ su - deployer
$ ssh-keygen -t rsa -b 4096
$ cat ~/.ssh/id_rsa.pub
```

## Conversions

### Generate video covers

```bash
$ php ./bin/console.php make:covers /web/www/mfts/src/data/xml/paxton.xml
```

### Convert videos (webm/mp4)

```bash
$ php ./bin/console.php convert:videos /web/www/mfts/src/data/xml/paxton.xml

```

