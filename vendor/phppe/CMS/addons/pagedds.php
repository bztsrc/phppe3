<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

class pagedds extends \PHPPE\AddOn
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
        return \PHPPE\View::template("cms_pagedds");
    }

    function save($params)
    {
echo("<pre>pagedds\n");
print_r($params);
die();
        return \PHPPE\Page::savePageInfo($this->name, Core::x(",", $params['value']));
    }
}

?>
