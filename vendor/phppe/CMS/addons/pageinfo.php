<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

class pageinfo extends \PHPPE\AddOn
{
    public $heightClass = "infobox";
    public $forceFull = 80;

    function load(&$app)
    {
        //! load languages
        $app->langs['']="*";
        foreach (!empty($_SESSION['pe_ls'])?$_SESSION['pe_ls']:['en'=>1] as $l=>$v)
            $app->langs[$l]=L($l);
        //! get views from database
        $rec = \PHPPE\Views::find([], "sitebuild=''", "id", "id,name");
        foreach ($rec as $r)
            $app->layouts[$r['id']] = $r['name'];
        unset($rec);
        //! add current template if it's not there
        $page = \PHPPE\View::getval("page");
        if(empty($app->layouts[$page->template]))
            $app->layouts[$page->template] = L(ucfirst($page->template));
        ksort($app->layouts);
    }

    function edit()
    {
        return \PHPPE\View::template("cms_pageinfo");
    }

    function save($params)
    {
        //! save page info
        return \PHPPE\Page::savePageInfo($params);
    }
}

?>
