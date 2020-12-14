# TPR

[![CI Build Status](https://github.com/AxiosCros/tpr/workflows/CI/badge.svg)](https://github.com/AxiosCros/tpr/actions?query=workflow%3ACI)
[![Latest Stable Version](https://poser.pugx.org/axios/tpr/v)](//packagist.org/packages/axios/tpr)
[![Total Downloads](https://poser.pugx.org/axios/tpr/downloads)](//packagist.org/packages/axios/tpr)
[![License](https://poser.pugx.org/axios/tpr/license)](//packagist.org/packages/axios/tpr)


> TPR is a PHP framework for quickly developing CGI&CLI applications. 
>
> see the details from [documentation](https://github.com/AxiosCros/tpr/wiki).

## Required

- PHP >= 7.2  (TPR 3.*)
- PHP >= 7.4  (TPR 5.*) ```Support PHP8 on TPR 5.0.8 version```

## Install

```bash
composer require axios/tpr
```

## Quickly initialize application

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

## Demo project

- Example of Simple Application : [github.com/AxiosCros/tpr-app](https://github.com/AxiosCros/tpr-app)
- Example of CMS application : [github.com/AxiosCros/tpr-cms](https://github.com/AxiosCros/tpr-cms)

## License

The TPR framework is open-sourced software licensed under the [Apache license Version 2.0](http://www.apache.org/licenses/LICENSE-2.0).
