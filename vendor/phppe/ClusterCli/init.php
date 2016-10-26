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
 * @file vendor/phppe/ClusterCli/init.php
 * @author bzt
 * @date 27 Sep 2016
 * @brief
 */
namespace PHPPE;

//! add your routes here
Http::route("cluster/client",  "\\PHPPE\\Ctrl\\ClusterCli", "client");
Http::route("cluster/bindcfg", "\\PHPPE\\Ctrl\\ClusterCli", "bindcfg");

//! return your service class here
return new ClusterCli;
