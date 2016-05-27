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
    public $field = "";
    public $fieldTitle = "";
    public $editable = false;
    public $height=0;
    public $page;

/**
 * default action
 */
    function action($item)
    {
        //! omit frame and panel and disable cache
        Core::$core->noframe = true;
        Core::$core->nopanel = true;
        Core::$core->nocache = true;

        //! if not called as it should, return
        if(empty($item) || empty($_SESSION['cms_url']) || empty($_SESSION['cms_param'][$item])) {
            Core::$core->template = "403";
            return;
        }

        //! get height
        $this->height=intval($_REQUEST['height']);
        
        //! save current scroll position to session so that on next
        //! page load cms_init will use it
        if(isset($_REQUEST['scrx']))
            $_SESSION['cms_scroll'] = [$_REQUEST['scrx'], $_REQUEST['scry']];

        //! get the field we're editing
        $F = clone $_SESSION["cms_param"][$item];
        $F->fld="page_value";
        if (method_exists($F, 'init')) {
            $F->init();
        }
        $this->fieldTitle = $F->name;
        $this->heightClass = @$F->heightClass;
        $this->boxHeight = $this->height-@$F->headerHeight;

        //! get the page we're editing
        //! if parameter name starts with "frame", load frame page instead
        $page = new \PHPPE\Page(substr($F->name,0,5)=="frame" ? "frame" : $_SESSION['cms_url']);
        //! if it's a new page, save it
        if (empty($page->name)) {
            $page->name = ucfirst($page->id);
            $page->save(true);
            $page->load();
        }
        $this->editable = $page->lock();

        \PHPPE\View::assign("page", $page);
        //! load extra data if any
        if (method_exists($F, 'load')) {
            $F->load($this);
        }

        //! get the input(s)
        if (method_exists($F, 'edit')) {
            $this->field = $F->edit();
        } else {
            //! fallback to a simple input field. Should never happen
            $this->field = "<input type='text' name='page_value' value=\"".htmlspecialchars($F->value)."\">";
        }

        //! save page parameter
        if (Core::isTry() && $this->editable) {
            $param = Core::req2arr("page");
            if (method_exists($F, "save")) {
                //! if it's a special field with it's own save mechanism
                $param['pageid'] = $page->id;
                $F->save($param);
            } else {
                //! otherwise standard page parameter
                $page->setParameter($F->name, $param['value']);
            }
            //! release the page lock
            $page->release();
            //! close the modal
            die("<html><script>window.parent.cms_close(true);</script></html>");
        }
    }
}
