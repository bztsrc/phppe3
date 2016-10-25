<?php
/**
 * Addon to select dynamic data sets for a page
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

class cmsdds extends \PHPPE\AddOn
{
	public $conf="*";

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
		$lists=["pages_list"=>L("Page list"),"img_list"=>L("Picture list"),"doc_list"=>L("Document list")];
        $v=is_array($this->value)?$this->value:json_decode($this->value,true);
        $r="<input type='hidden' name='".$this->fld."'><table width='100%'><tr><td></td><td>".L("SELECT")."</td><td>".L("FROM")."</td><td>".L("WHERE")."</td><td>".L("GROUP BY")."</td><td>".L("ORDER BY")."</td></tr>";
        if(!empty($v)) foreach($v as $k=>$m) {
            $r.="<tr><td width='1' style='min-width:100px;border:1px solid #202020;padding:2px;background:rgba(160,160,160,0.5);'>".$k."</td>";
			$l=explode(" ",$m[1])[0];
			$f=$h="";
			if(!empty($lists[$l])) {
				$f="<select onchange='cms_listhide(this);' onblur='cms_listhide(this);' style='width:95%;margin-left:0px;' class='input form-control'><option value=''>*</option>";
				foreach($lists as $n=>$li)
					$f.="<option value='".htmlspecialchars($n)."'".($l==$n?" selected":"").">".$li."</option>";
				$f.="</select>";
				$h=1;
			}
            foreach($m as $idx=>$val) {
                $r.="<td width='*' style='padding:0px;'>".$f."<input style='width:95%;margin-left:0px;".($h?"display:none;":"")."' class='input form-control' name='".$this->fld."[".$k."][".$idx."]' value=\"".htmlspecialchars(!empty($val)?$val:'')."\"".($f?" onkeydown='cms_listshow(this);'":"")."></td>";
				$f="";
			}
            $r.="<td><nobr><img src='images/cms/pagedel.png' onclick='cms_cleardds(this);' style='cursor:pointer;padding:2px;'><img src='images/cms/pageadd.png' onclick='cms_clonedds(this);' style='cursor:pointer;padding:2px;'></nobr></td></tr>";
        }
        $r.="<tr><td width='1' style='padding-right:3px;'><input style='min-width:100px;' class='input form-control' name='".$this->fld."[_][name]' value=''></td>";
        for($m=0;$m<5;$m++)
            $r.="<td width='*' style='padding:0px;'><input style='width:95%;margin-left:0px;' class='input form-control' name='".$this->fld."[_][".$m."]' value=''></td>";
            $r.="<td style='width:37px;'>&nbsp;</td></tr>";
        $r.="</table>";
        \PHPPE\View::js("cms_cleardds(obj)",
            "obj.parentNode.parentNode.parentNode.getElementsByTagName('INPUT')[0].value='';".
            "obj.parentNode.parentNode.parentNode.style.display='none';"
        );
        \PHPPE\View::js("cms_clonedds(obj)",
            "var i,tbl=obj.parentNode;".
            "while(tbl.tagName!='TABLE') tbl=tbl.parentNode;".
            "var inps=tbl.rows[tbl.rows.length-1].getElementsByTagName('INPUT');".
            "var orig=obj.parentNode.parentNode.parentNode.getElementsByTagName('INPUT');".
            "for(i=0;i<orig.length;i++) inps[i+1].value=orig[i].value;".
            "inps[0].value='New'; inps[0].select(); inps[0].focus();"
        );
        \PHPPE\View::js("cms_listhide(obj)",
            "var i,orig=obj.parentNode.parentNode.getElementsByTagName('INPUT');console.log('value',obj.selectedIndex,obj.value);".
            "if(obj.selectedIndex==0||obj.value==null||obj.value==''){".
			"obj.setAttribute('style','display:none;');for(i=0;i<orig.length;i++) orig[i].style.display='block';".
            "}else{".
			"if(obj.value=='pages_list'){orig[0].value='b.*';orig[1].value='pages_list a left join pages b on a.page_id=b.id and b.created=(SELECT MAX(c.created) FROM pages c WHERE c.id=b.id)';orig[2].value=\"a.list_id='@ID'\";orig[3].value='';orig[4].value='ordering';}".
			"if(obj.value=='img_list'){orig[0].value='id';orig[1].value='img_list';orig[2].value=\"list_id='@ID'\";orig[3].value='';orig[4].value='ordering';}".
			"if(obj.value=='doc_list'){orig[0].value='id';orig[1].value='doc_list';orig[2].value=\"list_id='@ID'\";orig[3].value='';orig[4].value='ordering';}".
            "}"
        );
        \PHPPE\View::js("cms_listshow(obj)",
            "var i,orig=obj.parentNode.parentNode.getElementsByTagName('INPUT');".
            "if(obj.value==null||obj.value==''){".
			"obj.previousSibling.setAttribute('style','display:block;');for(i=0;i<orig.length;i++) orig[i].style.display='none';}"
        );
        return $r;
    }
}

?>
