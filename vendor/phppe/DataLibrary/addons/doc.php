<?php
/**
 * Addon for page lists
 */
namespace PHPPE\AddOn;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\DataLibrary;

class doc extends \PHPPE\AddOn\setsel
{
    public $heightClass = "setsel_box";
    public $headerHeight = 30;
    public $forceFull = 80;
    public $conf = "*obj.field dataset [cssclass]";

	function edit()
	{
		$this->args[0]=1;
		$this->args[1]=0;
		$this->args[2]='';//'lang,tid:template';
		$this->args[3]="<img src='images/datalibrary/%type%.png' style='pointer-events:none;float:left;padding-right:3px;'>%name%<br><small style='pointer-events:none;'>%hsize%bytes</small><br style='clear:both;'>";
		$this->args[4]=L("Select Document");
        $this->args[5]="id";
		$this->attrs[0]=DataLibrary::getDocuments();
        $this->attrs[3]="<input type='file' name='doclist_upload' onchange='this.form.submit();' style='display:none;'>".
        "<input type='button' value='".L("Upload")."' class='setsel_button' onclick=\"this.form['pe_f'].value='doclist';this.form['doclist_upload'].click();\">";
		return parent::edit();
	}

    function save($params)
    {
        $page=View::getval("page");
        $page->setParameter($this->name, str_getcsv($params['value'], ',')[0]);
        return $page->save();
    }
}

?>
