<?php
/**
 * @file vendor/phppe/CMS/ctrl/content.php
 * @author bzt
 * @date 26 May 2016
 * @brief page link chooser
 */

namespace PHPPE\Ctrl;

use PHPPE\Page;

class CMSContent
{

/**
 * content chooser, loaded via AJAX
 */
    function action($item)
    {
        $list = Page::getPages(true);
        echo("<input type='text' style='width:98%;' placeholder='".L("Search")."' onkeyup='pe.wyswyg.search(this,this.nextSibling);'>");
        echo("<div class='wyswyg_content wyswyg_scroll'>\n");
        foreach($list as $content) {
            echo("<a href='/".($content['id']=="index"?"":$content['id'])."'>".$content['name']."</a>\n");
        }
        die("</div>");
    }
}
