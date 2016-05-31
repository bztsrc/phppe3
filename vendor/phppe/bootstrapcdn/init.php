<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016 bzt
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *   <http://www.gnu.org/licenses/>
 *
 * @file vendor/phppe/bootstrapcdn/init.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief Bootstrap CDN integration in PHPPE
 */

if (!\PHPPE\Core::isInst("bootstrap")) {
    //load style sheests
    \PHPPE\View::css("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css");
    \PHPPE\View::css("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css");

    //load JavaScript
    \PHPPE\View::jslib("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js","",1);

    //register ourself as bootstrap too
    \PHPPE\Core::lib("bootstrap", "PHPPE\Extension");
}
