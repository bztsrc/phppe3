<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztphp/phppe3/
 *
 *  Copyright LGPL 2015 bzt
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
 * @file vendor/phppe/CMS/07_CMS.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief WYSIWYG Content Editor
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

class CMS
{
	public $expert=false;
	public $revert=false;
	public $purge=false;

	function init($cfg) {
		PHPPE::lib("CMS","Content Editor", ["Core","wysiwyg"]);
		PHPPE::menu( L("CMS"), [
			L("Pages") ."@siteadm" => "cms/pages",
			L("Lists") ."@siteadm" => "cms/lists",
			L("Layouts") ."@siteadm" => "cms/layouts",
			L("Attachments") ."@siteadm" => "cms/attachments",
			PHPPE::isinst("CMSLT")? L("TimeLine") ."@siteadm" : "" => "cms/timeline",
		]);
		if(PHPPE::$core->app=="cms") {
			filter_loggedin();
			if(!PHPPE::$user->has("siteadm"))
				PHPPE::redirect("403");
		}
		if(PHPPE::$core->action=="pages")
			$_SESSION['cms_param']=[];
		if(!empty($cfg['expert']))
			$this->expert=true;
		if(!empty($cfg['revert']))
			$this->revert=true;
		if(!empty($cfg['purge']))
			$this->purge=true;
	}

	function dock() {
		if(PHPPE::$user->has("panel")) {
			if(PHPPE::$core->action=="pages")
				return PHPPE::template("cms_pagepanel");
			else
			if(PHPPE::$core->action=="layouts")
				return PHPPE::template("cms_layoutpanel");
		}
	}

	function icon($arg)
	{
		$title=$arg['title']=htmlspecialchars(L(@$arg[2]?$arg[2]:(@$arg[1]?$arg[1]:$arg[0])));
		list($a)=explode("(",$arg[0]);
		$arg['type']=$a;
		$spec=file_exists(__DIR__."/images/cms/".$a.".png");
		$_SESSION['cms_param'][]=$arg;
		return "<img style='padding:3px;position:absolute;' ".
			"onclick='cms_".urlencode($spec?$a:"edit")."(this,\"".urlencode($a)."\",".(count($_SESSION['cms_param'])-1).");' ".
			"src='images/cms/".($spec?urlencode($a).".png":"edit.png/".urlencode($a)."/".(count($_SESSION['cms_param'])-1))."' ".
			"alt='[".htmlspecialchars(strtoupper($a).($title?" ".$title:""))."]' ".
			"title='".$title."'>";
	}
}
