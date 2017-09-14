## Ensure dependencies are installed

Our reference system:

- Ubuntu 14.04.4 LTS
- PHP 5.5.9
- MySQL 5.5.49

A mostly complete list of installation requirements for Ubuntu:

    sudo apt-get install apache2
    sudo apt-get install mysql-client mysql-server
    sudo apt-get install php5-cli php5-common php5-curl php5-dev php5-gd php5-mysql libapache2-mod-php5 php-pear
    sudo apt-get install zip
    sudo pear install MDB2-2.5.0b4
    sudo pear install MDB2_Driver_mysqli-1.5.0b4
    sudo service apache2 restart


## Install the Archon code

First, make the directories that will hold the Archon instance:

    sudo mkdir -p /var/www/archon/{htdocs,log}
    sudo chown -R $USER:$USER /var/www/archon/htdocs
    cd /var/www/archon/htdocs

Next, download Archon. You can find download links for several Archon versions
here:

- <https://github.com/archonproject/archon/releases> (official releases)
- <https://github.com/LibraryHost/archon/releases> (LibraryHost development location - Archon Update Project)

In the following commands, we will download a release. The actual release filename varies. `v0.0.0` is used as an example only.

    wget https://github.com/archonproject/archon/archive/v0.0.0.tar.gz
    tar xfvz v0.0.0.tar.gz
    mv v0.0.0/* ./


## Change settings in `config.inc.php`

A default config file called `configblank.inc.php` is supplied with Archon.
Make a copy of this default file:

    cp configblank.inc.php config.inc.php

Then set these options in `config.inc.php`, picking your own values for
`<username>`, `<password>`, and `<databasename>`:

    $_ARCHON->db->Login = '<username>';
    $_ARCHON->db->Password = '<password>';
    $_ARCHON->db->DatabaseName = '<databasename>';
    $_ARCHON->db->ServerType = 'MySQLi';

## Configure Apache

Example configuration for a virtual host (for multiple websites hosted on one server):

`/etc/apache2/sites-enabled/<name>.conf`:

(`ServerName` can be set to anything you want.)

    <VirtualHost *:80>
            ServerName  archon.mydomain.com
            DirectoryIndex index.php
    
            DocumentRoot /var/www/archon/htdocs
            LogLevel warn
    
            ErrorLog  /var/www/archon/log/error.log
            CustomLog /var/www/archon/log/access.log combined
    </VirtualHost>

Example configuration for a dedicated host (only one website on the server):

`/etc/apache2/sites-enabled/000-default`:


    <VirtualHost *:80>
            DirectoryIndex index.php
    
            DocumentRoot /var/www/archon/htdocs
            LogLevel warn
    
            ErrorLog  /var/www/archon/log/error.log
            CustomLog /var/www/archon/log/access.log combined
    </VirtualHost>


## Prepare the Archon database and user on the new host

First, create a MySQL user that has the username and password you set in
`config.inc.php` (see above).

Log in to MySQL as the root user:

    $ mysql -u root -p

At the prompt, type the following to create the user:

    mysql> CREATE DATABASE <databasename>;
    mysql> CREATE USER <username>@localhost IDENTIFIED BY "<password>";
    mysql> GRANT ALL ON <databasename>.* TO <username>@localhost;
    mysql> FLUSH PRIVILEGES;


## Run the Archon web installer **or** migrate your existing database

The Archon web installer sets up the tables and initial data Archon needs to be
functional. You should only run the installer if you are not migrating an
existing database.

### (Optional) Run the Archon web installer

First, enable the installer:

    mv packages/core/install/install.php_ packages/core/install/install.php

Use the web interface to complete the installation process. When finished,
disable the installer:

    mv packages/core/install/install.php packages/core/install/install.php_

### (Optional) Migrate your existing database

Export the existing database from the old host with the following command. (You
can find the proper values for <username> and <databasename> in the
`config.inc.php` file in the Archon root directory on your existing server.)

    mysqldump -u <username> -p <databasename> > archon.sql

On the new host:

    $ mysql -u <username> -p <databasename>

At the prompt, type the following to load the database:

    mysql> USE <databasename>;
    mysql> source archon.sql



## Test the installation

Try to load the Archon web interface.

If you get something like "Error connecting to database!":

- Make sure the database adapter is set to `MySQLi` in `config.inc.php`
- Make sure you installed the PEAR dependencies listed aboved

If you get a mostly blank page, it might mean that your old Archon site was
using a custom theme which needs to be copied over to the new box. See the next
section for more info. For quick testing, just change the theme to the default
using the admin interface:

    http://your-site.your-domain.com/admin

Log in as the `sa` user and go to Archon Administration | Archon Configuration
| Default Theme. Then pick the theme called "default".


## (Optional) Copy over any customizations from old Archon install

If your Archon installation is customized, you'll need to make those same
customizations to the new codebase.

* * * * *

## Archon Security

ModSecurity is an open-source web application firewall that detects and blocks
malicious HTTP activity. You can improve Archon's security by running Archon
with ModSecurity.

Please see the following document in this repository for more information:

[Using ModSecurity with Archon](ModSecurity.md)
