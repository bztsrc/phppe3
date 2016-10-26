<?php
/**
 * @file vendor/phppe/CMS/ctrl/pages.php
 * @author bzt
 * @date 26 May 2016
 * @brief list pages
 */

namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\Http;
use PHPPE\Page;

class CMSPages
{
/**
 * Properties
 */
    public $pages;
    public $needpublish=false;
    public $ispublish=false;
    public $revert=false;

/**
 * default action
 */
    function action($item)
    {
		//! page history enabled?
		$this->revert=Core::lib("CMS")->revert;

		//! delete a page with all versions
        if(!empty($_REQUEST['pagedel'])) {
            Page::delete($_REQUEST['pagedel']);
            unset($_SESSION['cms_url']);
            Http::redirect();
        }

		//! publicate a page
        if(isset($_REQUEST['publish']) && $this->revert) {
			$this->ispublish=true;
			$publish=array_keys(Core::req2arr('publish'));
			if(!empty($publish)) {
            	Page::publish($publish);
            	Http::redirect("cms/pages");
            }
        }

        //! load languages
        $this->langs['']="*";
        foreach (!empty($_SESSION['pe_ls'])?$_SESSION['pe_ls']:['en'=>1] as $l=>$v)
            $this->langs[$l]=$l." ".L($l);

		//! unlock old pages for this user if any
		Page::unLock(Core::$user->id);

		//! get list of pages
        $pages = Page::getPages(intval(@$_REQUEST['order']));
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
		//! this is required once after page history turned off
		if($needcleanup)
			Page::cleanUp($pages);
    }
}
