PHP Portal Engine - single file framework
=========================================

PHPPE is a minimalistic, yet feature-full micro-framework and CMS. The framework's core is a single file and only 64k in size, so small, that it fits on your clipboard!
Unlike other existing OpenSource PHP frameworks, PHPPE was written with security, MVC, [KISS principle](http://en.wikipedia.org/wiki/KISS_principle) and no dependency at all in mind.
As being a micro-framework, it won't solve all of your web-development oriented tasks, but will definitely solve the most common ones and makes your life easier.
It's not bloated, and with simplicity cames stability and high performance.

Of course one single file is very limited, so here's the PHPPE Pack (~80KiB) to save the day and give you an easy start.
Includes normalize.css and JQuery2 to standardize views; eye candies like image zoomer, pop-up divs; SQL Query Builder, CSV and RSS output, RaspBerry Pi interface and even more!

For full CMS capability install the Content Editor too with PHPPE CMS (~30KiB), because PHPPE Core on it's own only serves contents.

See documentation for more (phppe3.html above) or visit the site [http://phppe.org/](http://phppe.org).

Features
--------
This 64k bytes of PHP code will give you:
- Stand alone environment, optional dependencies only. Single file deployment.
- Very low footprint, can run on a [Raspberry Pi](https://www.raspberrypi.org/)
- [PHP Composer](https://getcomposer.org/) compatibilty
- Can be used as CGI (Apache and nginx), from CLI and also as a library just out-of-the-box
- Has modular, easy to expand structure
- Self consistency check and diagnostics (even fix!)
- Environment auto-detection (like base url, browser's language, timezone and screen size)
- Clever, regular expression capable and filterable, standard URL to class::method routing mechanism
- PDO driven Database abstraction layer with transparent on demand scheme installation
- Convient and easy to use controller interface
- Fast and safe templater system for views
- Powerful caching with integrated [memcached](http://memcached.org/) support (can use others as well with Extensions)
- Automatic form data validation and security checks
- Access control lists
- Multilanguage support
- Logging to files as well as to syslog
- Library for common tasks with exactly 42 functions :-)
- Monitoring support (nagios can get status info and benchmark statistics easily from it's output)
- Thumbnail generation and image manipulation support (with libGD)
- Built-in Content Server for CMS support
- WYSIWYG web-based interface (editor available as an Extension)
- Uses View layer to detect Models (flexibility you've never seen)

Requirements
------------

At least PHP 5.5 (5.6 recommended). If you also install Pack and CMS Extensions, no more than 256k disk space needed.

Installation without Packagist
------------------------------

For detailed instructions and alternatives see [documentation](http://phppe.org/phppe3.html#install).

1. Create a webserver's document root in your project root

    ``` sh
    $ mkdir public
    ```

2. Download the framework (networkless alternative: copy it from the documentation and paste it into this command: `cat >public/index.php`)

    ``` sh
    $ curl https://raw.githubusercontent.com/bztphp/phppe3/master/public/index.php >public/index.php
    ```

3. Run diagnostics mode to extract directory structure (note that root privilege is only required for chown and chgrp calls)

    ``` sh
    $ sudo php public/index.php --diag
    ```

4. If you want additional features, install PHPPE Pack (~80kb) with

    ``` sh
    $ composer update
    ```

    or

    ``` sh
    $ curl https://raw.githubusercontent.com/bztphp/phppe3/master/phppe3_pack.tgz | tar -xz -C vendor/phppe && sudo php public/index.php --diag
    ```

Installation with Packagist
---------------------------
1. Create a new project

    ``` sh
    $ composer create-project "bztphp/phppe"
    ```

2. If you want additional features, install PHPPE Pack (~80kb) with

    ``` sh
    $ composer require "phppe"
    ```

Content Management
------------------

This single file also serves as a Content Server. You can install the CMS Content Editor as an extension

    $ composer require "phppe/CMS"

or

    $ curl https://raw.githubusercontent.com/bztphp/phppe3/master/phppe3_cms.tgz | tar -xz -C vendor/phppe/CMS

One CMS can feed several Content Servers. See [documentation](http://phppe.org/phppe3.html#contents) for more details on load balancing.

Extensions
----------

If you want a web based interface for extension management, install

    $ composer require "phppe/Extensions"

or

    $ curl https://raw.githubusercontent.com/bztphp/phppe3/master/phppe3_extmgr.tgz | tar -xz -C vendor/phppe/Extensions

This will give you the standard webadmin feeling you're used to, but unlike the competition, works in a secure way.

Testing
-------

Unit tests (over 100) and source are included in the Developer package

    $ composer require "phppe/Developer"

or

    $ curl https://raw.githubusercontent.com/bztphp/phppe3/master/phppe3_devel.tgz | tar -xz -C vendor/phppe/Developer

Tested under BSD, Darwin, Linux; apache2, nginx; php 5.5, 5.6; Firefox, Safari, Chrome. (Feed back on Win/IE would be nice).
You can also use [Packagist](https://packagist.org/packages/bztphp/phppe) to install the whole development environment:

    $ composer create-project "bztphp/phppe:dev-master"

Please note that this will install all the core extensions as well, including latest tarballs.

License
-------

PHPPE Core, PHPPE Pack as well as PHPPE CMS are free and OpenSource softwares, licensed under [LGPL-3.0+](http://www.gnu.org/licenses/). See vendor/phppe/LICENSE for details.

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

bzt AT phppe DOT org
