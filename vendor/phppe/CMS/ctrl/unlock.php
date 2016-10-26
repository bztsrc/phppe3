<?php
/**
 * @file vendor/phppe/CMS/ctrl/unlock.php
 * @author bzt
 * @date 26 May 2016
 * @brief AJAX hook called to unlock page
 */

namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\Page;

class CMSUnlock
{

/**
 * default action, loaded via AJAX
 */
	function action($item)
	{
        //! called via AJAX, no output required
	    Page::unLock(Core::$user->id);
        die("<script>top.document.location.href='".url("cms/pages")."';</script>");
	}
}
