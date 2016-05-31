<?php
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;
use \PHPPE\View as View;

class setsel extends \PHPPE\AddOn {

	function init()
	{
		Core::addon( "setsel", "Set list selection", "", "*(height,filters,itemtemplate,header,titlefield) obj.field options [cssclass [itemcssclass [filterhtml]]]" );
	}
	function show()
	{
		return "";
	}

	function edit()
	{
		View::jslib( "setsel.js", "setsel_search('".addslashes($this->fld)."');");
		$out=[0=>"",1=>""];
		$a = $this->attrs;
		$opts = ! empty($a[ 0 ]) && $a[ 0 ] != "-" ? View::getval($a[ 0 ]) : [];
		if(is_string($opts))
			$opts = explode(",", $opts);
		if(is_string($this->value))
			$val = explode(",", $this->value);
		elseif(isset($this->value[0]['id'])) {
            $val=[];
            foreach($this->value as $k=>$v)
                $val[$k]=$v['id'];
        } else
			$val = $this->value;
		if(empty($val))
			$val = [];
        $i=array_flip($val);
        $b=[];
		if(!empty($this->args[1])){
		    if(!is_array($this->args[1]))
			$f=array_flip(explode(",",$this->args[1]));
		    else
			$f=$this->args[1];
		} else $f=[];
		$flt=[]; $filters=[]; $idx=[];
		foreach($f as $k=>$v) {
			$d=explode(":",$k);
			if(!empty($d[1])) {
				$filters[$d[0]]=$v;
				$idx[$d[0]]=$d[1];
			}
			else
				$filters[$d[0]]=$v;
		}
		foreach($opts as $k=>$v) {
			$id=$k; $name=$v; $title="";
			$blk="<div class='setsel_item".(!empty($a[2])&&$a[2]!="-"?" ".$a[2]:"")."'";
			$rep=!empty($this->args[2])?$this->args[2]:"%name%";
			if(is_array($v)||is_object($v)) {
				foreach($v as $K=>$V) {
					if(isset($filters[$K]))
						$flt[$K][$V]=!empty($idx[$K])&&!empty($v[$idx[$K]])?$v[$idx[$K]]:$V;
					$rep=str_ireplace("%".$K."%",$V,$rep);
					if(!empty($this->args[4]) && $K==trim($this->args[4])) $title=$V;
					if($K=="name") $name=$V; else
					if($K=="id") $id=$V; else
						$blk.=" data-".$K."=\"".htmlspecialchars($V)."\"";
				}
			}
            if($title)
                $blk.=" title='".htmlspecialchars($title)."'";
			$blk.=" data-id='".htmlspecialchars($id)."' draggable='false' onmousedown='return setsel_drag(event,\"".$this->fld."\");':display>".$rep."</div>";
			if(isset($i[$id]))
				$b[$i[$id]]=str_replace(":display","",$blk);
			$out[1].=str_replace(":display",(isset($i[$id])?" data-inlist='1' style='display:none;'":""),$blk);
		}
		ksort($b);
		if(isset($flt['lang'])) {
			unset($flt['lang']);
			foreach($_SESSION['pe_ls'] as $l=>$v)
				$flt['lang'][$l]=$l." ".L($l);
		}
		$flthtml=!empty($a[3])?$a[3]:"";
		foreach($filters as $f=>$v) if($f) {
			$flthtml.="<select class='setsel_input' name='".$f."' onchange='setsel_search(\"".$this->fld."\");' style='margin-right:5px;'><option value=''>*</option>";
			if(is_array($flt[$f])) foreach($flt[$f] as $F=>$V)
				if(!empty($F) && !empty($V))
					$flthtml.="<option value=\"".htmlspecialchars($F)."\"".(@$_REQUEST['setsel_'.$f]==$F?" selected":"").">".L($V)."</option>";
			$flthtml.="</select>";
		}
		$out[0]=implode("",$b);
		return "<div class='setsel'><input type='hidden' id='".$this->fld."' name='".$this->fld."' value='".htmlspecialchars(implode(",", $val))."'>".
		"<div id='".$this->fld.":filters' class='setsel_filters'>".(!empty($this->args[3])?"<span class='setsel_title' style='float:left;line-height:22px !important;'>".$this->args[3]."</span>":"").$flthtml.
		"<input name='search' class='setsel_input' type='text' placeholder='".L("search")."' onchange='setsel_search(\"".$this->fld."\");' onkeyup='setsel_search(\"".$this->fld."\");'>".
		"<span style='font-size:20px;padding-left:5px;padding-right:5px;'>⌕</span><br style='clear:both;'/></div>\n".
		"<div class='".$this->css." ".(!empty($a[1])&&$a[1]!="-"?$a[1]:"")." setsel_box' onmouseover='setsel_droparea(event);' onmouseup=\"dnd_drop(event,'setsel_add');\" id='".$this->fld.":inlist' style='height:".intval(!empty($this->args[0])?$this->args[0]:128)."px;padding-bottom:64px;box-sizing:border-box;overflow:auto;'>".$out[0]."</div>".
		"<div class='".$this->css." ".(!empty($a[1])&&$a[1]!="-"?$a[1]:"")." setsel_box' onmouseup=\"dnd_drop(event,'setsel_remove');\" id='".$this->fld.":all' style='height:".intval(!empty($this->args[0])?$this->args[0]:128)."px;box-sizing:border-box;overflow:auto;'>".$out[1]."</div></div>";
	}
}

?>
