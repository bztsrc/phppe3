<?php
/**
 * @file vendor/phppe/CMS/ctrl/param.php
 * @author bzt
 * @date 26 May 2016
 * @brief
 */

namespace PHPPE\Ctrl;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Http as Http;

class CMSParam
{
    public $field;
    public $fieldTitle;
    public $w,$h;

/**
 * default action
 */
	function action($item)
	{
        //! if not called as it should, return
        if(empty($item) || empty($_SESSION['cms_url']) || empty($_SESSION['cms_param'][$item])) {
            Core::$core->template = "403";
            return;
        }
        
        //! save current scroll position to session so that on next
        //! page load cms_init will use it
        if(isset($_REQUEST['scrx']))
            $_SESSION['cms_scroll'] = [$_REQUEST['scrx'], $_REQUEST['scry']];

        //! save page
        if(Core::isTry()) {
            die("<html><script>window.parent.cms_close(true);</script></html>".$item);
        }

        //! get the page we're editing
        $page = new \PHPPE\Page($_SESSION['cms_url']);
        //! if it's a new page, save it
        if (empty($page->name)) {
            $page->name = $page->id;
            $page->save(true);
            $page->load();
        }

        //! get the field we're editing
        $F = $_SESSION["cms_param"][$item];
        $F->fld="app_value";
        if (method_exists($F, 'init')) {
            $F->init();
        }
        if (method_exists($F, 'edit')) {
            $this->field = $F->edit();
        } else {
            $this->field = "<input type='text' name='app_value' value=\"".htmlspecialchars($F->value)."\">";
        }
        $this->fieldTitle = $F->name;

        //! omit frame and panel
        Core::$core->noframe = true;
        Core::$core->nopanel = true;
        //! set values
		$this->w=intval($_REQUEST['w']);
		$this->h=intval($_REQUEST['h']);

//        die("Url: ".$_SESSION['cms_url']);
//die("<html><script>window.parent.cms_close(true);</script></html>".$item);
	}
}
