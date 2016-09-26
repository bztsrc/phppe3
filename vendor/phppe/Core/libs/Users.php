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
 * @brief PHPPE Users, included in Pack
 */

namespace PHPPE;

//inherited from \PHPPE\User which in turn inherited from \PHPPE\Model
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

    /**
     * Authenticate with username and password and create user object
     *
     * @param string    username
     * @param string    password
     *
     * @return boolean  true on success
     */
    public function login($name, $pass)
    {
        // if another event handler already logged the user in, do nothing
        if(!empty($_SESSION['pe_u']->id))
            return;
        // login handler specific part
        $rec = \PHPPE\DS::fetch("id,pass", static::$_table, "name=? AND active!='0'", "", "", [$name]);
        // authentication
        if(empty($rec['pass']) || !password_verify($pass, $rec['pass']))
            return;
        // success, save user object in session
        $_SESSION['pe_u']=new self($rec['id']);
        // housekeeping
        \PHPPE\DS::exec("UPDATE ".static::$_table." SET logind=CURRENT_TIMESTAMP WHERE id=?", [$rec['id']]);
        return;
    }

    /**
     * Logout user
     */
    public function logout()
    {
        \PHPPE\DS::exec("UPDATE ".static::$_table." SET logoutd=CURRENT_TIMESTAMP WHERE id=?", [$this->id]);
    }
}
