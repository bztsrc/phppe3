<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

class pageinfo extends \PHPPE\AddOn
{
    public $heightClass = "infobox";
    public $headerHeight = 30;
    public $forceFull = 80;

    function init()
    {
        $this->name="";
    }

    function load(&$app)
    {
        //! load languages
        $app->langs['']=L("Any");
        foreach (!empty($_SESSION['pe_ls'])?$_SESSION['pe_ls']:['en'=>1] as $l=>$v)
            $app->langs[$l]=L($l);
        //! get views from database
        $rec = \PHPPE\Views::find([], "", "id", "id,name");
        foreach ($rec as $r)
            $app->layouts[$r['id']] = $r['name'];
        unset($rec);
        //! add views from file system
        $rec = glob("vendor/phppe/*/views/*.tpl");
        usort($rec, function($a, $b) {
            return strcmp(basename($a), basename($b));
        });
        foreach($rec as $r) {
            $f = basename($r);
            $f = substr($f, 0, strlen($f)-4);
            if(empty($app->layouts[$f]))
                $app->layouts[$f] = L(ucfirst($f));
        }
        $page = \PHPPE\View::getval("page");
        $page->meta = $page->data['meta'];
        $page->header = "Page Meta Information";
    }

    function edit()
    {
        return \PHPPE\View::template("cms_pageinfo");
    }

    function save($params)
    {
echo("<pre>pageinfo\n");
print_r($params);
die();
        return \PHPPE\Page::savePageInfo($this->name, Core::x(",", $params['value']));
    }
}

?>
