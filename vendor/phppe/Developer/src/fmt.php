#!/usr/bin/php
<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztphp/phppe3/
 *
 *  Copyright LGPL 2015 bzt
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *   <http://www.gnu.org/licenses/>
 *
 * @file vendor/phppe/Developer/src/fmt.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief quick and dirty tool to compress or decompress php source and update documentation
 */
error_reporting(E_ALL & ~ E_NOTICE);
global $keywords,$spec,$modifiers,$funcs;
$keywords=array("as","===","!==","==","+=","-=","*=","/=","^=","%=","!=",".=","<=",">=","=>","->","++","--","+","-","*","/","=","[","]",".",",","::",":","?","!","~","%","^","<",">","&&","&","||","|");
$spec=array("if","else","for","while","do","foreach","elseif");
$modifiers=array("final","static","private","public","protected");

if(empty($_SERVER["argv"][1]) || $_SERVER["argv"][1]=="--help")
	die("fmt.php [-c] (inputfile) [ > (outputfile)]\n-c: compress php source and update documentation download\n(default) decompress php source and update documentation\n");
$i=1; $compress=false;
if($_SERVER["argv"][1]=="-c") {
	$compress=true;
	$i++;
}
$data=preg_replace("|\?".">[ \r\n\t]+\$|m","?".">",
	preg_replace('/"Corrupted "\.\$c\);[\n]*/m','"Corrupted ".$c);'."\n",
	str_replace('$c=__FILE__;if(filesize($c)!=',($compress?'':'//').'$c=__FILE__;if(filesize($c)!=',
	str_replace('//$c=__FILE__;if(filesize($c)!=','$c=__FILE__;if(filesize($c)!=',
	file_get_contents($_SERVER["argv"][$i])))));
if(empty($data))
	die("Core: unable to read ".$_SERVER["argv"][$i]."\n");
//without php code sniplets, just reply input back
if(!preg_match_all("|<\?(.*)\?".">|Ums",$data,$code,PREG_OFFSET_CAPTURE|PREG_SET_ORDER))
	die($data);

$offs=0; $out="";
foreach($code as $c) {
	if($c[1][1]>$offs) {
		$out.=substr($data,$offs,$c[1][1]-$offs);
		$offs=$c[1][1];
	}
	$offs+=strlen($c[1][0]);
	$out.=autoformat(!$compress,$c[1][0]);
}
if(strlen($data)>$offs)
	$out.=substr($data,$offs,strlen($data)-$offs);

