<?php
/**
 * @file vendor/phppe/CMS/ctrl/content.php
 * @author bzt
 * @date 26 May 2016
 * @brief
 */

namespace PHPPE\Ctrl;

class CMSContent
{

/**
 * content chooser, loaded via AJAX
 */
    function action($item)
    {
        $list = \PHPPE\Page::getPages(true);
        echo("<div class='wyswyg_content'>\n");
        foreach($list as $content) {
            echo("<a href='/".($content['id']=="index"?"":$content['id'])."'>".$content['name']."</a>\n");
        }
        die("</div>");
    }
}
