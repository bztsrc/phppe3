<?php
/**
 * Addon to select dynamic data sets for a page
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as PHPPE;

class cmsdds extends \PHPPE\AddOn
{
	function init()
	{
		PHPPE::addon("cmsdds", "CMS DDS Selector", "", "*");
	}

	function show(  )
	{
		$v=is_array($this->value)?$this->value:json_decode($this->value,true);
		$r="<table><tr><th>".L("Name")."</th><th>".L("SELECT")."</th><th>".L("FROM")."</th><th>".L("WHERE")."</th><th>".L("GROUP BY")."</th><th>".L("ORDER BY")."</th><th></th></tr>";
		if(!empty($v)) foreach($v as $k=>$m) {
			$r.="<tr><td>".$k.":</td>";
			foreach($m as $idx=>$val)
				$r.="<td>".htmlspecialchars(!empty($val)?$val:'')."</td>";
			$r.="</tr>";
		}
		$r.="</table>";
		return $r;
	}

	function edit(  )
	{
		$v=is_array($this->value)?$this->value:json_decode($this->value,true);
		$r="<input type='hidden' name='".$this->fld."'><table width='100%'><tr><td></td><td>".L("SELECT")."</td><td>".L("FROM")."</td><td>".L("WHERE")."</td><td>".L("GROUP BY")."</td><td>".L("ORDER BY")."</td></tr>";
		if(!empty($v)) foreach($v as $k=>$m) {
			$r.="<tr><td width='1' style='min-width:100px;background:rgba(160,160,160,0.5);'>".$k."</td>";
			foreach($m as $idx=>$val)
				$r.="<td width='*' style='padding:0px;'><input style='width:95%;margin-left:0px;' class='input' name='".$this->fld.":".$k."_".$idx."' value=\"".htmlspecialchars(!empty($val)?$val:'')."\"></td>";
			$r.="<td><nobr><img src='images/cms/pagedelete.png' onclick='cms_cleardds(this);' style='cursor:pointer;padding-right:5px;'><img src='images/cms/pageadd.png' onclick='cms_clonedds(this);' style='cursor:pointer;'></nobr></td></tr>";
		}
			$r.="<tr><td width='1'><input style='min-width:100px;' class='input' name='".$this->fld.":_name' value=''></td>";
			for($m=0;$m<5;$m++)
				$r.="<td width='*' style='padding:0px;'><input style='width:95%;margin-left:0px;' class='input' name='".$this->fld.":_".$m."' value=''></td>";
			$r.="<td style='width:37px;'>&nbsp;</td></tr>";
		$r.="</table>";
		return $r;
	}
}

?>