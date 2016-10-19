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
    public $forceFull = 60;

    function load(&$app)
    {
        $app->versions = \PHPPE\DS::query(
            "a.*,b.name as moduser,c.name as pubuser, CURRENT_TIMESTAMP as ct",
            "pages a left join users b on a.modifyid=b.id left join users c on a.publishid=c.id",
            "a.id=? AND (a.lang='' OR a.lang=?)",
            "", "a.created DESC", 0, 0, [$_SESSION['cms_url'], \PHPPE\Core::$client->lang]);
        foreach($app->versions as $k=>$v)
            if(!empty($v['publishid'])) {
                $app->versions[$k]['hdr']=1;
                break;
            }
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
