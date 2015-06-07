<?php
use PHPPE\Core as PHPPE;

//check user access
if( !PHPPE::$user->has("install") )
	die( "403" );

PHPPE::$core->nocache = true;

header("Content-type:text/javascript;charset=utf8");
error_reporting(0);
?>/*
 *   PHP Portal Engine v3.0.0
 *   https://github.com/bztphp/phppe3/
 *
 *   Copyright 2015 bzt, LGPLv3
 *
 *   PHPPE Extension Manager
 *
 */

/*PRIVATE VARS*/
var extensions_installed=[ <?=PHPPE::lib("Extensions")->getinstalled()?> ];
var extensions_pkgs=[ <?=PHPPE::lib("Extensions")->getpkgs()?> ];
var extensions_tmr=null, extensions_me=false, extensions_lastcmd="", extensions_param="";

/*PUBLIC METHODS*/
function extensions_search(str,installed)
{
	var t="",up=false,down=false;
	if(str==""||str==null||str==undefined)str=document.getElementById("search").value;
	if(str=="<?=L("installed")?>"<?=(L("installed")!="installed"?"||str==\"installed\"":"")?>){str="";installed=true;}
	if(str=="<?=L("upgrade")?>"<?=(L("upgrade")!="upgrade"?"||str==\"upgrade\"":"")?>){str="";up=true;}
	if(str=="<?=L("downgrade")?>"<?=(L("downgrade")!="downgrade"?"||str==\"downgrade\"":"")?>){str="";down=true;}
	if(extensions_tmr) { clearTimeout(extensions_tmr); extensions_tmr=null; }
	var r=(str!=null && str!=undefined && str!="" ? new RegExp(str.replace(".","\\.").replace("*","\\*").replace("+","\\+").replace("|","\\|"),"i") : null);
	for(i=0;i<extensions_pkgs.length;i++) {
		if((installed==true && !extensions_pkgs[i].installed) ||
		   (r!=null &&
		   !(extensions_pkgs[i].name.match(r)||extensions_pkgs[i].version.match(r)||extensions_pkgs[i].desc.match(r)||extensions_pkgs[i].maintainer.match(r)||extensions_pkgs[i].license.match(r)||extensions_pkgs[i].url.match(r))
		   )
		) continue; else
		if(up==true && (!extensions_pkgs[i].installed ||
		   extensions_pkgs[i].installed>=extensions_pkgs[i].version)
		) continue; else
		if(down==true && (!extensions_pkgs[i].installed ||
		   extensions_pkgs[i].installed<=extensions_pkgs[i].version)
		) continue;
		t+="<div class='extension'><img class='preview' title='"+extensions_pkgs[i].url+"' src='data:image/png;base64,"+(extensions_pkgs[i].preview?extensions_pkgs[i].preview:'iVBORw0KGgoAAAANSUhEUgAAAIAAAAB5CAMAAADGfdkoAAAAUVBMVEUAAADDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8OOFo/iAAAAGnRSTlMA+Mgje5u258wWvleUTwtvrUQspPCGYTne1lzKNFwAAAPaSURBVGje7ZrJlqsgEEALBAdUBFHU+v8PfadHTToRRAlvkbvJok8nF6kJEoiC1MzkVKim6yEBVuDKYCZ4LR3BO5ZXKkwFPkBJeBEdPmZ4USyM+JSXbEOHzyEZRGfCPShEp8RdGESmxn2G2Kkg0EEH8ZASMnSRQxx6JophWCi6KKLsQU/RmxiZ2OAB+FTBxeR4kLK71EFhALyKXnpflY8VwUCEjNv73AyXJMSC4ZAeTtPjGYoqbvNxI4IXbhnrWhmUA+fbczUWPwvILJ4kO/7xOa6QAU9iDn8+QwdxHkE9svani8hM112TL+vq40eBLklBzWh1v0kcmU0ta/KyiJ8I/WbXZ2HGrt2KQDXVncBQ+rByR2aqmq1IcGxYj8DbK6ezUOO0zqNRgkC73+REX27AifXayBbD4Oda7rqMOp6A8komFm8LSq+KyuMFYeH1LgbDqE9k4cr0WY/atUJfORZN6EZvK7TuGkWL60qxRQ+KJW+6m1ZRacu4cHqwgCzcL4zbAv27MTPBJ1TgICy8Z8GZ1dnGo9dNYBUAgcEUHx1LZ3KdYEOurWY8y7Dk3eOCwiCgF4YPfyZoGpnwGqa/Nb2Qh88eM+eUBA8ey97hUEqPLGRfiWV5edyiBTncrv/m8+0wiLF9FJNs+P2PaZvenZoPzt/93vFcfT9ixfS9hextk8+fNaPqb+NTM7X4ZsLtyEIsbLn948I7Lf8eB1vQAxb5WN8aZvWoiqMTwyhd5Wag3P7pU/QnFPOxre70hHvwUd+Lp1Z63vXM+SYs7vN4UUxvLdrCdSfEqDKcaXmw3syq09WTkkAW3v1aVMtuEpwr+SW3k3wynBHa1F8Gw6kDkHXGsRifSs6t4wBBJLiQeIp6f3wtwU1+/qBfdWYJv4ho8RTqp3k93KcRPCB4Crn3KC14YE5Gwd5KJvBAX3Xpx4/NnivDyT14Hk0zuFnNT991ZI7jR/w9KBzTd6w96Ne2eQe78uunxfDHGP0bBPyO/rrbd1JDTGaP6Toqo8+NjmVumg/GzQtv2kv2oAVool4LL26B0vvLGIrfveHrrUvwgLkLvkA3FP4KGAoeZOcF1tIr8PvM8aVDr/gNRu05uFBjlCGojFJm+HgxuWc57EIF3DTgQ+UUmJcsCMzBC+ESKCgE4StgnQJlXAGZWgBEaoE6tYBMLQAqtUCbWkCSxAJgUgvo1AIwpBJwj4ZtVIEVmloAeGoBqGq+JBNYJRpKEgmsEu0oSEKBVSKZwHo1LwZEm0BgRepxSiSw8hZ4C7wF3gJvgbfAW+At8Bb4HwRoYoEZSRCXCdSqpAGURsMT/gG5cUM2oGYBXgAAAABJRU5ErkJggg==');
		t+="' style='position:absolute;width:128px !important;' alt='"+extensions_pkgs[i].id+"'><div style='overflow:auto;padding-left:140px;height:128px;'>";
		t+=extensions_pkgs[i].name+" "+extensions_pkgs[i].version+(extensions_pkgs[i].installed?" <span class='installed' style='color:green;'>(<?=L("installed")?> "+extensions_pkgs[i].installed+")</span>":"")+"<br>";
		if( extensions_pkgs[i].url != undefined ) {
			var u="",s=extensions_pkgs[i].size;
			if(s>1024*1024) { u="M"; s=Math.round(s/1024/1024); }
			if(s>1024) { u="K"; s=Math.round(s/1024); }
			t+="<small class='details' style='font-style:italic;'>"+extensions_pkgs[i].id+" ("+s+" "+u+"b, "+extensions_pkgs[i].license+", "+extensions_pkgs[i].maintainer+")</small><br>";
			if(!extensions_pkgs[i].installed) {
				if(Math.floor(extensions_pkgs[i].price)!=0)
				t+="<input type='button' onclick='"+(extensions_pkgs[i].homepage? "window.open(\""+extensions_pkgs[i].homepage : "alert(\"<?=L("No webshop url given")?>" )+"\");' value='<?=L("Buy")?> ("+extensions_pkgs[i].price+"&euro;)' style='background:#B0B0F0;'>&nbsp;&nbsp;&nbsp;&nbsp;";
				t+="<input type='button' onclick='extensions_cmd(\"install\","+i+");' value='<?=L("Install")?>' style='background:#"+(Math.floor(extensions_pkgs[i].price)!=0?"B0B0F0":"B0F0B0")+";'>";
			} else if(extensions_pkgs[i].installed<extensions_pkgs[i].version) t+="<input type='button' onclick='extensions_cmd(\"install\","+i+");' value='<?=L("Upgrade to")?> "+extensions_pkgs[i].version+"'>";
			else if(extensions_pkgs[i].installed>extensions_pkgs[i].version) t+="<input type='button' onclick='if(confirm(\"<?=L("Are you sure?")?>\"))extensions_cmd(\"install\","+i+");' value='<?=L("Downgrade to")?> "+extensions_pkgs[i].version+"' style='background:#F0B0B0;'>";
			else t+="<input type='button' onclick='extensions_cmd(\"install\","+i+");' value='<?=L("Reinstall")?>' style='background:#"+(Math.floor(extensions_pkgs[i].price)!=0?"B0B0F0":"B0F0B0")+";'>";
			if(extensions_pkgs[i].config!="" && window.XMLHttpRequest && (extensions_pkgs[i].installed||extensions_pkgs[i].id=="phppe")) t+="<input type='button' onclick='extensions_conf("+i+");' value='<?=L("Configure")?>' style='background:#B0F0B0;'>";
			if(extensions_pkgs[i].installed) {
				t+="&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' onclick='if(confirm(\"<?=L("Are you sure?")?>\"))extensions_cmd(\"uninstall\","+i+");' value='<?=L("Remove")?>' style='background:#F0B0B0;'>";
			}
		}
		t+="<br><small class='desc' style='color:#808080;'>"+(extensions_pkgs[i].desc?extensions_pkgs[i].desc:'<?=L("No description")?>')+"<br><small>"+extensions_pkgs[i].time+"   "+extensions_pkgs[i].sha1+"</small></small></div></div>";
	}
	if(t=="") t="<i><?=L("No match found.")?></i>";
	document.getElementById('pkgs').innerHTML=t;
	return true;
}
function extensions_conf(i)
{
	var t="",p="",cfg=new Array(),was=new Array();
	if(i==-1 || extensions_pkgs[i].config=="") return;
	var url="<?=url("/")?>extensions/getconf?item="+encodeURIComponent(extensions_pkgs[i].id);
	if( window.XMLHttpRequest ) {
		var r = new XMLHttpRequest();
		r.open('GET', url, false); r.send(null);
		try {
		if(r.status==200) cfg=JSON.parse(r.responseText);
		else alert('HTTP-E: '+r.status);
		} catch(e) {
			if(r.responseText!=null&&r.responseText!=undefined)
				alert(r.responseText);
			cfg=new Array();
		}
	} else return;
	for(p in extensions_pkgs[i].config) {
		if(!extensions_pkgs[i].config.hasOwnProperty(p)) continue;
		var m=extensions_pkgs[i].config[p].match(/^([^\(]+)\(?([^\)]*)/);
		var a=m[2]!=null?m[2].split(','):new Array();
		if(m[1]==null) continue;
		was[p]=1;
		t+="<tr><td>"+(extensions_pkgs[i].conf.hasOwnProperty(p)?extensions_pkgs[i].conf[p]:p)+":</td><td width='100%'>";
		switch(m[1]) {
			case "select":
				t+="<select name='"+p+"'>";
				for(var j=0;j < a.length;j++) {
					var d = a[j].split("=");
					var v = d[1]!=null?d[0]:a[j];
					var n = d[1]!=null?d[1]:a[j];
					t+="<option value='"+v+"'"+(cfg[p]!=null&&cfg[p]==v?" selected":"")+">"+(extensions_pkgs[i].conf.hasOwnProperty(n)?extensions_pkgs[i].conf[n]:n)+"</option>";
				}
				t+="</select>";
				break;
			case "textarea":
				t+="<textarea name='"+p+"' style='width:98%' rows='3' wrap='virtual'>"+(cfg[p]!=null?cfg[p]:"")+"</textarea><br>";
				break;
			default:
				t+="<input type='text' name='"+p+"' style='width:98%;' value='"+(cfg[p]!=null?cfg[p]:"")+"'>";
				break;
		};
		t+="</td></tr>\n";
	}
	if(t=="") return;
	t="<table width='95%'><tr><td colspan='2' align='center'><h2>"+extensions_pkgs[i].name+"</h2></td></tr>"+t+"<tr><td>";
	for(p in cfg)
		if(was[p]==null) t+="<input type='hidden' name='"+p+"' value='"+(cfg[p]!=null?cfg[p]:"")+"'>";
	t+="</td><td align='right'><input type='button' value='<?=L("Save")?>'onclick='extensions_saveconf("+i+");'></td></tr></table>";
	document.getElementById('statusbg').style.visibility='visible';
	document.getElementById('status').style.visibility='visible';
	document.getElementById('status').innerHTML=t;
	extensions_lastcmd='conf';
}
function extensions_walk(n, f) {
	f(n);
	n = n.firstChild;
	while(n) {
		extensions_walk(n,f);
		n = n.nextSibling;
	}
}
function extensions_saveconf(i)
{
	var t="",i;
	var url="<?=url("/")?>extensions/setconf?item="+encodeURIComponent(extensions_pkgs[i].id);
	if( window.XMLHttpRequest ) {
		var r = new XMLHttpRequest();
		extensions_param="";
		extensions_walk(document.getElementById('status').firstChild, function(n) {
				if(n.tagName=="INPUT" && n.type!="button") {
					extensions_param+=(extensions_param?"&":"")+n.name+"="+encodeURIComponent(n.value);
				}
				if(n.tagName=="SELECT") {
					extensions_param+=(extensions_param?"&":"")+n.name+"="+encodeURIComponent(n.value);
				}
				if(n.tagName=="TEXTAREA") {
					extensions_param+=(extensions_param?"&":"")+n.name+"="+encodeURIComponent(n.value);
				}
		});
		r.open('POST', url, false);
		r.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		r.setRequestHeader("Content-length", extensions_param.length);
		r.setRequestHeader("Connection", "close");
		r.send(extensions_param);
		t=r.status==200?r.responseText:'HTTP-E: '+r.status;
	}
	document.getElementById('status').style.visibility='hidden';
	document.getElementById('statusbg').style.visibility='hidden';
	document.getElementById('status').innerHTML='';
	alert(t);
}
function extensions_cmd(cmd,i)
{
	var t="";
	cookie_set('extensions_scroll',((window.pageYOffset||document.documentElement.scrollTop)-(document.documentElement.clientTop||0))+'');
	if( cmd=="uninstall" ) {
		var bn=extensions_pkgs[i].url.split('/').pop().split('.')[0];
		extensions_me=(bn=="PHPPE_extmgr");
		if(extensions_me && !confirm("<?=L("You are going to remove the Extension Manager itself!")."\\n".L("Are you really really sure?")?>") ) return;
	} else
		extensions_me=false;

	if( i!=-1 && Math.floor(extensions_pkgs[i].price)!=0) t=prompt("<?=L("Product key?")?>" );
	if( i==-1 || Math.floor(extensions_pkgs[i].price)==0 || (t!=null && t!=undefined && t!="") ) {
		document.getElementById('statusbg').style.visibility='visible';
		document.getElementById('status').style.visibility='visible';
		var url="<?=url("/")?>extensions/"+cmd+(i!=-1?"?item="+encodeURIComponent(extensions_pkgs[i].url+(t?"&key="+Sha256.hash(t):"")+"#"+extensions_pkgs[i].id):"");
		extensions_lastcmd=cmd;
		if( window.XMLHttpRequest ) {
			var r = new XMLHttpRequest();
			r.open('GET', url, false); r.send(null);
			t=r.status==200?r.responseText:'HTTP-E: '+r.status;
			document.getElementById('status').style.color=(t.substr(0,7)!="PHPPE-I"?'#FF6060':'#60FF60');
			document.getElementById('status').innerHTML=t;
		} else
			document.getElementById('status').src=url;
	}
}
function extensions_exitcmd()
{
	document.getElementById('status').style.visibility='hidden';
	document.getElementById('statusbg').style.visibility='hidden';
	document.getElementById('status').innerHTML='';
	if(extensions_lastcmd!='conf')
		document.location.href="<?=url("/")?>"+(extensions_me?"":"extensions");
}
function extensions_searchbg()
{
	if(extensions_tmr) { clearTimeout(extensions_tmr); extensions_tmr=null; }
	extensions_tmr = setTimeout("extensions_search(document.getElementById('search').value);",500);
}
function extensions_init()
{
	var i,j,t,ctx=document.getElementById('content'),sc=cookie_get('extensions_scroll'),cnt=0;
	for(j=1;j<extensions_installed.length;j++) {
		var was=0;
		for(i=0;i<extensions_pkgs.length;i++)
			if(extensions_pkgs[i].id==extensions_installed[j].id) {
				extensions_pkgs[i].installed=extensions_installed[j].version;
				if(extensions_pkgs[i].installed<extensions_pkgs[i].version) cnt++;
				was=1;
				break;
			}
		if(!was)
			extensions_pkgs.push({"id":extensions_installed[j].id,"name":"NA "+extensions_installed[j].id,"version":"?.?.?","installed":(extensions_installed[j].version!=undefined&&extensions_installed[j].version!=""?extensions_installed[j].version:"?.?.?"),"desc":"*<?=L("installed, but not in repo")?>*"});
	}
	t="";
	t+="<div style='float:right;width:50%;'><nobr><input id='search' type='text' style='width:70%;' onkeyup='return extensions_searchbg();'><input type='button' style='font-size:28px;padding-top:0px;line-height:24px;vertical-align:middle;width:8%;' onclick='extensions_search(document.getElementById(\"search\").value);' value='⌕'></nobr></div>";
	t+="<div style='padding:5px 10px;'><b>"+(extensions_installed[0]!=null?extensions_installed[0].name:"<?=L("No name")?>")+"</b><br><small>&nbsp;&nbsp;<?=(!empty(PHPPE::$user->data['remote']['user'])?PHPPE::$user->data['remote']['user']."@":"").PHPPE::$user->data['remote']['host'].(!empty(PHPPE::$user->data['remote']['path'])?":".PHPPE::$user->data['remote']['path']:"")?></small></div>";
	t+="<center><nobr><input type='button' value='<?=L("All")?> ("+extensions_pkgs.length+")' style='line-height:24px;width:20%;' onclick='document.getElementById(\"search\").value=\"\";extensions_search();'><input type='button' value='<?=L("Installed")?> ("+(extensions_installed.length-1)+")' style='line-height:24px;width:20%;' onclick='document.getElementById(\"search\").value=\"<?=L("installed")?>\";extensions_search();'><input id='upbtn' type='button' value='<?=L("Upgrade")?> ("+cnt+")' style='line-height:24px;width:20%;' onclick='document.getElementById(\"search\").value=\"<?=L("upgrade")?>\";extensions_search();'><input type='button' value='<?=L("Bootstrap")?>' style='line-height:24px;width:20%;' onclick='extensions_cmd(\"bootstrap\",-1);'></nobr></center>";
	t+="<div id='pkgs'></div><div id='statusbg' onclick='extensions_exitcmd();'></div>";
	if (window.XMLHttpRequest)
		t+="<pre id='status'><?=L("Connecting to host")?>...</pre>";
	else
		t+="<iframe id='status' src='data:text/plain,<?=urlencode(L("Connecting to host"))?>...'></iframe>";
	if(ctx==null||ctx==undefined) ctx=document.body;
	ctx.innerHTML=t;
	extensions_search();
	document.getElementById('search').focus();
	if(sc>0) {
		window.scrollTo(0,0);
		window.scrollBy(0,sc);
		cookie_set('extensions_scroll','',-1);
	}
}
function cookie_get(name) {var index=document.cookie.indexOf(name + "=");if(index==-1)return null;index=document.cookie.indexOf("=",index)+1;var endstr=document.cookie.indexOf(";",index);if(endstr==-1)endstr=document.cookie.length;return unescape(document.cookie.substring(index,endstr));}
function cookie_set(name,value,nDays) {var today=new Date();var expire=new Date();if(nDays==null||nDays<1)nDays=1;expire.setTime(today.getTime()+3600000*24*nDays);document.cookie=name+"="+escape(value)+";path=/;expires="+expire.toGMTString();}
