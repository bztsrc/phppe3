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
 * @file vendor/phppe/MultiServer/libs/MultiServer.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief Allows to manage multiple servers at once.
 */
namespace PHPPE;

/**
 * Main class
 *
 */
class MultiServer extends Extension
{
    public static $cache = "data/multiserver";
    public static $servers = [];
/**
 * Register MultiServer
 *
 * @param cfg not used
 */
	public function init($cfg) {
		//! check whether we are enabled
		//if(empty($cfg['type']))
		//	return false;

        View::jslib("multiserver.js");
        View::css("multiserver.css");
	}

    public function stat()
    {
        if (!Core::$user->has("remoteadm")) {
            return '';
        }
        $servers = self::load();
        if(!empty(Core::$user->data['remote'])){
            $was=0; $d=Core::$user->data['remote'];
            foreach($servers as $k=>$s){
                if(@$d['user']==$s['user'] && @$d['host']==$s['host'] && @$d['port']==$s['port'] && @$d['path']==$s['path']) {
                    $was=$k; break;
                }
            }
            if(!$was) {
                $was=L("Default");
                @self::add($was,$d['host'],$d['port'],$d['user'],$d['identity'],$d['path']);
                $servers = self::load();
            }
        }
        $t="";
        foreach($servers as $k=>$s){
            $t.="<li ".($k==$was?"style='background:#A0A0FF;' ":"")."title='".htmlspecialchars($s['user']."@".$s['host'].$s['path'])."'><span onclick='pe.multiserver.remove(\"".$k."\");' class='glyphicon glyphicon-trash'></span><span onclick='pe.multiserver.set(\"".$k."\");'>".$k."</span></li>";
        }
        $t.="<li onclick='pe.multiserver.add();'><span class='glyphicon glyphicon-plus-sign'></span>".L("Add New Server")."</li>";
        return "<div id='pe_ms' class='sub' style='display:none;' onmouseover='return pe_w();'><ul>".$t."</ul></div>".
               "<span class='glyphicon glyphicon-hdd' onclick='return pe_p(\"pe_ms\");'></span>";
    }

    function load()
    {
        if (!Core::$user->has("remoteadm")) {
            return [];
        }
        if(empty(self::$servers)){
            if(!empty($_SESSION['pe_msrv'])){
                self::$servers=$_SESSION['pe_msrv'];
            } else {
                $d=@file_get_contents(self::$cache);
                if(empty($d)){
                    $d=json_decode($d,true);
                    self::$servers=is_array($d)?$d:[];
                    $_SESSION['pe_msrv']=self::$servers;
                }
            }
        }
        return self::$servers;
    }

    function save()
    {
        if (!Core::$user->has("remoteadm")) {
            return;
        }
        $_SESSION['pe_msrv']=self::$servers;
        file_put_contents(self::$cache, json_encode(self::$servers));
    }

    function add($id,$host,$port,$user,$identity,$path)
    {
        self::load();
        self::$servers[$id]=['host'=>$host,'port'=>$port,'user'=>$user,'identity'=>$identity,'path'=>$path];
        self::save();
        self::set($id);
    }

    function remove($id)
    {
        self::load();
        unset(self::$servers[$id]);
        self::save();
    }

    function set($id)
    {
        self::load();
        if(!empty(self::$servers[$id])) {
            $_SESSION['pe_u']->data['remote']=Core::$user->data['remote']=self::$servers[$id];
        }
    }

    function addAction($item)
    {
        $obj=Core::req2arr('ms');
        if(!empty($obj['id']) && !empty($obj['host']) && !empty($obj['user']) && !empty($obj['identity']) && !empty($obj['path'])){
            self::add($obj['id'],$obj['host'],$obj['port'],$obj['user'],$obj['identity'],$obj['path']);
            die("OK");
        }
        die("ERR");
    }

    function removeAction($item)
    {
        self::remove($item);
        die("OK");
    }

    function setAction($item)
    {
        self::set($item);
        die("OK");
    }
}
