<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

// L("pagedds")
class pagedds extends \PHPPE\AddOn
{
    public $heightClass = "infobox";
    public $forceFull = 80;

    function load(&$app)
    {
        //! load global dds as well from frame
        $frame = new \PHPPE\Page("frame");
        $page = \PHPPE\View::getval("page");
        $page->gdds = $frame->dds;
    }

    function edit()
    {
        $quickhelp=!Core::lib("CMS")->expert;
        \PHPPE\View::assign("quickhelp",$quickhelp);
        return \PHPPE\View::template("cms_pagedds");
    }

    function save($params)
    {
        \PHPPE\Page::saveDDS("frame", $params['gdds']);
        return \PHPPE\Page::saveDDS($params['pageid'], $params['dds']);
    }
}

?>
