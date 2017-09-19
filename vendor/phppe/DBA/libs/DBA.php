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
 * @file vendor/phppe/DBA/libs/DBA.php
 * @author bzt
 * @date 29 Sep 2016
 * @brief DataBase Administrator
 */

namespace PHPPE;

use PHPPE\Core;
use PHPPE\DS;

class DBA
{
    private static $self;

    public function __construct()
    {
    }
    
    function init($config) {
    }

    /** Get tables from database */
    static function tables() {
        $arr = DS::exec("show tables");
        $ret = [];
        foreach($arr as $r) {
            $v = reset($r);
            if($v!="sqlite_sequence")
                $ret[$v] = $v;
        }
        return $ret;
    }

    /** Get column definitions for a table */
    static function columns($table) {
        $arr = DS::exec("show columns from ".$table);
        $ret = [];
        foreach($arr as $r) {
            $name = !empty($r['Field'])? $r['Field'] : $r['name'];
            $ret[] = [
                'id' => $name,
                'name' => ucfirst(L($name)),
                'type' => strtolower(!empty($r['Type'])? $r['Type'] : $r['type']),
                'key' => (!empty($r['Key']) && $r['Key']=='PRI') || (!empty($r['pk']) && $r['pk']) ? true : false
            ];
        }
        return $ret;
    }
}
