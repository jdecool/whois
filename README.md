whois
=====

This is a toy project that implement a [whois](https://en.wikipedia.org/wiki/WHOIS) client in PHP.

## Usage

First you need to install dependencies with Composer:

```bash
$ composer install
```

Then you can use the CLI command:

```bash
$ php bin/whois [domain]
```

Or start a web server:

```bash
$ php bin/server
```

The web server can be configure with environment variables:

* `IP`: the IP address to listen
* `PORT`: the port to use

Example: `IP=0.0.0.0 PORT=8000 php bin/server`

## About

### PHP 7.4

The source code use typed properties which is available in PHP 7.4.

I use Docker to test it. There is a [Dockerfile](Dockerfile) in a project which use [phpdaily](https://phpdaily.github.io) to work with PHP development branches. 

### HTTP web server

The web server is provided by [ReactPHP](https://reactphp.org) HTTP server component. 
