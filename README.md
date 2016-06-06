PHP Portal Engine - single file framework
=========================================

PHPPE is a minimalistic, yet feature-full micro-framework and CMS. The framework's core is a single file and only a few kilobytes in size, so small, that it fits on your clipboard!
Unlike other existing OpenSource PHP frameworks, PHPPE was written with security, [MVC](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller), [KISS principle](http://en.wikipedia.org/wiki/KISS_principle) and no dependency at all in mind.
As being a micro-framework, it won't solve all of your web-development oriented tasks, but will definitely solve the most common ones and make your life easier.
It's not bloated, and with simplicity cames stability and high performance.

Features
--------
This 80k bytes of PHP code will give you:
- Stand alone environment, optional dependencies only. Single file deployment.
- [PHP Composer](https://getcomposer.org/) compatibilty
- [PHPUnit](https://phpunit.de) compatibility with [100% code coverage](http://bztsrc.github.io/phppe3/coverage)
- [Bootstrap](https://getbootstrap.com/) compatibilty
- Can be used as CGI (Apache and nginx), from CLI and also as a library just out-of-the-box
- Very low footprint, less than 768KiB, ideal on small computers such as [Raspberry Pi](https://www.raspberrypi.org/)
- Highly modular, easy to expand structure with Class autoloader
- Self consistency check and diagnostics (even fix!)
- Environment auto-detection (like base url, browser's language, timezone and screen size)
- Clever, regular expression capable and filterable, standard URL to class::method routing mechanism
- PDO driven Database abstraction layer with transparent on demand scheme installation
- Convient and easy to use controller and ORM model interface
- Fast and safe templater system for views
- Powerful caching with integrated [memcached](http://memcached.org/), APC/APCu and file cache support
- Automatic form data validation and security checks
- Access control lists
- Multilanguage support
- Logging to files as well as to syslog
- Monitoring support (nagios can get performance and status info easily from it's output)
- Thumbnail generation and image manipulation support (with libGD)
- Built-in Content Server for CMS support
- Uses View layer to detect Models (flexibility you've never seen)

Of course one single file is limited, so here's the [PHPPE Pack](http://bztsrc.github.io/phppe3/phppe3_core.tgz) (~80KiB) to save the day and give you an easy start.

For full CMS capability you'll also need the Content Editor with [PHPPE CMS](http://bztsrc.github.io/phppe3/phppe3_cms.tgz) (28KiB), because PHPPE Core on it's own only serves contents.

Requirements
------------

- At least PHP 7.0
- SSH terminal access (use ssh or PuTTY)
- Apache or nginx with php-fpm on server side
- Any HTML5 compatible browser on client side
- No more than 768KiB free space if you install the full basic environment (with Pack, CMS, Extensions and Developer)

Installation with Packagist
---------------------------
1. Create a new project that ships production version of the PHPPE framework with

    ``` sh
    $ composer create-project "bztsrc/phppe3"
    $ mv phppe3 myProject
    ```

Installation without Packagist
------------------------------

There are many ways to install PHPPE if you don't want Packagist. You can use git, Composer alone, or use curl/wget.
For detailed instructions and alternatives see [documentation](http://bztsrc.github.io/phppe3/index.html#install).

1. Create a webserver's document root in your project root

    ``` sh
    $ mkdir public
    ```

2. Download the framework Core (networkless alternative: copy it from the [documentation](http://bztsrc.github.io/phppe3/index.html#downloads) and paste it into this command: `cat >public/index.php`)

    ``` sh
    $ curl https://raw.githubusercontent.com/bztsrc/phppe3/master/public/index.php >public/index.php
    ```

3. Run diagnostics mode to extract directory structure, including vendor/phppe/Core (note that root privilege is only required for chown and chgrp calls)

    ``` sh
    $ sudo php public/index.php --diag
    ```

4. If you want the full functionality in vendor/phppe/Core, also install PHPPE Pack with

    ``` sh
    $ composer update
    ```

    or without Composer

    ``` sh
    $ curl https://bztsrc.github.io/phppe3/phppe3_core.tgz | tar -xz -C vendor/phppe/Core && sudo php public/index.php --diag
    ```

Content Management
------------------

This single file also serves as a Content Server. You can install the CMS Content Editor as an extension with

    $ composer require "phppe/CMS"

or

    $ curl https://bztsrc.github.io/phppe3/phppe3_cms.tgz | tar -xz -C vendor/phppe/CMS

In a scalable, multi-server environment one Content Editor can feed several Content Servers. See [documentation](http://bztsrc.github.io/phppe3/index.html#contents) for more details on load balancing.

Extensions
----------

If you want a web based interface for extension management, install

    $ composer require "phppe/Extensions"

or

    $ curl https://bztsrc.github.io/phppe3/phppe3_extensions.tgz | tar -xz -C vendor/phppe/Extensions

This will give you the standard webadmin feeling you're used to with other frameworks, but unlike the others, this works in a secure way over SSH.

Developing
----------

Even if you don't want to contribute to the framework, just using it or writing your own Extensions, it's worth installing the Developer package!
That will give you a nice templater to generate your php files, an utility to create language dictionaries out of your code, and also ships a minimal, PHPUnit compatible testing framework.

    $ composer require "phppe/Developer"

or

    $ curl https://bztsrc.github.io/phppe3/phppe3_developer.tgz | tar -xz -C vendor/phppe/Developer

This will provide you utilities like

    $ php public/index.php create model myExtension myModel
    $ php public/index.php create controller myExtension myController
    $ php public/index.php create route myExtension myurl myController myAction
    $ php public/index.php lang myExtension en
    $ php public/index.php tests run
    $ php public/index.php mkrepo

You can use [Packagist](https://packagist.org/packages/bztsrc/phppe3) to install the whole repository with all extensions:

    $ composer create-project "bztsrc/phppe3:dev-master"

License
-------

PHPPE Core, PHPPE Pack, PHPPE CMS, PHPPE Extensions as well as PHPPE Developer are free and OpenSource softwares, licensed under [LGPL-3.0+](http://www.gnu.org/licenses/). See vendor/phppe/LICENSE for details.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published
    by the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

Author
------

zoltan DOT baldaszti AT gmail DOT com
