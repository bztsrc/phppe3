<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\Page;

// L("pagedds")
class pagedds extends \PHPPE\AddOn
{
    public $heightClass = "infobox";
    public $forceFull = 80;

    function load(&$app)
    {
        //! load global dds as well from frame
        $frame = new Page("frame");
        $page = View::getval("page");
        $page->gdds = $frame->dds;
    }

    function edit()
    {
        $quickhelp=!Core::lib("CMS")->expert;
        View::assign("quickhelp",$quickhelp);
        return View::template("cms_pagedds");
    }

    function save($params)
    {
        Page::saveDDS("frame", $params['gdds']);
        return Page::saveDDS($params['pageid'], $params['dds']);
    }
}

?>
