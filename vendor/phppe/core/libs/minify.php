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
 * @file vendor/phppe/core/libs/minify.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief CSS and JS minifier
 */
namespace PHPPE;

function minify( $data, $type = "css" ) {
	//! check input, return output just as is if type unknown
	if($type != "css" && $type != "js")
		return $data;

	//! allow use of third party vendor code
	if($type == "css" && class_exists("CSSMin") )
		return CSSMin::minify($data);
	if($type == "js" && class_exists("JSMin") )
		return JSMin::minify($data);

	//! do the stuff ourself (fastest, safest, simpliest, and no dependency required at all...)
	$new = ""; $l=strlen($data);
	for($i=0;$i<strlen($data);$i++) {
		if($data[$i]=='/' && $data[$i+1]=='*') { $i+=2; while($i<$l&&!($data[$i-1]=='*'&&$data[$i]=='/'))$i++; continue; }
		if($type == "js") {
			if($data[$i]=='/' && $data[$i+1]=='/' && $data[$i-1]!=':') { $i+=2; while($i<$l&&!($data[$i]=="\n"||$data[$i]=="\r"))$i++; continue; }
			if($data[$i]=='/' && ($new[strlen($new)-1]=='='||$new[strlen($new)-1]=='(')) { while($i+1<$l&&($data[$i+1]!='/'||$data[$i]=="\\"))$new.=$data[$i++]; $new.=$data[$i]; continue; }
		}
		if($data[$i]=='"' || $data[$i]=="'") { $s=$data[$i]; $new.=$s; $i++; while($i<$l&&($data[$i]!=$s||$data[$i-1]=="\\"))$new.=$data[$i++]; $new.=$s; continue; }
		if($data[$i]==" " || $data[$i]=="\t" || $data[$i]=="\r" || $data[$i]=="\n") {
			$o=$data[$i];
			while($i<$l && ($data[$i]==" " || $data[$i]=="\t" || $data[$i]=="\r" || $data[$i]=="\n"))$i++;$i--;
			//if(($o=="\n"||$o=="\r")&&$data[$i+1]!='{'&&!empty($new)&&!in_array($new[strlen($new)-1],['[','(',';',',','?',':','{','}','|','&','='])) $new.=";";
			if($new&&preg_match("/[a-zA-Z0-9\_]/",$new[strlen($new)-1])&&preg_match('/[a-zA-Z0-9\.\$\_]/',$data[$i+1])&&$data[$i]!="\n"&&$data[$i]!="\r")
				$new.=" ";
			continue;
		}
		$new .= $data[$i];
	}
	return $new;
}
