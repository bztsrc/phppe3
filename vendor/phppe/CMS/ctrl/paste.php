<?php
/**
 * @file vendor/phppe/CMS/ctrl/paste.php
 * @author bzt
 * @date 26 May 2016
 * @brief insert styleguides into html
 */

namespace PHPPE\Ctrl;

class CMSPaste
{
/**
 * default action, loaded via AJAX
 */
    function action($item)
    {
        // get styleguides
        $list=[]; $t="";
        $files = @glob("vendor/phppe/*/views/styleguide.tpl");
        foreach($files as $f)
            $t.=@file_get_contents($f);
        if(preg_match_all("|<section[^>]+data\-id=[\'\"]?([^\>\'\"]+)[^>]*>(.*?)</section>|ms",$t,$m,PREG_SET_ORDER)){
            foreach($m as $d)
                $list[$d[1]]=$d[2];
        }
        // styleguide chooser
        $u=url("cms/paste");
        echo("<input type='text' style='width:98%;' placeholder='".L("Search")."' onkeyup='pe.wyswyg.search(this,this.nextSibling);'>");
        echo("<div class='wyswyg_styleguide wyswyg_scroll'>\n");
        foreach($list as $tag=>$cfg) {
            if($cfg[0]=='*' && $onlywidget)
                continue;
            echo("<img class='wyswyg_icon' src='js/wyswyg.js.php?item=".urlencode($tag)."' alt=\"".strtr($tag,["<"=>"&lt;",">"=>"&gt;","\""=>"&quot;"])."\" data-styleguide=\"".htmlspecialchars($cfg)."\">\n");
        }
        die("</div>");
    }

}
