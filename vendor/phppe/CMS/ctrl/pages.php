<?php
/**
 * @file vendor/phppe/CMS/ctrl/pages.php
 * @author bzt
 * @date 26 May 2016
 * @brief
 */

namespace PHPPE\Ctrl;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Http as Http;

class CMSPages
{
    public $pages;

/**
 * default action
 */
    function action($item)
    {
        if(!empty($_REQUEST['pagedel'])) {
            \PHPPE\Page::delete($_REQUEST['pagedel']);
            Http::redirect();
        }

        $pages = \PHPPE\Page::getPages(intval(@$_REQUEST['order']));
        foreach ($pages as $p)
            $this->pages[
                empty($_REQUEST['order'])?0:(empty($p['template'])?$p['tid']:$p['template'])
                ][] = $p;
    }
}
