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
    public $adjust=0;

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
        $this->height=intval(@$_REQUEST['height']);
        $this->adjust=intval(@$_REQUEST['adjust']);

        //! save current scroll position to session so that on next
        //! page load pe.cms.init() will use it
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
		$F->value = $page->data[$_SESSION['cms_param'][$item]->name];
        //! if it's a new page, save it
        if (empty($page->name) && empty($page->template)) {
            $page->name = ucfirst($page->id);
            $page->template = "simple";
            $page->save(true);
            $page->load();
        }
        $this->editable = $page->lock();

        \PHPPE\View::assign("page", $page);
        //! load extra data if any
        if (method_exists($F, 'load')) {
            $F->load($this);
        }

        //! save page parameter
        if (Core::isTry("page") && $this->editable) {
            $param = Core::req2arr("page");
            //! if there was no validation error
            if (!Core::isError())
            {
                if (method_exists($F, "save")) {
                    //! if it's a special field with it's own save mechanism
                    $param['pageid'] = $page->id;
                    if(!$F->save($param))
                        \PHPPE\Core::error(L("Unable to save page"));
                } else {
                    //! otherwise standard page parameter
                    $page->setParameter($F->name, $param['value']);
                    if(!$page->save())
                        \PHPPE\Core::error(L("Unable to save page"));
                }
                //! close the modal if save was successful
                if (!Core::isError()) {
                    //! release the page lock
                    $page->release();
                    die("<html><script>parent.pe.cms.close(true);</script></html>");
                }
            }
            //! copy the form data. normally you don't need to do that
            //! but here form name and object name differs, so it's not automatic
            foreach($param as $k=>$v)
                $page->$k = $v;
        }
        //! get the input(s)
        if (method_exists($F, 'edit')) {
            $this->field = $F->edit();
        } else {
            //! fallback to a simple input field. Should never happen
            $this->field = "<input type='text' class='input".(Core::isError("page.value")?" errinput":"")."' name='page_value' value=\"".htmlspecialchars($F->value)."\">";
        }

        //! focus first input
        \PHPPE\View::js("init()", "var inp=document.querySelector('.reqinput,.input');if(inp!=null){inp.focus();inp.selectionStart=inp.selectionEnd=(inp.value!=null?inp.value:inp.innerHTML).length;}", true);
    }
}
