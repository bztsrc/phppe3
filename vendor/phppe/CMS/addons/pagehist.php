<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

// L("pagehist")
class pagehist extends \PHPPE\AddOn
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
        return \PHPPE\View::template("cms_pagehist");
    }

    function save($params)
    {
echo("<pre>");
print_r($params);
die();
        return \PHPPE\Page::savePageInfo($this->name, str_getcsv($params['value'], ','));
    }
}

?>
