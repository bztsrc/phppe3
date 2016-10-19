<?php
/**
 * @file vendor/phppe/CMS/ctrl/pages.php
 * @author bzt
 * @date 26 May 2016
 * @brief
 */

namespace PHPPE\Ctrl;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Http as Http;

class CMSPages
{
    public $pages;
    public $needpublish=false;
    public $ispublish=false;

/**
 * default action
 */
    function action($item)
    {
        if(!empty($_REQUEST['pagedel'])) {
            \PHPPE\Page::delete($_REQUEST['pagedel']);
            Http::redirect();
        }

        if(isset($_REQUEST['publish'])) {
			$this->ispublish=true;
			$publish=array_keys(Core::req2arr('publish'));
			if(!empty($publish)) {
            	\PHPPE\Page::publish($publish);
            	Http::redirect("cms/pages");
            }
        }

        //! load languages
        $this->langs['']="*";
        foreach (!empty($_SESSION['pe_ls'])?$_SESSION['pe_ls']:['en'=>1] as $l=>$v)
            $this->langs[$l]=$l." ".L($l);

        $pages = \PHPPE\Page::getPages(intval(@$_REQUEST['order']));
        foreach ($pages as $p) {
			if($this->ispublish && $p['publishid']!=0)
				continue;
            if($p['publishid']==0)
                $this->needpublish=true;
            $this->pages[
                empty($_REQUEST['order'])?0:(empty($p['template'])?$p['tid']:$p['template'])
                ][] = $p;
		}
    }
}
