<?php
/**
 * @file vendor/phppe/CMS/ctrl/layouts.php
 * @author bzt
 * @date 26 May 2016
 * @brief
 */

namespace PHPPE\Ctrl;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Http as Http;

class CMSTag
{
/**
 * default action, loaded via AJAX
 */
    function action($item)
    {
			$list = [
            "/form"=>"*variable [url [onsubmitjs",	//L("help_form")
            "/if"=>"*expression",					//L("help_if")
            "else"=>"*",							//L("help_else")
            "/foreach"=>"*dataset",					//L("help_foreach")
            "/template"=>"*",						//L("help_template")
            "include"=>"*view",						//L("help_include")
            "app"=>"*",								//L("help_app")
            "dump"=>"variable",						//L("help_dump")
            "cms"=>"*addon ) variable",				//L("help_cms")
            "="=>"expression",						//L("help_eval")
            "L"=>"label",							//L("help_L")
            "date"=>"expression",					//L("help_date")
            "time"=>"expression",					//L("help_time")
            "difftime"=>"expression",				//L("help_difftime")
            "var"=>"*addon ) variable",				//L("help_var")
            "field"=>"*addon ) variable",			//L("help_field")
            "widget"=>"*addon ) variable",			//L("help_widget")
            ];
            $d=array_merge(get_declared_classes(),array_keys(\PHPPE\ClassMap::$map));
			foreach($d as $c) {
				if(strtolower(substr($c,0,12))=="phppe\\addon\\") {
					$F=new $c([],"dummy",$c,[]);
					if(isset($F->conf) && $F->conf!="*")
						$list["_".strtolower(substr($c,12))]=$F->conf;
					unset($F);
				}
			}
		if(!empty($item)){
			//! edit form
			$acl=$widget="";$req=$needsel=0;
			if(substr($item,0,2)!="<!") {
				die(\PHPPE\View::e("E",L("Unknown tag")));
			} else {
				$d="";$c="";
				foreach($list as $k=>$v) {
					if($k[0]=="_")
						continue;
					if(substr($item,2,strlen($k))==$k||"/".substr($item,2,strlen($k)-1)==$k) {
						$d=$k[0]=="/"?substr($k,1):$k;
						$c=$v[0]=='*'?substr($v,1):$v;
					}
				}
				if(empty($d))
					die(\PHPPE\View::e("E",L("Unknown tag")));
				if($d=="=") {
					$d="eval";
					$a=[substr($item,3,strlen($item)-4)];
				} else {
					$a=str_getcsv(preg_replace("/[\ ]+/"," ",strtr(substr($item,2,strlen($item)-3),["(,"=>" - ","("=>" ",")"=>" )",",,"=>" - ",","=>" "]))," ");
					array_shift($a);
				}
				if(substr($c,0,5)=="addon") {
					if(@$a[0][0]=="@") {
						$acl=substr($a[0],1);
						array_shift($a);
					}
					if(@$a[0][0]=="*") {
						$req=1;
						$a[0]=substr($a[0],1);
					}
					$widget=array_shift($a);
					if(empty($widget)) $widget="hidden";
					$needsel=1;
				}
				echo("<b>".L(!empty($widget)&&!empty(Core::$l[$widget])?$widget:"help_".$d)."</b><br/>\n<div id='tageditor' style='padding:5px;'><input type='hidden' name='tag' value='".htmlspecialchars($d)."'>\n");
				if(substr($c,0,5)=="addon") {
					$t=$d=="cms"?L("Show value"):L("Required value");
					echo("<input type='checkbox' class='input' name='required' onchange='pe.cms.settag(\"tageditor\");' title=\"".htmlspecialchars($t)."\" value='*'".($req?" checked":"").">\n");
					echo("<select class='input' name='widget' onchange='pe.cms.settag(\"tageditor\");pe.wyswyg.popup(event,\"layout_data\",\"cms/tag?item=".urlencode("<!".$d." ".($req?"*":""))."\"+this.value+\">\",true);' onmouseover='pe_w();'>");
					foreach($list as $k=>$v) {
						if($k[0]!="_")
							continue;
						echo("<option value='".htmlspecialchars(substr($k,1))."'".(substr($k,1)==$widget?" selected":"")." onmouseover='pe_w();'>".L(substr($k,1))."</option>\n");
					}
					echo("</select>\n<input type='text' class='input smallinput' name='acl' onkeydown='if(event.key==\"Enter\"){event.preventDefault();pe_p();}' onkeyup='pe.cms.settag(\"tageditor\");event.preventDefault();' onchange='pe.cms.settag(\"tageditor\");' title=\"".L("Access filters")."\" placeholder=\"".L("Access filters")."\" value=\"".htmlspecialchars($acl)."\" list='filters'>");
					echo("<datalist id='filters'>");
					foreach(\PHPPE\ClassMap::ace() as $b)
						echo("<option value='".$b."'>".L($b)."</option>");
					echo("<option value='siteadm|webadm'>".L("Administrator")."</option>");
					echo("</datalist><br/>\n");
					$c=@$list["_".$widget];
				}
				if(empty($c)||@$item[2]=="/")
					die(L("Not configurable"));

					if($c[0]=="*")
						$c=substr($c,1);
					$c=str_getcsv(preg_replace("/[\ ]+/"," ",strtr($c,["("=>"( ",")"=>" ) ","["=>" [ ","]"=>"",","=>" "]))," ");
					if(in_array(")",$c)){
						if(!in_array(")",$a))
							array_unshift($a,")");
						array_shift($c);
					}
					if($c[0]!=")" && in_array(")",$a))
						echo("(<input type='hidden' value='('><br/><div style='padding-left:10px;'>");
					elseif($c[0]==")")
						array_shift($c);
					$i=0; $optional=""; $f=1; $js=0;
					foreach($c as $k=>$v) {
						if($v=="[") {
							$optional=" optional";
							continue;
						}
						if($v==")") {
							echo("</div>)<input type='hidden' value=')'><br/>\n");
							while($a[$i]!=")" && !empty($a[$i])) $i++; $i++;
							$optional="";
							continue;
						}
						if(empty($optional)&&$f) {
							$optional=" focus";
							$f=0;
						}
						switch($v) {
						case "":$i++;break;
						case "view":
							$views=\PHPPE\Views::find([],"sitebuild=''","id","id,name");
							foreach(array_merge(glob("app/views/*.tpl"),glob("vendor/phppe/Core/views/*.tpl")) as $view) {
								$w=str_replace(".tpl","",basename($view));
								if($w!="frame")
									$views[]=['id'=>$w,'name'=>ucfirst($w)];
							}
							if($a[$i]==")") $i--;
							echo("<select class='input".$optional."' name='arg".$k."' data-type='".htmlspecialchars($v)."' ".
							"onchange='pe.cms.settag(\"tageditor\");' title=\"".L($v)."\">");
							$w=0;
							foreach($views as $view) {
								echo("<option value='".htmlspecialchars($view['id'])."'".($view['id']==$a[$i]?" selected":"").">".L($view['name']?$view['name']:$view['id'])."</option>");
								if($view['id']==$a[$i]) $w=1;
							}
							if(!$w)
								echo("<option value='".htmlspecialchars($a[$i])."' selected>".(!empty($a[$i])?L($a[$i]):"*")."</option>");
							echo("</select>\n");
							$i++;
							break;
						case "min":
						case "max":
						case "maxlen":
						case "rows":
						case "size":
						case "picturesize":
						case "iconheight":
						case "iconwidth":
						case "itemheight":
						case "itemwidth":
						case "num":
							echo("<input type='number' class='input".$optional."' name='arg".$k."' data-type='".htmlspecialchars($v)."' ".
							"onkeyup='pe.cms.settag(\"tageditor\");' onkeydown='if(event.key==\"Enter\"){event.preventDefault();pe_p();}' onchange='pe.cms.settag(\"tageditor\");' title=\"".L($v)."\" placeholder=\"".L($v)."\" ".
							"value=\"".htmlspecialchars(@$a[$i]==")"?"":@$a[$i++])."\"><br/>\n");
							break;
						default:
							echo("<input type='text' class='input".$optional."' name='arg".$k."' data-type='".htmlspecialchars($v)."' ".
							"onkeyup='pe.cms.settag(\"tageditor\");' onkeydown='if(event.key==\"Enter\"){event.preventDefault();pe_p();}' onchange='pe.cms.settag(\"tageditor\");' title=\"".L($v)."\" placeholder=\"".L($v)."\" ".
							"value=\"".htmlspecialchars(@$a[$i]==")"?"":@$a[$i++])."\"".($v=="label"||$v=="cssclass"||$v=="dataset"||$v=="listopts"?" list=\"".($v=="listopts"?"dataset":$v)."s\"":(substr($v,-2)=="js"?" list='jss'":""))."><br/>\n");
							if(substr($v,-2)=="js"&&$js==0){
								//! filled in by JavaScript
								echo("<datalist id=\"jss\"></datalist>\n");
								$js=1;
							}
							if($v=="label"||$v=="cssclass"){
								//! filled in by JavaScript
								echo("<datalist id=\"".$v."s\"></datalist>\n");
							}
							if($v=="dataset"||$v=="listopts"){
								echo("<datalist id=\"datasets\">\n");
								$pages=\PHPPE\Page::find([],"","created DESC","dds","id");
								$dds=[];
								foreach($pages as $p) {
									$g=@json_decode(@$p['dds'],true);
									if(!empty($g) && is_array($g))
										foreach($g as $G=>$w) {
											$dds[$G]=$G;
									}
								}
								ksort($dds);
								foreach($dds as $G) {
									echo("<option value=\"".htmlspecialchars($G)."\">".L($G)."</option>");
								}
								echo("</datalist>\n");
							}
						}
						if($optional=="focus")
							$optional="";
					}
			}
			die("</div>\n<small>".L(!empty(Core::$l['_'.$d])?'_'.$d:"")."</small>");
		} else {
			// tag chooser
			$onlywidget=(strpos($_SERVER['HTTP_REFERER'],"/cms/layouts/")===false);
    	    echo("<input type='text' style='width:98%;' placeholder='".L("Search")."' onkeyup='pe.wyswyg.search(this,this.nextSibling);'>");
        	echo("<div class='wyswyg_tag wyswyg_scroll'>\n");
        	foreach($list as $tag=>$cfg) {
				if($cfg[0]=='*' && $onlywidget)
					continue;
				if(substr($tag,0,1)=="_") {
					$tag=($onlywidget?"widget":"field")." ".substr($tag,1);
				} else
				if(substr($tag,0,1)=="/") {
         			echo("<img class='wyswyg_icon' src='js/wyswyg.js.php?item=".urlencode("<!".substr($tag,1).">")."' alt=\"".strtr("<!".substr($tag,1).">",["<"=>"&lt;",">"=>"&gt;","\""=>"&quot;"])."\">\n");
				}
         		echo("<img class='wyswyg_icon' src='js/wyswyg.js.php?item=".urlencode("<!".$tag.">")."' alt=\"".strtr("<!".$tag.">",["<"=>"&lt;",">"=>"&gt;","\""=>"&quot;"])."\">\n");
        	}
        	die("</div>");
    	}
    }

}
