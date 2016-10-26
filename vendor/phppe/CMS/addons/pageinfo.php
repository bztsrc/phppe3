<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\Views;

// L("pageinfo")
class pageinfo extends \PHPPE\AddOn
{
    public $heightClass = "infobox";
    public $forceFull = 50;

    function load(&$app)
    {
        //! load languages
        $app->langs['']="*";
        foreach (!empty($_SESSION['pe_ls'])?$_SESSION['pe_ls']:['en'=>1] as $l=>$v)
            $app->langs[$l]=L($l);
        //! get views from database
        $rec = Views::find([], "sitebuild=''", "id", "id,name");
        foreach ($rec as $r)
            $app->layouts[$r['id']] = $r['name'];
        foreach(glob("app/views/*.tpl") as $view) {
            $w=str_replace(".tpl","",basename($view));
            if($w!="frame")
                $app->layouts[$w] = ucfirst($w);
        }
        unset($rec);
        //! add current template if it's not there
        $page = View::getval("page");
        if(empty($app->layouts[$page->template]))
            $app->layouts[$page->template] = L($page->template)==$page->template?ucfirst($page->template):L($page->template);
        ksort($app->layouts);
    }

    function edit()
    {
        $quickhelp=!Core::lib("CMS")->expert;
        View::assign("quickhelp",$quickhelp);
        return View::template("cms_pageinfo");
    }

    function save($params)
    {
        //! save page info
        return Page::savePageInfo($params);
    }
}

?>
