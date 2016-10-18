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
		if(!empty($item)){
			//! edit form
die(htmlspecialchars($item));
		} else {
			// tag chooser
			$list = [
            "="=>"expression",
            "L"=>"label",
            "date"=>"expression",
            "time"=>"expression",
            "difftime"=>"expression",
            "/form"=>"variable [url [onsubmitjs",
            "/if"=>"expression",
            "else"=>"",
            "/foreach"=>"variable",
            "/template"=>"",
            "include"=>"view",
            "app"=>"",
            "dump"=>"variable",
            "var"=>"addon ) variable",
            "field"=>"addon ) variable",
            "widget"=>"addon ) variable",
            "cms"=>"addon ) variable"
            ];
    	    echo("<input type='text' style='width:98%;' placeholder='".L("Search")."' onkeyup='pe.wyswyg.search(this,this.nextSibling);'>");
        	echo("<div class='wyswyg_tag wyswyg_scroll'>\n");
        	foreach($list as $tag=>$cfg) {
				if(substr($tag,0,1)=="/") {
         			echo("<img class='wyswyg_icon' src='js/wyswyg.js?item=".urlencode("<!".substr($tag,1).">")."' alt=\"".strtr("<!".substr($tag,1).">",["<"=>"&lt;",">"=>"&gt;","\""=>"&quot;"])."\">\n");
				}
         		echo("<img class='wyswyg_icon' src='js/wyswyg.js?item=".urlencode("<!".$tag.">")."' alt=\"".strtr("<!".$tag.">",["<"=>"&lt;",">"=>"&gt;","\""=>"&quot;"])."\">\n");
        	}
        	die("</div>");
    	}
    }

}
