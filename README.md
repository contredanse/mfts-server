# contredanse/mfts-server

Backend server for materforthespine application. 

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

