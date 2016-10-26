<?php
/**
 * @file vendor/phppe/CMS/ctrl/layouts.php
 * @author bzt
 * @date 26 May 2016
 * @brief list and edit layouts
 */

namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\Http;
use PHPPE\Views;
use PHPPE\Page;
use PHPPE\DS;

class CMSLayouts
{
/**
 * default action
 */
	function action($item)
	{
		//! create a fake page parameter
		$name="layoutadd";
		$_SESSION['cms_param'][sha1("layoutadd_")] = new \PHPPE\AddOn\layoutadd([],$name,$name);
		//! if layout not given
		if(empty($item)){
			//! check if we have to activate a sitebuild
            if(!empty($_REQUEST['set'])) {
                DS::exec("UPDATE ".Views::$_table." SET id=sitebuild WHERE sitebuild!='' AND id='frame'");
                DS::exec("UPDATE ".Views::$_table." SET id='frame' WHERE sitebuild=?",trim($_REQUEST['set']));
                Http::redirect();
            }
            //! load layouts and sitebuilds
			$this->layouts = Views::find([],"sitebuild=''","name");
			$this->sitebuilds = Views::find([],"sitebuild!=''","name");
		} else {
			//! load layout
			$this->layout = new Views($item);
			if(!empty($this->layout->jslib))
				foreach($this->layout->jslib as $j)
					View::jslib($j);
			if(!empty($this->layout->css))
				foreach($this->layout->css as $c)
					View::css($c);
			$this->numPages = Page::getNum($item);
			//! get user input
			$layout=Core::req2arr("layout");
			//! merge the new data with the loaded layout's properties
			if(!empty($this->layout->sitebuild) && !empty($layout)) {
				Core::$core->noframe=1;
				$layout['sitebuild']=$layout['id'];
			}
			if(Core::isTry("layout")) {
				//! delete a layout
				if(!empty($layout['delete'])){
					$this->layout->delete();
				} else {
					unset($layout['delete']);
					//! renamed?
					if($this->layout->id!=$layout['id']) {
						DS::exec("UPDATE ".Views::$_table." SET id=? WHERE id=?",[$layout['id'],$this->layout->id]);
						Core::log('A',sprintf("Layout %s renamed to %s by %s",$this->layout->id,$layout['id'],Core::$user->name), "cmsaudit");
					}
					//! save new data
					foreach($layout as $k=>$v)
						$this->layout->$k=$v;
					$this->layout->save();
				}
				Http::redirect("cms/layouts");
			}
		}
	}
}
