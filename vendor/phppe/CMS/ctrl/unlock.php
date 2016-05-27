<?php
/**
 * @file vendor/phppe/CMS/ctrl/unlock.php
 * @author bzt
 * @date 26 May 2016
 * @brief
 */

namespace PHPPE\Ctrl;

class CMSUnlock
{

/**
 * default action
 */
	function action($item)
	{
        //! called via AJAX, no output required
	    \PHPPE\Page::unLock(\PHPPE\Core::$user->id);
        die("OK");
	}
}
