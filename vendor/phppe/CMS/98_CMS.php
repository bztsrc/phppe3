<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
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
 * @file vendor/phppe/CMS/98_CMS.php
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
	public $purge=3;
	public $metas=[];

	function init($cfg) {
		PHPPE::lib("CMS","Content Editor", ["Core","wysiwyg"], $this);
		if(PHPPE::$core->app=="cms") {
			\PHPPE\Filter\loggedin::filter();
			if(!PHPPE::$user->has("siteadm|webadm"))
				PHPPE::redirect("403");
		}
		if(PHPPE::$core->action=="pages")
			$_SESSION['cms_param']=[];
		if(!empty($cfg['expert']))
			$this->expert=true;
		if(!empty($cfg['pagehistory']))
			$this->revert=true;
		if(isset($cfg['purge']))
			$this->purge=intval($cfg['purge']);
		if($this->purge<3)
			$this->purge=8;
		if($this->purge>128)
			$this->purge=128;
		if(!empty($cfg['metas']))
			$this->metas=x(",",$cfg['metas']);

		if(PHPPE::$user->has("panel")) {
			PHPPE::jslib("cms.js","cms_init();");
		}

		if(!empty($_REQUEST['asset'])){
			$d=explode("/",trim($_REQUEST['asset']));
			if(count($d)!=2||!in_array($d[0],["css","js","images"])) {
				header("HTTP/1.1 403 Acces forbidden");
				die("Access forbidden");
			}
			header("Content-type:".($d[0]=="js"?"text/javascript":($d[0]=="css"?"text/css":"image/png")).";charset=utf-8");
			die(file_get_contents(".tmp/".session_id()."/".$d[0]."/".$d[1]));
		}
		return true;
	}

	function stat() {
		if(PHPPE::$user->has("panel")) {
			$cms_menu=
				(PHPPE::$user->has("siteadm")?"<span style='margin-left:4px;'><img title=\"".L("CMS Layouts")."\" src='images/cms/layouts.png' onclick='document.location.href=\"".url("cms","layouts")."\";'></span>":"").
				(PHPPE::$user->has("siteadm|webadm")?"<span><img title=\"".L("CMS Pages")."\" src='images/cms/pages.png' onclick='document.location.href=\"".url("cms","pages")."\";'></span>":"");
			$c=get_class(PHPPE::getval("app"));
			if($c=="PHPPE\App" || $c=="PHPPE\Content")
				return
//			"<span style='padding-right:6px;'><a href='".url("cms","pages")."'><img src='images/cms/home.png'></a></span>".
//			"<span style='padding-right:4px;'><img onclick=\"cms_pageadd(this,&quot;pageadd&quot;);\" src=\"images/cms/pageadd.png\" title=\"".L("Add new page")."\"></span>".
			"<span><a href='cms/pages/".(PHPPE::$core->app."/".(PHPPE::$core->action!="action"?PHPPE::$core->action:"").(PHPPE::$core->item?"/".PHPPE::$core->item:""))."'><img src='images/cms/edit.png' title=\"".L("Edit page")."\"></a></span>".
			$cms_menu;
			elseif(PHPPE::$core->app!="cms")
				return $cms_menu;
			elseif(PHPPE::$core->action=="pages")
				return PHPPE::template("cms_pagepanel").$cms_menu;
			elseif(PHPPE::$core->action=="layouts")
				return PHPPE::template("cms_layoutpanel").$cms_menu;
		}
	}

	function icon($arg)
	{
		if(empty(PHPPE::$core->item)) return "";
		$title=$arg['title']=htmlspecialchars(L(@$arg[2]?$arg[2]:(@$arg[1]?$arg[1]:$arg[0])));
		list($a)=explode("(",$arg[0]);
		$arg['type']=$a;
		$spec=file_exists(__DIR__."/images/cms/".$a.".png");
		$_SESSION['cms_param'][]=$arg;
		$u=url("/"); if($u[strlen($u)-1]!="/") $u.="/";
		return "<img style='position:absolute;z-index:998;".($spec&&$a!="pagelist"?"":"opacity:0.7;")."' ".
			"onclick='cms_".urlencode($spec?$a:"edit")."(this,\"".urlencode($a)."\",".(count($_SESSION['cms_param'])-1).");' ".
			"src='".$u."images/cms/".($spec?urlencode($a).".png":"edit.png/".urlencode($a)."/".(count($_SESSION['cms_param'])-1))."' ".
			"alt='[".htmlspecialchars(strtoupper($a).($title?" ".$title:""))."]' ".
			"title='".$title."'>";
	}

	static function taghtml($data)
	{
			$t="";
			if(preg_match("|<body[^>]*>(.*)</body>|ims",$data,$m)) {
				$id=1;
				for($i=0;$i<strlen($m[1]);$i++){
					if($m[1][$i]=='<'&&$m[1][$i+1]!='/') {
						while($m[1][$i]!=''&&$m[1][$i]!=' '&&$m[1][$i]!='>'&&$m[1][$i]!="\t"&&$m[1][$i]!="\n"&&$m[1][$i]!="\r")
							$t.=$m[1][$i++];
						$t.=" data-chooseid='".$id++."'";
					}
					$t.=$m[1][$i];
				}
			}
			return $t;
	}

	static function splithtml($data,$id,$idx=1)
	{
		if(!preg_match("|data-chooseid|",$data))
			$data=self::taghtml($data);
		if($id==0)
			return $idx==1?preg_replace("| data-chooseid='[0-9]+'|ims","",trim($data)):"";
		if( preg_match_all( "/<([^\ \t\r\n]+)[\ \t\r\n]data-chooseid='".$id."'[^>]*>/ims", $data,$T,PREG_OFFSET_CAPTURE | PREG_SET_ORDER ) )
		{
			if($idx==0)
				return preg_replace("| data-chooseid='[0-9]+'|ims","",trim(substr($data,0,$T[0][0][1])));
			$c=0;
			$i=$T[0][0][1];$lc=$i;while($data[$lc]!=''&&$data[$lc]!='>') $lc++;$lc++;
			for(;$i<strlen($data);$i++) {
				if(strtolower(substr($data,$i,strlen($T[0][1][0])+1))=='<'.strtolower($T[0][1][0]) ) $c++;
				if(strtolower(substr($data,$i,strlen($T[0][1][0])+2))=='</'.strtolower($T[0][1][0]) ) $c--;
				if($c==0) {
					while($data[$i]!=''&&$data[$i]!='>') $i++;$i++;$lc=$i;
					return preg_replace("| data-chooseid='[0-9]+'|ims","",trim($idx==1? substr($data,$T[0][0][1],$i-$T[0][0][1]) : substr($data,$i)));
				}
			}
			if($c!=0) return preg_replace("| data-chooseid='[0-9]+'|ims","",trim($idx==1? substr($data,$T[0][0][1],$lc-$T[0][0][1]) : substr($data,$lc)));
		}
		return "";
	}
}
