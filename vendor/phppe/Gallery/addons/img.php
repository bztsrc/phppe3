<?php
/**
 * Addon for page lists
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

class img extends \PHPPE\AddOn\setsel
{
    public $heightClass = "setsel_box";
    public $headerHeight = 30;
    public $forceFull = 80;

	function init()
	{
		\PHPPE\Core::addon( "img", "Image Selector", "", "*(itemheight) obj.field options [cssclass]" );
	}

	function edit()
	{
		$h=!empty($this->args[0])?intval($this->args[0]):64;

		$this->args[0]=1;
		$this->args[1]=0;
		$this->args[2]='';
		$this->args[3]="<img src='gallery/%id%' alt='%id%' height='".$h."' style='margin:2px;'>";
		$this->args[4]=L("Select Image");
        $this->args[5]="id";
		$this->attrs[0]=\PHPPE\Gallery::getImages();
        $this->attrs[2]="setsel_img";
        $this->attrs[3]="<input type='file' name='imglist_upload' onchange='this.form.submit();' style='display:none;'>".
        "<input type='button' value='Upload' class='setsel_button' onclick=\"this.form['pe_f'].value='imglist';this.form['imglist_upload'].click();\">";
		return parent::edit();
	}

    //! to load use DDS: id, img_list, list_id='@ID', , ordering
    function save($params)
    {
        return \PHPPE\Gallery::saveImageList($this->name, Core::x(",", $params['value']));
    }
}

?>
