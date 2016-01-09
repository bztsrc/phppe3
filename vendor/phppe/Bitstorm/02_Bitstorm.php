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
 * @file vendor/phppe/Bitstorm/02_Bitstorm.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief The Bitstorm torrent tracker
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

class Bitstorm
{
	private static $cfg;

	function init($cfg) {
		PHPPE::lib("Bitstorm","Lightweight Bittorrent tracker", ["Core"]);
		$cfg['peersdb']='data/Bittorrent.Peers';
		self::$cfg=$cfg;
		if(PHPPE::$core->app==(!empty($cfg['url'])?$cfg['url']:"torrent")) {
			include("libs/source.txt");
			die();
		}
		return true;
	}

	function stat() {
		if(PHPPE::$user->has("panel")) {
			$a=unserialize(@file_get_contents(self::$cfg['peersdb']));
			if(is_array($a)) {
				ob_start();
				print_r($a);
				$peers=nl2br(ob_get_clean());
			} else
				$peers=L("No peers currently");
			return
			"<div id='pe_torrents' class='sub' onmousemove='return pe_w();' style='visibility:hidden;'>Peers:<br><small>".$peers."</small></div>".
			"<span onclick='return pe_p(\"torrents\");'><img style='padding-right:3px;' src='images/bitstorm.png'>".(is_array($a)?count($a):0)."</span>";
		}
	}
}
