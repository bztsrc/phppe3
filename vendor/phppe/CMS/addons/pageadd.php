<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

class pageadd extends \PHPPE\AddOn\pageinfo
{
    function edit()
    {
        if (!\PHPPE\Core::isTry()) {
            $page = new \PHPPE\Page;
            \PHPPE\View::assign("page", $page);
        }
        return \PHPPE\View::template("cms_pageinfo");
    }

    function save($params)
    {
        //! save page as new
        return \PHPPE\Page::savePageInfo($params, true);
    }
}

?>
