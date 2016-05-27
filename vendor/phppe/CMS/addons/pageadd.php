<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

class pageadd extends \PHPPE\AddOn\pageinfo
{
    public $heightClass = "infobox";
    public $headerHeight = 30;
    public $forceFull = 80;

    function init()
    {
        $this->name="";
    }

    function edit()
    {
        $page = new \PHPPE\Page;
        $page->header = "Add New Page";
        \PHPPE\View::assign("page", $page);

        return \PHPPE\View::template("cms_pageinfo");
    }

    function save($params)
    {
echo("<pre>pageadd\n");
print_r($params);
die();
        return \PHPPE\Page::savePageInfo($this->name, Core::x(",", $params['value']));
    }
}

?>