$fn="phppe3.html";
if($compress) {
	$out=trim($out); $l=strlen($out)+2;
	if($l<65535 && substr($out,$l-4)=="?".">") {
		$out=substr($out,0,$l-4);
		$out.=str_repeat(" ",65535-$l)."\n?>\n";
	} else {
		fprintf(STDERR,"Core: file too big (".$l." bytes)\n");
//		exit(1);
	}
	if($l<65535) {
		$chksum=sha1(preg_replace("/\'([^\']+)\'\!\=sha1/","''!=sha1",$out));
		fprintf(STDERR,"Core: checksum ".$chksum." size ".$l."\n");
		$out=preg_replace("/\'([^\']+)\'\!\=sha1/","'".$chksum."'!=sha1",$out);
		if(file_exists($fn)) {
			fprintf(STDERR,"Docu: updating downloadable\n");
//			file_put_contents($fn,preg_replace("/data:text\/plain[^\"\']+/","data:text/plain,".str_replace("+","%20",urlencode($out)),file_get_contents($fn)));
			file_put_contents($fn,preg_replace("|/\*core\*/\"data:text\/plain[^\"\']+|","/*core*/\"data:text/plain;base64,".base64_encode($out),file_get_contents($fn)));
		}
	}
}
if(!$compress && file_exists($fn)) {
	$funcde=array(
				" h("=>" htmlspecialchars(",
				"(h("=>"(htmlspecialchars(",
				" e("=>" error_reporting(",
				"(e("=>"(error_reporting(",
				"\te("=>"\terror_reporting(",
				" f("=>" file_exists(",
				"(f("=>"(file_exists(",
				"\tf("=>"\tfile_exists(",
				" o("=>" ob_get_clean(",
				"(o("=>"(ob_get_clean(",
				"\to("=>"\tob_get_clean(",
				" g("=>" file_get_contents(",
				"(g("=>"(file_get_contents(",
				" i("=>" include_once(",
				"(i("=>"(include_once(",
				"\ti("=>"\tinclude_once(",
				" a("=>" is_array(",
				"(a("=>"(is_array(",
				" r("=>" trim(",
				"(r("=>"(trim(",
				" q("=>" method_exists(",
				"(q("=>"(method_exists(",
				" y("=>" function_exists(",
				"(y("=>"(function_exists(",
				" z("=>" substr(",
				"(z("=>"(substr(",
				" w("=>" substr(",
				"(w("=>"(substr(",
				" u("=>" strlen(",
				"(u("=>"(strlen(",
				" s("=>" strtr(",
				"(s("=>"(strtr(",
				" d(1)"=>" debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[1]['file']",
				"(d(1)"=>"(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[1]['file']",
				"\td(1)"=>"\tdebug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[1]['file']",
				" d(2)"=>" debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[2]['file']",
				"(d(2)"=>"(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[2]['file']",
				"\td(2)"=>"\tdebug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[2]['file']",
				" n("=>" dirname(",
				"(n("=>"(dirname(",
				" m("=>" basename(",
				"(m("=>"(basename(",
				" t("=>" strtolower(",
				"(t("=>"(strtolower(",
				" k("=>" strtoupper(",
				"(k("=>"(strtoupper(",
				" x("=>" explode(",
				"(x("=>"(explode(",
				" jd("=>" json_decode(",
				"(jd("=>"(json_decode(",
				" p("=>" preg_match(",
				"(p("=>"(preg_match(",
				" pr("=>" preg_replace(",
				"(pr("=>"(preg_replace(",
				" ce("=>" class_exists(",
				"(ce("=>"(class_exists(",
	);
	$c=0;
	$d=str_replace("<br/>","",file_get_contents($fn));
	foreach($funcs as $f=>$v) {
		if(preg_match_all("|<pre id='src_".strtr($f,array("."=>"\.","("=>"\(",")"=>"\)"))."'[^>]*>(.*)<\/pre>|Ums",$d,$m,PREG_OFFSET_CAPTURE|PREG_SET_ORDER)) {
			$p=substr($d,0,$m[0][0][1]);
			if(preg_match_all("|<pre class='lineno'>[^p]+<\/pre>|Ums",$p,$M,PREG_OFFSET_CAPTURE|PREG_SET_ORDER))
				$p=substr($p,0,$M[count($M)-1][0][1]);
			$nc=dohl(str_replace("<","&lt;",str_replace(">","&gt;",(str_replace('" . "',"",preg_replace("/([^a-zA-Z])PE([^a-zA-Z])/","\\1\".php\"\\2",
				preg_replace("/([^a-zA-Z])j\(([^,]+),([^,]+),([^\)]+)\)/","\\1sha1(\\2.'|'.\\3.'|'.\\4",
				strtr(strtr(strtr(strtr($v['code'],array(
				"\n\t"=>"\n",
				"\" . PE"=>".php\"",
				"P . \""=>"\"vendor/phppe/core/",
				"M . \""=>"\"vendor/",
				"C . \""=>"\"\\\\PHPPE\\\\",
				"C . \$"=>"\"\\\\PHPPE\\\\\" . \$",
				"A . \""=>"\"\\\\PHPPE\\\\AddOn\\\\",
				"A . \$"=>"\"\\\\PHPPE\\\\AddOn\\\\\" . \$",
				" N . \""=>" \"vendor/phppe/",
				"(N . \""=>"(\"vendor/phppe/",
				"=N . \""=>"=\"vendor/phppe/",
				"use\\"=>"use \\",
				"new\\"=>"new \\",
				"return\\"=>"return \\",
			    )),$funcde),$funcde),$funcde))))))));
			$D=explode("\n",$nc);
			$ln="";for($i=0;$i<count($D);$i++)$ln.=($i+1)."<br>";
			$d=$p."<pre class='lineno'>".$ln."</pre>\n".substr($d,$m[0][0][1],$m[0][1][1]-$m[0][0][1]).
			    str_replace("\n","<br/>",$nc).
			substr($d,$m[0][1][1]+strlen($m[0][1][0]));
			unset($D); unset($p); unset($ln);
			$c++;
		}
	}
	fprintf(STDERR,"Docu: updating ".$c." sources\n");
	$d=preg_replace("|/\*view\*/\"data:text\/plain[^\"\']+|","/*view*/\"data:text/plain;base64,".base64_encode(file_get_contents("phppe/views/index.tpl")),$d);
	fprintf(STDERR,"Docu: updating self test page\n");
	$d=preg_replace("|Source <small>\([0-9]+k\)</small>|","Source <small>(".round(strlen($out)/1024,0)."k)</small>",$d);
	file_put_contents($fn,$d);
}
die($out);

