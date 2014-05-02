# SchoolBusManager
================

## Introduction
------------
Application permettant de gérer un service de transports scolaires.

## Installation
------------

### Using Composer (recommended)
----------------------------
< à renseigner >

### Using Git submodules
--------------------
  Alternatively, you can install using native git submodules:

    git clone git://github.com/dafap/SchoolBusManager.git --recursive

## Web Server Setup
----------------

### PHP CLI Server

The simplest way to get started if you are using PHP 5.4 or above is to start the internal PHP cli-server in the root directory:

    php -S 0.0.0.0:8080 -t public/ public/index.php

This will start the cli-server on port 8080, and bind it to all network
interfaces.

_Note:_ The built-in CLI server is _for development only_.

### Apache Setup

To setup apache, setup a virtual host to point to the public/ directory of the project and you should be ready to go! It should look something like below:

    # Virtual Hosts for PHP 5.4 
    #
    # Required modules: `mod_log_config\`
    <VirtualHost *:80>
        ServerAdmin webmaster@sbm.dev
        DocumentRoot /path/to/sbm/public
        ServerName www.sbm.localhost
        ServerAlias sbm.dev
        ErrorLog /path/to/sbm/logs/sbm_error.log
        CustomLog /path/to/sbm/logs/sbm_access.log common
        SetEnv APPLICATION_ENV "development"
        <Directory "/path/to/sbm/public">
            DirectoryIndex index.php
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>`