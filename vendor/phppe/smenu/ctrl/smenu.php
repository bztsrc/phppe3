<?php
/**
 * @file vendor/phppe/smenu/ctrl/smenu.php
 * @author bzt
 * @date 31 May 2017
 * @brief
 */

namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\DS;

class smenu
{

	function add($item)
	{
        if(!Core::$user->has("siteadm|webadm"))
            die(L("Access denied"));
        if(!empty($_POST['url']) && !empty($_POST['id'])) {
            try {
                DS::exec("INSERT INTO smenu_list (list_id,id,title,posx,posy) VALUES (?,?,?,20,20)",
                    [$_POST['url'], $_POST['id'], $_POST['id']]);
                die("OK");
            } catch(\Exception $e) {
                die($e->getMessage());
            }
        }
        die(L("hacker"));
	}

	function move($item)
	{
        if(!Core::$user->has("siteadm|webadm"))
            die(L("Access denied"));
        if(!empty($_POST['url']) && !empty($_POST['id']))
            DS::exec("UPDATE smenu_list SET posx=?,posy=? WHERE list_id=? AND id=?",
                [intval($_POST['x']), intval($_POST['y']), $_POST['url'], $_POST['id']]);
	    die("OK");
	}

	function edit($item)
	{
        if(!Core::$user->has("siteadm|webadm"))
            die(L("Access denied"));
        if(!empty($_POST['url']) && !empty($_POST['id']))
            DS::exec("UPDATE smenu_list SET title=?,type=? WHERE list_id=? AND id=?",
                [$_POST['title'], $_POST['type'], $_POST['url'], $_POST['id']]);
	    die("OK");
	}

	function del($item)
	{
        if(!Core::$user->has("siteadm|webadm"))
            die(L("Access denied"));
        if(!empty($_POST['url']) && !empty($_POST['id']))
            DS::exec("DELETE FROM smenu_list WHERE list_id=? AND id=?", [$_POST['url'], $_POST['id']]);
	    die("OK");
	}

}