function last($a){return $a[count($a)-1];}

function autoformat($decompress,$code)
{
	global $keywords,$spec,$modifiers,$funcs;
	if(substr($code,0,3)=="php") {
		$out="php".($decompress?"\n":"");
		$code=substr($code,3);
	} else $out="";
	$code=	preg_replace("/else[\ \t\n]+if/","elseif",$code);
	//tokenize
	$tokens=array();
	for($i=0;$i<strlen($code);) {
		while($code[$i]==' ' || $code[$i]=="\t" || $code[$i]=="\n" || $code[$i]=="\r") $i++;
		if($code[$i]=='#' || ($code[$i]=='/' && $code[$i+1]=='/')) {
			$s=$i; $i+=2;
			while($code[$i]!="\n" && $i<strlen($code)) $i++;
			if(!$decompress) continue;
			$tokens[]["comment"]=substr($code,$s,$i-$s);
			continue;
		}
		if($code[$i]=='/' && $code[$i+1]=='*') {
			$s=$i; $i+=2;
			while(($code[$i]!='*' || $code[$i+1]!='/') && $i+1<strlen($code)) $i++;
			$i+=2;
			if($s>10 && !$decompress) continue;
			$tokens[]["comments"]=($code[$s-1]=="\n"?"\n":"").substr($code,$s,$i-$s);
			continue;
		}
		if($code[$i]=="'" || $code[$i]=='"') {
			$s=$i; $c=$code[$i]; $i++;
			while($code[$i]!=$c && $i<strlen($code)) { if($code[$i]=="\\") $i++; $i++; }
			$i++;
			$tokens[]["const"]=substr($code,$s,$i-$s);
			continue;
		}
		if($code[$i]=="(" || $code[$i]==")" || $code[$i]=="{" || $code[$i]=="}" || $code[$i]==";" || $code[$i]=="\\") {
			$tokens[][$code[$i]]=$code[$i];
			$i++; continue;
		}
		foreach($keywords as $k)
			if(strtolower(substr($code,$i,strlen($k)))==$k) {
				$tokens[]["keyword"]=substr($code,$i,strlen($k));
				$i+=strlen($k);
				continue 2;
			}
		foreach($modifiers as $k)
			if(strtolower(substr($code,$i,strlen($k)))==$k) {
				$tokens[]["modifier"]=substr($code,$i,strlen($k));
				$i+=strlen($k);
				continue 2;
			}
/*
		if($tokens[count($tokens)-1]["keyword"]!="->") foreach($spec as $k)
			if(strtolower(substr($code,$i,strlen($k)))==$k && ($code[$i+strlen($k)]==' ' || $code[$i+strlen($k)]=="\t" || $code[$i+strlen($k)]=="\n" || $code[$i+strlen($k)]=="\r")) {
				$tokens[]["const"]=(!$decompress?($code[$i-1]!="\n"?"\n":""):"").substr($code,$i,strlen($k));
				$i+=strlen($k);
				continue 2;
			}
*/
		if($code[$i]=='$' || $code[$i]=='@' || ($code[$i]>='0' && $code[$i]<='9') || ($code[$i]>='a' && $code[$i]<='z') || ($code[$i]>='A' && $code[$i]<='Z') || $code[$i]=='_') {
			$s=$i; $i++;
			while(strpos("/\'\"()[]{}<> \n\r\t.,+-*%=~:;?!^&|\$",$code[$i])===false && $i<strlen($code)) $i++;
			$tokens[][$code[$s]=='$'?"var":"const"]=substr($code,$s,$i-$s);
			continue;
		}
		$i++;
	}
	//output tokens
	$p=0; $b=0; $c=false; $os=0; $pnl=$bnl=-1;
	$funcname="";$funcpre="";$funcb=-1; $nlcls=0;
	foreach($tokens as $n=>$t)
		foreach($t as $type=>$value) {
			if($type=="{") { if($decompress && $out[strlen($out)-1]!="\n" && $out[strlen($out)-1]!="\t") $out.=$nlcls?newline($b):" "; $nlcls=0; $b++; }
			if($type=="}") { $b--; if($b<0) die(last(explode("\n",$out))."\nERROR}"); }
			if($type=="(") { if(!empty($tokens[$n-1]["const"])&&in_array($tokens[$n-1]["const"],$spec)) $pnl=$p; $p++; }
			if($type==")") { $p--; if($p<0) die(last(explode("\n",$out))."\nERROR)"); }
			if($decompress) {
				if(($type=="keyword"||($type=="var"&&$tokens[$n-1]["keyword"]!="::")/*||($type==")"&&empty($tokens[$n-1]["("]))*/) && $value!="," && $value!="->" && $value!="::" && $value!="++" && $value!="--" && $value!="[" && $out[strlen($out)-1]!=" " && $out[strlen($out)-1]!="&" && $out[strlen($out)-1]!="(" && $out[strlen($out)-1]!="\t")
					$out.=" ";
				if(!empty($tokens[$n-1]["const"]) && $tokens[$n-1]["const"]=="class" && $tokens[$n-2]["keyword"]!="->") {
					$nlcls=1;
					$funcpre=$value;
					$s=strlen($out)-6;
					for($m=$n-2;!empty($tokens[$m]["modifier"]);$m--)
						$s-=strlen(reset($tokens[$m]))+1;
					if(!empty($tokens[$m]["comment"])||!empty($tokens[$m]["comments"]))
						$s-=strlen(reset($tokens[$m]))+1+$b;
					$dummy=substr($out,$s);
					$out=substr($out,0,$s);
					$out.=newline($b).$dummy;
					unset($dummy);
				}
				if($tokens[$n-1]["const"]=="namespace") $funcpre="";
				if(!empty($tokens[$n-1]["const"]) && $tokens[$n-1]["const"]=="function") {
					$nlcls=1;
					$funcname=($funcpre?$funcpre.".":"").$value;
					$funcb=$b;
					$funcs[$funcname]['start']=strlen($out)-9;
					$e=0;
					for($m=$n-2;!empty($tokens[$m]["modifier"]);$m--)
						$funcs[$funcname]['start']-=strlen(reset($tokens[$m]))+1;
					if(!empty($tokens[$m]["comment"])||!empty($tokens[$m]["comments"]))
						$funcs[$funcname]['start']-=strlen(reset($tokens[$m]))+1+$b;
					while($out[$funcs[$funcname]['start']]=="\t") $funcs[$funcname]['start']++;
					$dummy=substr($out,$funcs[$funcname]['start']);
					$out=substr($out,0,$funcs[$funcname]['start']);
					$funcs[$funcname]['start']=strlen($out);
					$out.=$dummy;
					unset($dummy);
				}
				if($type=="}" && !empty($tokens[$n+1][")"])) $out.="\n".newline($b);
			} else {
				if(($type=="comments"/*||$value=="class"||($b<2&&$value=="function")*/) && empty($tokens[$n-1]["comments"]) && $tokens[$n-1]["keyword"]!="->") $out.="\n";
			}
			$out.=trim($value).(($type=="const"&&!empty($tokens[$n+1])&&(array_keys($tokens[$n+1])[0]=="const"||($decompress&&array_keys($tokens[$n+1])[0]=="var")))||$tokens[$n+1]["keyword"]=="as"||($type=="modifier"&&($decompress||$tokens[$n+1]["const"]=="function"||!empty($tokens[$n+1]["modifier"])))?" ":"");
			if($decompress) {
				if($value=="{" && $bnl!=-1) $bb++;
				if($value=="}" && $bnl!=-1) $bb--;
				if($value=="}" && $funcb==$b) {
					if($funcb==$b) {
						$funcs[$funcname]['code']=wordwrap(substr($out,$funcs[$funcname]['start']),132,"\n");
						$funcb=-1;
					}
				}
				if($value==":" && $tokens[$n-2]["const"]=="case") $out.=newline($b);
				if(($value==";" || $type=="}") && $bb==0 && $bnl!=-1) { $b=$bnl; $bnl=-1; }
				if($type==";" && !empty($tokens[$n+1]["comment"])) $out.=(!empty($tokens[$n-1]["var"])&&!empty($tokens[$n-2]["modifier"]))||(!empty($tokens[$n-3]["var"])&&!empty($tokens[$n-4]["modifier"]))||(!empty($tokens[$n-1]["keyword"]))?addtab($out):(empty($tokens[$n-1][")"])?newline($b):"");
				if($type==")" && $p==$pnl) { $pnl=-1; if(empty($tokens[$n+1]["{"])) { if($bnl==-1) $bnl=$b; $b++;  $out.=newline($b-(!empty($tokens[$n+1]["}"])?1:0)); } }
				if(($type=="keyword"/*||($type=="(" && empty($tokens[$n+1][")"]))*/) && $value!="->" && $value!="::" && $value!="&" && $value!="]" && $out[strlen($out)-1]!=" " && $out[strlen($out)-1]!="\t") $out.=" ";
				if($type=="comment" || $value=="else") $out.=newline($b-(!empty($tokens[$n+1]["}"])?1:0)+($value=="else"&&empty($tokens[$n+1]["{"])&&(empty($tokens[$n+1]["const"])||$tokens[$n+1]["const"]!="if")?1:0));
				if($type=="{" || ($type=="}"&&empty($tokens[$n+1][")"]))/*&&empty($tokens[$n+1]["comment"])*/) $out.=newline($b-(!empty($tokens[$n+1]["}"])?1:0));
				if($type==";" || $type=="comments") $out.=$p!=0&&$tokens[$n+1]["const"]!="else"?" ":($type==";"&&(!empty($tokens[$n-1]["keyword"])||!empty($tokens[$n-1]["var"])||!empty($tokens[$n-3]["var"]))&&!empty($tokens[$n+1]["comment"])&&empty($tokens[$n+2]["const"])?"":newline($b-(!empty($tokens[$n+1]["}"])?1:0)));
			} else if($type=="comments" && $out[strlen($out)-1]!="\n") $out.="\n";
	}
	if($decompress) {
	    $out=str_replace("return\\","return \\",
		 str_replace("new\\","new \\",
		 str_replace("use\\","use \\",$out)));
	}
	return(str_replace("[ ]","[]",
		str_replace("\n\n\n","\n\n",
		str_replace("/***","\n/***",
		str_replace("\" . \"","",
		str_replace("-> \$","->\$",
		preg_replace("/[\t]+\/\*\*/m","/**",
		str_replace('function as ','function as',
		preg_replace("/as([A-Z])/","as \\1",
		preg_replace("/[\n]([ \t]+)[\n]/m","\n",
		str_replace('$ $','$$',$out)))))))))));
}
function addtab($c)
{
	$i=strlen($c)-1;$s=0;while($i>0&&$c[$i]!="\n"){$s+=$c[$i]=="\t"?8:1;$i--;}
	return @str_repeat("\t",7-intval($s/8));
}
function newline($level)
{
	return("\n".($level>0?str_repeat("\t",$level):""));
}

