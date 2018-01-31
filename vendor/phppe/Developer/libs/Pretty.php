<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016 bzt
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
 * @file vendor/phppe/Developer/libs/Pretty.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief Utility to pretty format sources
 */
namespace PHPPE;

class Pretty
{
/**
 * Usage information
 * @usage php public/index.php create
 */
	static function getUsage()
	{
		echo(chr(27)."[96m".L("Usage").":".chr(27)."[0m\n  php public/index.php ".Core::$core->app." <extension>\n\n".
			chr(27)."[96m".L("Format a PHP source or all PHP files in an extension to PSR-2 Coding Style.").chr(27)."[0m\n\n");
	}


/**
 * Parse and format php code
 * @usage php public/index.php pretty <extension>
 *
 * @param extension
 */
	static function parse($extension)
	{
		$files=[];
		if(file_exists($extension))
			self::format($extension);
		else {
			chdir("vendor/phppe/".$extension);
			foreach(["*.php", "*/*.php","*/*.js"] as $f)
				$files=array_merge($files, glob($f));
			echo(chr(27)."[96m".L("Scanning for files").":".chr(27)."[92m ".count($files).chr(27)."[0m found\n");
			foreach($files as $f)
				self::format($f);
			chdir("../../..");
		}
	}

	static function format($file)
	{
		$d=file_get_contents($file);
		$oldsize=strlen($d); $new="";
		$words=[];
		$i = $j = 0; $line=1;
		$d=preg_replace("/else[\ \t\r\n]+if/","elseif",$d);
		$l = strlen($d);
		while ($i < $l) {
			if ($d[$i] == "\n")
				$line++;
			//! skip over string literals
			if (($d[$i] == "'" || $d[$i] == '"')) {
				if($i-$j>0)
					$words[]=trim(substr($d,$j,$i-$j));
				$s = $d[$i];
				$j = $i;
				$i++;
				while ($i < $l && $d[$i] != $s) {
					if ($d[$i] == "\n")
						$line++;
					if ($d[$i] == '\\') {
						$i++;
					}
					$i++;
				}
				$i++;
				$words[]=substr($d,$j,$i-$j); $j=$i;
				continue;
			}
			if ($d[$i] == '>' && $d[$i + 1] == '?') {
				if($i-$j>0)
					$words[]=trim(substr($d,$j,$i-$j));
				$j = $i;
				$i += 2;
				while ($i + 1 < $l && ($d[$i] != '<' || $d[$i + 1] != '?')) {
					if ($d[$i] == "\n")
						$line++;
					$i++;
				}
				$i+=2;
				$words[]=substr($d,$j,$i-$j); $j=$i;
				continue;
			}
			 //! comments
			if ($d[$i] == '/' && $d[$i + 1] == '*') {
				if($i-$j>0)
					$words[]=trim(substr($d,$j,$i-$j));
				$j = $i;
				$i += 2;
				while ($i + 1 < $l && ($d[$i] != '*' || $d[$i + 1] != '/')) {
					if ($d[$i] == "\n")
						$line++;
					$i++;
				}
				$i+=2;
				$words[]=substr($d,$j,$i-$j); $j=$i;
				continue;
			}
			if ($d[$i] == '/' && $d[$i + 1] == '/') {
				if($i-$j>0)
					$words[]=trim(substr($d,$j,$i-$j));
				$j = $i;
				$i ++;
				while ($i < $l && $d[$i] != "\n") {
					$i++;
				}
				$words[]=substr($d,$j,$i-$j); $j=$i;
				continue;
			}
			//! operators and other separators
			if(in_array(substr($d,$i,2),['->','=>','?'.'>','<'.'?','<'.'!','<=','=<','=<','==','!=','+=','-=','*=','/=','%=','.=','++','--','::','&&','||'])) {
				if($i-$j>0)
					$words[]=trim(substr($d,$j,$i-$j));
				$j=$i;
				if(substr($d,$i,5)=='<'.'?php') $i+=3;
				if(substr($d,$i,3)=='===') $i++;
				if(substr($d,$i,3)=='!==') $i++;
				$i+=2;
				$words[]=trim(substr($d,$j,$i-$j));
				$j=$i;
				continue;
			}
			if(in_array($d[$i],['(',')','[',']','{','}','.',',',';','?',':','!','+','-','*','/','%','=',' '])) {
				if($i-$j>0)
					$words[]=trim(substr($d,$j,$i-$j));
				$words[]=$d[$i].($d[$i]=='='&&($d[$i+1]=='>'||$d[$i+1]=='=')?$d[$i+1]:'');
				if($d[$i]=='='&&($d[$i+1]=='>'||$d[$i+1]=='=')) $i++;
				$j=++$i;
				continue;
			}
			$i++;
		}
		if($i-$j-1>0)
			$words[]=trim(substr($d,$j,$i-$j-1));
		$ww=[];
		foreach($words as $v)
			if(trim($v)!="") $ww[]=$v;
		$words=$ww;
		//! double check, save spaces do we have everything?
		if(preg_replace("/[\ \t\r\n]+/","",implode('',$words))==
			preg_replace("/[\ \t\r\n]+/","",$d)) {
			//! format output
			$lastcmd=""; $b=0; $p=0; $s=0; $ll=0; $l=""; $db=0; $lo=$lof=$mo=0;
			for($i=0;$i<count($words);$i++) {
				$w=$words[$i];
				$sep=" ";
				if(in_array($w,['namespace','use','class','function'])) $lastcmd=$w;
				if($w[0]=='/' && (@$w[1]=='/' || @$w[1]=='*')) {
					$sep="\n";
				}
				if($w=="::"||$w=="++"||$w=="--"||$w=="->"||$w==';'||$w==','||$w=='.'||$w==')'||$w==']'||($w=='['&&@$words[$i-1][0]!='='&&@$words[$i-1]!=','&&@$words[$i-1][0]!='+'&&@$words[$i-1][0]!='-'&&@$words[$i-1][0]!='*'&&@$words[$i-1][0]!='/'&&@$words[$i-1][0]!='%')||($w=='('&&!in_array(@$words[$i-1],['if','for','foreach','while','switch']))) {
					$l=rtrim($l);
				}
				if($w=="::"||$w=="->"||$w=='!'||$w=='.'||$w=='('||$w=='['||($w==']'&&@$words[$i+1][0]!='='&&@$words[$i+1][0]!='!'&&@$words[$i+1][0]!=':'&&@$words[$i+1][0]!='+'&&@$words[$i+1][0]!='-'&&@$words[$i+1][0]!='*'&&@$words[$i+1][0]!='/'&&@$words[$i+1][0]!='%')) {
					$sep="";
				}
				if($w==';') {
					if($p>0) $sep=" "; else $sep="\n".($lastcmd=='namespace'||($lastcmd=='use'&&$words[$i+1]!='use')?"\n":"");
					if(substr(@$words[$i+1],0,4)=="//!<") {
						$l=sprintf("%-40s",$l.";"); $sep="";
						continue;
					}
				}
				if($w=='<'.'?php'||$w=='{'||$w=='}') {
					$sep="\n";
				}
				if($w=='{'&&($lastcmd=='class'||$lastcmd=='function')) {
					$l=rtrim($l)."\n".str_repeat("    ",$b+$s);
					$db=$b;
				}
				if($w=='}') {$b--; if(@$words[$i+1]=="else") $sep=" "; elseif($b<=$db) $sep.="\n"; }
				if($w==')') $p--;
				if($w==']') $s--;
				if(($lo==2 && $w==',')||
				   ($lo==1 && ($w=='&&'||$w=='||')) ||
				   ($lo==3 && $p==0 && $s==0 && ($w=='.'||$w=='+'))) {
					$sep="\n".str_repeat("    ",$b+$s+$p)."  ";
				}
				if($lo==2 && $lof && $w==$loc && $lof==($loc==')'?$p:$s)+1) {
					$l.="\n".str_repeat("    ",$b+$s+$p); $lof=0;
				}
				if($lo==2 && !$lof && ($w=='(' || $w=='[')) {
					$lof=($w=='('?$p:$s)+1; $loc=$w=='('?')':']';
					$sep="\n".str_repeat("    ",$b+$s+$p+1)."  ";
					$mo++;
				}
				$l.=$w.$sep;
				if(substr($sep,-1)=="\n") {
					if($l[0]!='/'||$l[1]!='*')
						$l=str_repeat("    ",$b+$s+$p).$l;
					$k=strrpos(rtrim($l),"\n");
					if($lo==0&&strpos($l,"\".\"")!==false&&strpos($l,'&&')===false&&strpos($l,"||")==false) $lo=2;
					if($mo>1 || (strlen($l)-$k>120 && substr(trim($l),0,strlen($words[$ll]))==$words[$ll])) {
						if($lo<3) {
							$lo++; $i=$ll-1; $l=""; $lof=0; continue;
						}
					}
					$new.=$l;
					$l=""; $ll=$i+1; $lo=$lof=$mo=0;
				}
				if($w=='{') $b++;
				if($w=='(') $p++;
				if($w=='[') $s++;
				if($w=='{'||$w=='}'||($w==';'&&$p==0)) $lastcmd="";
			}
			if($l)
				$new.=str_repeat("    ",$b+$s+$p).$l;
		} else echo(" SCANFAIL");
		echo("  ".sprintf("%-40s ".chr(27)."[90m%6d",substr($file,-40), $oldsize));
		if(!empty($new) && file_put_contents($file,$new))
			echo(sprintf(" %6d ",strlen($new)).chr(27)."[92m".L("OK").chr(27)."[0m\n");
		else
			echo(sprintf(" %6d ",0).chr(27)."[91m".L("FAIL").chr(27)."[0m\n");
	}
}
