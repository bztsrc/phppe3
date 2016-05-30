<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/.
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
 * @file vendor/phppe/Core/libs/Users.php
 *
 * @author bzt
 * @date 1 Jan 2016
 * @brief PHPPE Users
 */

namespace PHPPE;

//inherited from \PHPPE\Model and \PHPPE\User
class Users extends \PHPPE\User
{
    //properties
    public $id;
    public $name;
    public $email;
    public $parentid;
    public $active;

    //database table name for model methods
    protected static $_table = 'users';

    public function login($name, $pass)
    {
        $rec = \PHPPE\DS::fetch("id,pass", static::$_table, "name=? AND active!='0'", "", "", [$name]);
        if(empty($rec['pass']) || !password_verify($pass, $rec['pass']))
            return false;
        $_SESSION['pe_u']=new self($rec['id']);
        Core::log('A', 'Login '.$name, 'users');
        \PHPPE\DS::exec("UPDATE ".static::$_table." SET logind=CURRENT_TIMESTAMP WHERE id=?", [$rec['id']]);
        Http::redirect();
    }

    public function logout()
    {
        \PHPPE\DS::exec("UPDATE ".static::$_table." SET logoutd=CURRENT_TIMESTAMP WHERE id=?", [$this->id]);
    }
}
