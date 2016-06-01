<?php
/**
 * Addon for page lists
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

class doclist extends \PHPPE\AddOn\setsel
{
    public $heightClass = "setsel_box";
    public $headerHeight = 30;
    public $forceFull = 80;

	function init()
	{
		\PHPPE\Core::addon( "doclist", "Document List Selector", "", "*obj.field options [cssclass]" );
	}

	function edit()
	{
		$this->args[0]=0;
		$this->args[1]=0;
		$this->args[2]='';//'lang,tid:template';
		$this->args[3]="<img src='images/datalibrary/%type%.png' style='float:left;padding-right:3px;'>%name%<br><small>%size% bytes</small><br style='clear:both;'>";
		$this->args[4]=L("Edit Document List");
        $this->args[5]="id";
		$this->attrs[0]=\PHPPE\DataLibrary::getDocuments();
        $this->attrs[3]="<input type='file' name='doclist_upload' onchange='this.form.submit();' style='display:none;'>".
        "<input type='button' value='Upload' class='setsel_button' onclick=\"this.form['pe_f'].value='doclist';this.form['doclist_upload'].click();\">";
		return parent::edit();
	}

    //! to load use DDS: id, doc_list, list_id='@ID', , ordering
    function save($params)
    {
        return \PHPPE\DataLibrary::saveDocumentList($this->name, str_getcsv($params['value'], ','));
    }
}

?>
