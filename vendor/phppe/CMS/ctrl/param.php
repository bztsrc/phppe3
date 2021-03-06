<?php
/**
 * @file vendor/phppe/CMS/ctrl/param.php
 * @author bzt
 * @date 26 May 2016
 * @brief edit page parameter
 */

namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\Page;
use PHPPE\ClassMap;

class CMSParam
{
/**
 * Properties
 */
    public $field = "";
    public $fieldTitle = "";
    public $editable = false;
    public $height=0;
    public $adjust=0;
    public $ace=[];

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
        if(empty($item) || ($item!=sha1("pageadd_") && (empty($_SESSION['cms_url']) || empty($_SESSION['cms_param'][$item])))) {
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

		//! get available access control entries
		$this->ace = ClassMap::ace();
		foreach($this->ace as $k=>$v)
			$this->ace[$k]="@".$v;
		$this->ace[] = "@siteadm|webadm";
		$this->ace[] = "loggedin";
		$this->ace[] = "csrf";
		$this->ace[] = "get";
		$this->ace[] = "post";

        //! get the field we're editing
        $F = clone $_SESSION["cms_param"][$item];
        $F->fld="page_value";
        if(get_class($F)=="PHPPE\\AddOn\\wyswyg")
        	$F->args=[0,"pe.cms.image"];
        if (method_exists($F, 'init')) {
            $F->init();
        }
        $this->fieldTitle = $F->name;
        $this->heightClass = @$F->heightClass;
        $this->boxHeight = $this->height-@$F->headerHeight;

        //! get the page we're editing
        //! if parameter name starts with "frame", load frame page instead
        $page = new Page(substr($F->name,0,6)=="frame." ? "frame" : @$_SESSION['cms_url']);
        $this->editable = $page->lock();

        View::assign("page", $page);
        $n = substr($F->name,0,6)=="frame." ? substr($F->name,6) : (substr($F->name,0,4)=="app." ? substr($F->name,4) : $F->name);
        if(!empty($page->data[$n]))
            $F->value=$page->data[$n];
        //! load extra data if any
        if (method_exists($F, 'load')) {
            $F->load($this);
        }

        //! save page parameter
        $param = Core::req2arr("page");
        if (!empty($param) && $this->editable) {
            //! if there was no validation error
            if (!Core::isError())
            {
                if (method_exists($F, "save")) {
                    //! if it's a special field with it's own save mechanism
                    $param['pageid'] = $page->id;
                    if(!$F->save($param))
                        Core::error(L("Unable to save page!"));
                } else {
                    //! otherwise standard page parameter
                    $page->setParameter($F->name, $param['value']);
                    if(!$page->save())
                        Core::error(L("Unable to save page!"));
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
        View::js("init()", "var inp=document.querySelector('.reqinput,.input');if(inp!=null){inp.focus();inp.selectionStart=inp.selectionEnd=(inp.value!=null?inp.value:inp.innerHTML).length;}", true);
    }
}
