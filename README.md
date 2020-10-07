# TPR

> The framework for quickly developing cgi&cli applications

[![Travis Build Status](https://travis-ci.com/AxiosCros/tpr.svg?branch=master&status=unknown)](https://travis-ci.com/AxiosCros/tpr)

## Required

- PHP >= 7.2  (TPR 3.*)
- PHP >= 7.4  (TPR 5.*)

## Install

```bash
composer require axios/tpr
```

## Quickly create app

### git clone from github repo

```bash
# download from github
git clone https://github.com/AxiosCros/tpr-app.git

# install libraries
cd tpr-app/ && composer install

# run cli
php tpr 
```

### create project by `tpr-cli` command

```bash
composer global require axios/tpr

# set `~/.composer/vendor/bin` or `~/.config/composer/vendor/bin` to your PATH environment variable
tpr-cli create <app-name>
```

## Usage demo

- Example of Simple Application : [github.com/AxiosCros/tpr-app](https://github.com/AxiosCros/tpr-app)
- Example of CMS application : [github.com/AxiosCros/tpr-cms](https://github.com/AxiosCros/tpr-cms)

## [Document for development](https://github.com/AxiosCros/tpr/wiki)

## License

The TPR framework is open-sourced software licensed under the [Apache license Version 2.0](http://www.apache.org/licenses/LICENSE-2.0).
