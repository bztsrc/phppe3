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
 * @file vendor/phppe/DBA/01_DBA.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief DataBase Administrator tool
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

PHPPE::lib("bootstrap","Bootstrap");
PHPPE::css("bootstrap.min.css");
PHPPE::css("bootstrap-theme.min.css");
PHPPE::jslib("bootstrap.min.js");