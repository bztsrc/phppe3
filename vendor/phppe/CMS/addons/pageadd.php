<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\Page;

// L("pageadd")
class pageadd extends \PHPPE\AddOn\pageinfo
{
    function edit()
    {
        if (!Core::isBtn()) {
            $page = new Page;
            $page->template="simple";
            View::assign("page", $page);
        }
        $quickhelp=!Core::lib("CMS")->expert;
        View::assign("quickhelp",$quickhelp);
        return View::template("cms_pageinfo");
    }

    function save($params)
    {
        //! save page as new
        return Page::savePageInfo($params, true);
    }
}

?>
