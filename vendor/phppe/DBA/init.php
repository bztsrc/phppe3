<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016
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
 * @file vendor/phppe/DBA/init.php
 * @author bzt
 * @date 29 Sep 2016
 * @brief
 */
namespace PHPPE;

//! add your routes here
//\PHPPE\Http::route("dba", "\\PHPPE\\Ctrl\\DBA", "action");
\PHPPE\View::menu(L("Database")."@siteadm","dba");

//! return your service class here
return new DBA;
