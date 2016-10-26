<?php
/**
 * @file vendor/phppe/CMS/ctrl/pages.php
 * @author bzt
 * @date 26 May 2016
 * @brief list pages
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
    public $revert=false;

/**
 * default action
 */
    function action($item)
    {
		$this->revert=Core::lib("CMS")->revert;

        if(!empty($_REQUEST['pagedel'])) {
            \PHPPE\Page::delete($_REQUEST['pagedel']);
            unset($_SESSION['cms_url']);
            Http::redirect();
        }

        if(isset($_REQUEST['publish']) && $this->revert) {
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

		\PHPPE\Page::unLock(\PHPPE\Core::$user->id);
        $pages = \PHPPE\Page::getPages(intval(@$_REQUEST['order']));
		$needcleanup=false;
        foreach ($pages as $p) {
			if($this->ispublish && ($p['publishid']!=0||$p['ownerid']!=0))
				continue;
            if($this->revert && $p['publishid']==0)
                $this->needpublish=true;
			if(!$this->revert && $p['versions']>1)
				$needcleanup=true;
            $this->pages[
                empty($_REQUEST['order'])?0:(empty($p['template'])?$p['tid']:$p['template'])
                ][] = $p;
		}
		if($needcleanup)
			\PHPPE\Page::cleanUp($pages);
    }
}
