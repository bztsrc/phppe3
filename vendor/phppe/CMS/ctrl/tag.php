<?php
/**
 * @file vendor/phppe/CMS/ctrl/layouts.php
 * @author bzt
 * @date 26 May 2016
 * @brief
 */

namespace PHPPE\Ctrl;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Http as Http;

class CMSTag
{
/**
 * default action, loaded via AJAX
 */
    function action($item)
    {
			$list = [
            "/form"=>"*variable [url [onsubmitjs",
            "/if"=>"*expression",
            "else"=>"*",
            "/foreach"=>"*variable",
            "/template"=>"*",
            "include"=>"*view",
            "app"=>"*",
            "dump"=>"variable",
            "cms"=>"*addon ) variable",
            "="=>"expression",
            "L"=>"label",
            "date"=>"expression",
            "time"=>"expression",
            "difftime"=>"expression",
            "var"=>"*addon ) variable",
            "field"=>"*addon ) variable",
            "widget"=>"*addon ) variable",
            ];
            $d=array_merge(get_declared_classes(),array_keys(\PHPPE\ClassMap::$map));
			foreach($d as $c) {
				if(strtolower(substr($c,0,12))=="phppe\\addon\\") {
					$F=new $c([],"dummy",$c,[]);
					if(isset($F->conf) && $F->conf!="*")
						$list["_".strtolower(substr($c,12))]=$F->conf;
					unset($F);
				}
			}
		if(!empty($item)){
			//! edit form
print_r($list);
die(htmlspecialchars($item));
		} else {
			// tag chooser
			$u=url("cms/layouts");
			$onlywidget=(substr($_SERVER['HTTP_REFERER'],0,strlen($u))!=$u);
    	    echo("<input type='text' style='width:98%;' placeholder='".L("Search")."' onkeyup='pe.wyswyg.search(this,this.nextSibling);'>");
        	echo("<div class='wyswyg_tag wyswyg_scroll'>\n");
        	foreach($list as $tag=>$cfg) {
				if($cfg[0]=='*' && $onlywidget)
					continue;
				if(substr($tag,0,1)=="_") {
					$tag=($onlywidget?"widget":"field")." ".substr($tag,1);
				} else
				if(substr($tag,0,1)=="/") {
         			echo("<img class='wyswyg_icon' src='js/wyswyg.js?item=".urlencode("<!".substr($tag,1).">")."' alt=\"".strtr("<!".substr($tag,1).">",["<"=>"&lt;",">"=>"&gt;","\""=>"&quot;"])."\">\n");
				}
         		echo("<img class='wyswyg_icon' src='js/wyswyg.js?item=".urlencode("<!".$tag.">")."' alt=\"".strtr("<!".$tag.">",["<"=>"&lt;",">"=>"&gt;","\""=>"&quot;"])."\">\n");
        	}
        	die("</div>");
    	}
    }

}