function dohl($c)
{
return $c;
	$operators=array("!=","!","==","=","+=","+","-=","-","/=","/","*=","*",".=",".","|=","|","&=","&amp;=","&amp;","&","?",":");
	$keywords=array("as","=&lt;","static","final","public","private","function","if","foreach","for","while","break","continue","switch","case","array","stdclass","parent","extends","self");

	$n="";
	for($i=0;$i<strlen($c);$i++) {
		//keywords
		$iskw=""; foreach($keywords as $k) if(strtolower(substr($c,$i,strlen($k)))==$k) { $iskw=$k; break; }
//		$isop=""; foreach($operators as $k) if(strtolower(substr($c,$i,strlen($k)))==$k) { $isop=$k; break; }
		//html workaround
		if($c[$i]=="&") {
			if(substr($c,$i,2)=="&&") { $n.="<span class='condition'>&amp;&amp;</span>"; $i++; } else
			if(substr($c,$i,10)=="&amp;&amp;") { $n.="<span class='condition'>&amp;&amp;</span>"; $i+=9; } else {
				$j=$i; while(!empty($c[$i])&&$c[$i]!=";")$i++;
				$n.=substr($c,$j,$i-$j);
			}
		} else
		if($c[$i]=="|"&&$c[$i+1]=="|") {
			$n.="<span class='condition'>||</span>"; $i++;
		} else
		//comments
		if($c[$i]=="/"&&$c[$i+1]=="/") {
			$j=$i; while($c[$i]!="\n"&&substr($c,$i,3)!="<br")$i++;
			$n.="<span class='comment'>".substr($c,$j,$i-$j)."</span>".$c[$i];
		} else
		if($c[$i]=="/"&&$c[$i+1]=="*") {
			$j=$i++; while($c[$i-1]!="*"||$c[$i]!="/")$i++;$i++;
			$n.="<span class='comment'>".substr($c,$j,$i-$j)."</span>";$i--;
		} else
		//string
		if($c[$i]=="'"||$c[$i]=='"') {
			$e=$c[$i]; $j=$i; $i++; while($c[$i]!=$e){if($c[$i]=='\\')$i++;$i++;};$i++;
			$n.="<span class='stringconst'>".substr($c,$j,$i-$j)."</span>";$i--;
		} else
		//number
		if(preg_match("/[0-9\-]/",$c[$i])) {
			$e=$c[$i]; $j=$i; while(preg_match("/[0-9\.eE]/",$c[$i]))$i++;
			$n.="<span class='stringconst'>".substr($c,$j,$i-$j)."</span>";$i--;
		} else
		//variables
		if($c[$i]=='$') {
			$j=$i++; while(preg_match("/[a-zA-Z0-9_]/",$c[$i]))$i++;
			if(substr($c,$i,5)=="-&gt;"){$i+=5;while(!empty($c[$i])&&preg_match("/[a-zA-Z0-9_]/",$c[$i]))$i++;}
			$n.="<span class='".($c[$i]=="("?"function":"variable")."'>".substr($c,$j,$i-$j)."</span>";$i--;
		} else
		//keywords
		if($iskw) {
			$j=$i; $i+=strlen($iskw);
			$n.="<span class='keyword'>".substr($c,$j,$i-$j)."</span>";$i--;
		} else
		//variables
		if($c[$i]=='$') {
			$j=$i++; while(preg_match("/[a-zA-Z0-9_]/",$c[$i]))$i++;
			if(substr($c,$i,5)=="-&gt;"){$i+=5;while(!empty($c[$i])&&preg_match("/[a-zA-Z0-9\_]/",$c[$i]))$i++;}
			$n.="<span class='".($c[$i]=="("?"function":"variable")."'>".substr($c,$j,$i-$j)."</span>";$i--;
		} else
		//functions
		if(preg_match("/^[a-zA-Z\_][a-zA-Z0-9\_]*[\(]/",substr($c,$i,64))) {
			$j=$i; while($c[$i]!="(")$i++;
			$n.="<span class='function'>".substr($c,$j,$i-$j)."</span>";$i--;
		} else
/*
		if($isop) {
			$j=$i; $i+=strlen($isop);
			$n.="<span class='operator'>".substr($c,$j,$i-$j)."</span>".$c[$i];
		} else
*/
			$n.=$c[$i];
	}
	return $n;
}
?>