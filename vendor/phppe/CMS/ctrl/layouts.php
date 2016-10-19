<?php
/**
 * @file vendor/phppe/CMS/ctrl/layouts.php
 * @author bzt
 * @date 26 May 2016
 * @brief
 */

namespace PHPPE\Ctrl;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Http as Http;

class CMSLayouts
{
/**
 * default action
 */
	function action($item)
	{
		$name="layoutadd";
		$_SESSION['cms_param'][sha1("layoutadd_")] = new \PHPPE\AddOn\layoutadd([],$name,$name);
		if(empty($item)){
			$this->layouts = \PHPPE\Views::find([],"sitebuild=''","name");
			$this->sitebuilds = \PHPPE\Views::find([],"sitebuild!=''","name");
		} else {
			$this->layout = new \PHPPE\Views($item);
			$this->numPages = \PHPPE\Page::getNum($item);
			$layout=Core::req2arr("layout");
			if(!empty($this->layout->sitebuild)) {
				Core::$core->noframe=1;
				$layout['sitebuild']=$layout['name'];
			}
			if(Core::isTry("layout")) {
				if(!empty($layout['delete'])){
					$this->layout->delete();
				} else {
					unset($layout['delete']);
					\PHPPE\Core::log('A',sprintf(L("Layout %s modified by %s"),$this->id,\PHPPE\Core::$user->name), "cmsaudit");
					$this->layout = new \PHPPE\Views($layout);
					$this->layout->save();
				}
				Http::redirect("cms/layouts");
			}
		}
	}
}
