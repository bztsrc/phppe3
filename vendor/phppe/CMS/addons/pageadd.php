<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

// L("pageadd")
class pageadd extends \PHPPE\AddOn\pageinfo
{
    function edit()
    {
        if (!\PHPPE\Core::isTry()) {
            $page = new \PHPPE\Page;
            $page->template="simple";
            \PHPPE\View::assign("page", $page);
        }
        $quickhelp=!Core::lib("CMS")->expert;
        \PHPPE\View::assign("quickhelp",$quickhelp);
        return \PHPPE\View::template("cms_pageinfo");
    }

    function save($params)
    {
        //! save page as new
        return \PHPPE\Page::savePageInfo($params, true);
    }
}

?>
