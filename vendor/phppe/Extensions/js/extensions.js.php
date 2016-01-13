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
 *   http://phppe.org/
 *
 *   Copyright LGPL 2016 bzt
 *
 *   PHPPE Extension Manager
 *
 */

/*PRIVATE VARS*/
var extensions_installed=[ <?=PHPPE::lib("Extensions")->getinstalled()?> ];
var extensions_pkgs=[ <?=PHPPE::lib("Extensions")->getpkgs()?> ];
var extensions_licenses=[],extensions_cats=[];
var extensions_tmr=null, extensions_me=false, extensions_lastcmd="", extensions_param="",extensions_return="",extensions_url="";

/*PUBLIC METHODS*/
function extensions_search(str,installed)
{
	var t="<a name='category_label0'></a>",up=false,down=false;
	if(str==""||str==null||str==undefined)str=document.getElementById("search").value;
	if(str=="<?=L("installed")?>"<?=(L("installed")!="installed"?"||str==\"installed\"":"")?>){str="";installed=true;}
	if(str=="<?=L("upgrade")?>"<?=(L("upgrade")!="upgrade"?"||str==\"upgrade\"":"")?>){str="";up=true;}
	if(str=="<?=L("downgrade")?>"<?=(L("downgrade")!="downgrade"?"||str==\"downgrade\"":"")?>){str="";down=true;}
	if(extensions_tmr) { clearTimeout(extensions_tmr); extensions_tmr=null; }
	var cat="",r=(str!=null && str!=undefined && str!="" ? new RegExp(str.replace(".","\\.").replace("*","\\*").replace("+","\\+").replace("|","\\|"),"i") : null);
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
		try{
		if(up==false && down==false && (!document.getElementById('license_filter'+extensions_licenses.indexOf(extensions_pkgs[i].license)).checked ||
		   !document.getElementById('category_filter'+extensions_cats.indexOf(extensions_pkgs[i].category)).checked
		)) continue;
		}catch(e){}
		if(extensions_pkgs[i].category!=null && extensions_pkgs[i].category!=cat) {
			cat=extensions_pkgs[i].category;
			t+="<br style='clear:both;'><a name='category_label"+extensions_cats.indexOf(cat)+"'><b>"+L(cat)+"</b></a><br style='clear:both;'>"
		}
		t+="<div class='extension' dir='ltr'><img class='preview' title='"+extensions_pkgs[i].url+"' src='data:image/png;base64,"+(extensions_pkgs[i].preview?extensions_pkgs[i].preview:'iVBORw0KGgoAAAANSUhEUgAAAIAAAAB5CAMAAADGfdkoAAAAUVBMVEUAAADDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8OOFo/iAAAAGnRSTlMA+Mgje5u258wWvleUTwtvrUQspPCGYTne1lzKNFwAAAPaSURBVGje7ZrJlqsgEEALBAdUBFHU+v8PfadHTToRRAlvkbvJok8nF6kJEoiC1MzkVKim6yEBVuDKYCZ4LR3BO5ZXKkwFPkBJeBEdPmZ4USyM+JSXbEOHzyEZRGfCPShEp8RdGESmxn2G2Kkg0EEH8ZASMnSRQxx6JophWCi6KKLsQU/RmxiZ2OAB+FTBxeR4kLK71EFhALyKXnpflY8VwUCEjNv73AyXJMSC4ZAeTtPjGYoqbvNxI4IXbhnrWhmUA+fbczUWPwvILJ4kO/7xOa6QAU9iDn8+QwdxHkE9svani8hM112TL+vq40eBLklBzWh1v0kcmU0ta/KyiJ8I/WbXZ2HGrt2KQDXVncBQ+rByR2aqmq1IcGxYj8DbK6ezUOO0zqNRgkC73+REX27AifXayBbD4Oda7rqMOp6A8komFm8LSq+KyuMFYeH1LgbDqE9k4cr0WY/atUJfORZN6EZvK7TuGkWL60qxRQ+KJW+6m1ZRacu4cHqwgCzcL4zbAv27MTPBJ1TgICy8Z8GZ1dnGo9dNYBUAgcEUHx1LZ3KdYEOurWY8y7Dk3eOCwiCgF4YPfyZoGpnwGqa/Nb2Qh88eM+eUBA8ey97hUEqPLGRfiWV5edyiBTncrv/m8+0wiLF9FJNs+P2PaZvenZoPzt/93vFcfT9ixfS9hextk8+fNaPqb+NTM7X4ZsLtyEIsbLn948I7Lf8eB1vQAxb5WN8aZvWoiqMTwyhd5Wag3P7pU/QnFPOxre70hHvwUd+Lp1Z63vXM+SYs7vN4UUxvLdrCdSfEqDKcaXmw3syq09WTkkAW3v1aVMtuEpwr+SW3k3wynBHa1F8Gw6kDkHXGsRifSs6t4wBBJLiQeIp6f3wtwU1+/qBfdWYJv4ho8RTqp3k93KcRPCB4Crn3KC14YE5Gwd5KJvBAX3Xpx4/NnivDyT14Hk0zuFnNT991ZI7jR/w9KBzTd6w96Ne2eQe78uunxfDHGP0bBPyO/rrbd1JDTGaP6Toqo8+NjmVumg/GzQtv2kv2oAVool4LL26B0vvLGIrfveHrrUvwgLkLvkA3FP4KGAoeZOcF1tIr8PvM8aVDr/gNRu05uFBjlCGojFJm+HgxuWc57EIF3DTgQ+UUmJcsCMzBC+ESKCgE4StgnQJlXAGZWgBEaoE6tYBMLQAqtUCbWkCSxAJgUgvo1AIwpBJwj4ZtVIEVmloAeGoBqGq+JBNYJRpKEgmsEu0oSEKBVSKZwHo1LwZEm0BgRepxSiSw8hZ4C7wF3gJvgbfAW+At8Bb4HwRoYoEZSRCXCdSqpAGURsMT/gG5cUM2oGYBXgAAAABJRU5ErkJggg==');
		t+="' style='"+(LANG['rtl']!=null&&LANG['rtl']!=''&&LANG['rtl']!=false?"padding-left:16px;":"")+"position:absolute;width:128px !important;' alt='"+extensions_pkgs[i].id+"'><div"+(LANG['rtl']!=null&&LANG['rtl']!=''&&LANG['rtl']!=false?" dir='rtl'":"")+">";
		t+=extensions_pkgs[i].name+" "+extensions_pkgs[i].version+(extensions_pkgs[i].installed?" <span class='installed' style='color:green;'>(<?=L("installed")?> "+extensions_pkgs[i].installed+")</span>":"")+"<br>";
		if( extensions_pkgs[i].url != undefined ) {
			var u="",s=extensions_pkgs[i].size;
			if(s>1024*1024) { u="M"; s=Math.round(s/1024/1024); }
			if(s>1024) { u="K"; s=Math.round(s/1024); }
			t+="<small dir='ltr' class='details' style='font-style:italic;'>"+extensions_pkgs[i].id+" ("+s+" "+u+"b, "+L(extensions_pkgs[i].license)+", "+extensions_pkgs[i].maintainer+")</small><br>";
			if(Math.floor(extensions_pkgs[i].price)!=0)
			t+="<input type='button' class='button' onclick='"+(extensions_pkgs[i].homepage? "window.open(\""+extensions_pkgs[i].homepage : "alert(\"<?=L("No webshop url given")?>" )+"\");' value='<?=L("Buy")?> ("+extensions_pkgs[i].price+"&euro;)' style='background:#B0B0F0 linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%);'>&nbsp;&nbsp;&nbsp;&nbsp;";
			if(!extensions_pkgs[i].installed) {
				t+="<input type='button' class='button' onclick='extensions_cmd(this,\"install\","+i+");' value='⬇' style='width:24px;font-size:16px;line-height:24px;' title='<?=L("Install")?>' style='"+(Math.floor(extensions_pkgs[i].price)!=0?"background:#B0B0F0 linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%);":"")+"'>";
			} else if(extensions_pkgs[i].installed<extensions_pkgs[i].version) t+="<input type='button' class='button' onclick='extensions_cmd(this,\"install\","+i+");' value='<?=L("Upgrade to")?> "+extensions_pkgs[i].version+"' style='color:green;background:#B0F0B0 linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%);'>";
			else if(extensions_pkgs[i].installed>extensions_pkgs[i].version) t+="<input type='button' class='button' onclick='if(confirm(\"<?=L("sure")?>\"))extensions_cmd(this,\"install\","+i+");' value='<?=L("Downgrade to")?> "+extensions_pkgs[i].version+"'>";
			else t+="<input type='button' class='button' onclick='extensions_cmd(this,\"install\","+i+");' value='⟳' style='font-size:24px;line-height:24px;' title='<?=L("Reinstall")?>'>";
			if(extensions_pkgs[i].config!="" && window.XMLHttpRequest && (extensions_pkgs[i].installed||extensions_pkgs[i].id=="phppe")) t+="<input type='button' class='button' onclick='extensions_conf(this,"+i+");' value='⚒' style='font-size:22px;line-height:24px;' title='<?=L("Configure")?>'>";
			if(extensions_pkgs[i].installed) {
				t+="&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' class='button' onclick='if(confirm(\"<?=L("sure")?>\"))extensions_cmd(this,\"uninstall\","+i+");' value='♻' title='<?=L("Remove")?>' style='line-height:24px;font-size:18px;background:#F0B0B0 linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%);'>";
			}
				try {
					var j,k,l;
					for(j=0;j<extensions_installed.length;j++) {
						if(extensions_installed[j].id==null) continue;
						var idx=extensions_pkgs[i].depends.indexOf(extensions_installed[j].id);
						if(idx>-1) extensions_pkgs[i].depends.splice(idx,1);
					}
					if(extensions_pkgs[i].depends.length>0)
						t+="<br><small"+(extensions_pkgs[i].installed?" style='color:red;'":"")+">"+L(extensions_pkgs[i].installed?"Failed dependency":"Also installs")+": "+extensions_pkgs[i].depends.join(", ")+"</small>";
				} catch(e) {}
		}
		t+="<br><small class='desc' style='color:#808080;'>"+(extensions_pkgs[i].desc?extensions_pkgs[i].desc.replace(/\b(http[^\'\"\ \t\r\n\;\,!\<\>]+)/,"<a href='$1' target='_new'>$1</a>"):'<?=L("No description")?>')+"<br><small>"+extensions_pkgs[i].time+"   "+extensions_pkgs[i].sha1+"</small></small></div></div>";
	}
	if(t=="") t="<br style='clear:both;'><i><?=L("No match found.")?></i>";
	document.getElementById('pkgs').innerHTML=t;
	return true;
}
function extensions_conf(obj,i)
{
	var waserr="",t="",tabs="",p="",cfg=new Array(),was=new Array(),wasinp=false;
	if(i==-1 || extensions_pkgs[i].config=="") return;
	var url="<?=url("extensions")?>getconf?item="+encodeURIComponent(extensions_pkgs[i].id);
	if( window.XMLHttpRequest ) {
		var r = new XMLHttpRequest();
		r.open('GET', url, false); r.send(null);
		try {
		if(r.status==200) cfg=JSON.parse(r.responseText);
		else alert('HTTP-E: '+r.status);
		} catch(e) {
			if(r.responseText!=null&&r.responseText!=undefined&&r.responseText!='')
			    waserr=L('Error reading configuration')+': '+r.responseText;
			cfg=new Array();
		}
	} else return;

	var first=null;for(p in extensions_pkgs[i].config){first=p;break;}
	var isstr=typeof extensions_pkgs[i].config[first] == 'string';
	var carr=isstr?{"Basic":extensions_pkgs[i].config}:extensions_pkgs[i].config;
	var tn=0;
	for (var tab in carr) {
		tabs+="<b class='conftab"+(tab==first?"_active":"")+"' onclick='extensions_conftab("+i+","+tn+");'>"+(extensions_pkgs[i].conf.hasOwnProperty(tab)?extensions_pkgs[i].conf[tab]:tab)+"</b>";
		t+="<table class='conftable' id='conftable"+(tn++)+"'"+(tab==first||isstr?"":" style='display:none;'")+">";
		for(p in carr[tab]) {
			var m=carr[tab][p].match(/^(\*)?([^\(\*]+)\(?([^\)]*)/);
			var a=m[3]!=null?m[3].split(','):new Array();
			if(m[2]==null) continue;
			was[p]=1;
			wasinp=true;
				t+="<tr><td>"+(extensions_pkgs[i].conf.hasOwnProperty(p)?extensions_pkgs[i].conf[p]:p)+":</td><td width='100%' title='"+(extensions_pkgs[i].help.hasOwnProperty(p)?extensions_pkgs[i].help[p]:'')+"'>";
			switch(m[2]) {
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
				case "integer":
					t+="<input type='number' min='"+a[0]+"' max='"+a[1]+"' value='"+a[2]+"'>";
					break;
				case "boolean":
				case "notboolean":
					var bv=(cfg[p]!=null?(Math.floor(cfg[p])==1||cfg[p]=="true"?"true":"false"):(Math.floor(a[0])==1||a[0]=="true"?"true":"false"));
					if(boolean_open) {
						t+="<span dir='ltr'>"+boolean_open(p,bv,m[2],a)+"</span>";
					} else {
						var t1="<option value=''>"+(a[2]!=null?a[2]:L('No'))+"</option>";
						var t2="<option value='"+(a[0]!=null?a[0]:'true')+"'>"+(a[1]!=null?a[1]:L('Yes'))+"</option>";
						t+="<select name='"+p+"'>";
						if(m[2]=='boolean')
							t+=t1+t2;
						else
							t+=t2+t1;
						t+="</select>";
					}
					break;
				case "textarea":
					t+="<textarea name='"+p+"' style='width:98%' rows='3' wrap='virtual'>"+(cfg[p]!=null?cfg[p]:(m[1]?m[3]:""))+"</textarea><br>";
					break;
				case "url":
					t+="<span dir='ltr'><small>http://("+L("remote base")+")/</small><input type='text' name='"+p+"' style='width:33%;' value='"+(cfg[p]!=null?cfg[p]:(m[1]?m[3]:""))+"' placeholder='"+m[3]+"'></span>";
					break;
				case "string":
				default:
					t+="<input type='text' name='"+p+"' style='width:98%;' value='"+(cfg[p]!=null?cfg[p]:(m[1]?m[3]:""))+"' placeholder='"+m[3]+"'>";
					break;
			};
			t+="</td></tr>\n";
		}
		t+="</table>";
	}
	if(t==""||!wasinp) return;
	t="<table id='confhdr"+i+"' width='95%'><tr><td colspan='2' align='center'><h2>"+extensions_pkgs[i].name+"</h2></td></tr>"+(waserr!=""?"<tr><td colspan='2' style='color:#FEA0A0;background:rgba(128,0,0,0.6);'>"+waserr+"</td></tr>":"")+"<tr><td>"+(isstr?'':tabs)+"</td><td><input type='button' class='button' style='float:"+(LANG['rtl']!=null&&LANG['rtl']!=''&&LANG['rtl']!=false?'left':'right')+";color:#fff;background:rgba(64,64,64,0.8) linear-gradient(to bottom,rgba(0,0,0,0.2) 5%,rgba(64,64,64,0.8) 90%,rgba(128,128,128,0.2) 5%);border:outset #404040;' value='<?=L("Save")?>'onclick='extensions_saveconf("+i+");'></td></tr></table>"+t+"<table width='95%'><tr><td>";
	for(p in cfg)
		if(was[p]==null) t+="<input type='hidden' name='"+p+"' value='"+(cfg[p]!=null?cfg[p]:"")+"'>";
	t+="</td><td><input type='button' class='button' style='float:"+(LANG['rtl']!=null&&LANG['rtl']!=''&&LANG['rtl']!=false?'left':'right')+";color:#fff;background:rgba(64,64,64,0.8) linear-gradient(to bottom,rgba(0,0,0,0.2) 5%,rgba(64,64,64,0.8) 90%,rgba(128,128,128,0.2) 5%);border:outset #404040;' value='<?=L("Save")?>'onclick='extensions_saveconf("+i+");'></td></tr></table>";
	document.getElementById('statusbg').style.visibility='visible';
	document.getElementById('status').innerHTML=t;
	document.getElementById('statusbg').style.visibility='visible';
	if(<?=empty(PHPPE::$core->noanim)?'false':'true'?> || typeof jQuery=='undefined'){
		document.getElementById('status').style.visibility='visible';
	} else {
	    var a=obj,x=0,y=0;
	    if(a.offsetParent) {
		do {
		    x += a.offsetLeft - a.scrollLeft ;//+ a.clientLeft;
		    y += a.offsetTop - a.scrollTop ;//+ a.clientTop;
		} while (a = a.offsetParent);
		x-=Math.floor((window.pageXOffset?window.pageXOffset:document.body.scrollLeft));
		y-=Math.floor((window.pageYOffset?window.pageYOffset:document.body.scrollTop));
	    }
	    document.getElementById('status').style.left=x+'px';
	    document.getElementById('status').style.top=y+'px';
	    document.getElementById('status').style.width=obj.offsetWidth+'px';
	    document.getElementById('status').style.height=obj.offsetHeight+'px';
		document.getElementById('status').style.visibility='visible';
		extensions_return={left:x+'px',top:y+'px',width:obj.offsetWidth+'px',height:obj.offsetHeight+'px'};
		$('#status').animate({left:'20%',top:'20%',width:'60%',height:'60%'},500);
	}
	extensions_lastcmd='conf';
}

function extensions_conftab(i,tn)
{
	var hdr=document.getElementById('confhdr'+i);
	var b=hdr.getElementsByTagName("B");
	for(var j in b)
		b[j].className="conftab"+(j==tn?"_active":"");
	var j=0,tabs=hdr.nextSibling;
	while(tabs.className!=null && tabs.className.indexOf("conftable")>-1) {
		tabs.style.display=j++==tn?"block":"none";
		tabs = tabs.nextSibling;
	}
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
	var url="<?=url("extensions")?>setconf?item="+encodeURIComponent(extensions_pkgs[i].id);
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
	extensions_exitcmd();
/*
	document.getElementById('status').style.visibility='hidden';
	document.getElementById('statusbg').style.visibility='hidden';
	document.getElementById('status').innerHTML='';
*/
	if(t) alert(t);
}
function extensions_cmd(obj,cmd,i)
{
	var t="";
	cookie_set('extensions_scroll',((window.pageYOffset||document.documentElement.scrollTop)-(document.documentElement.clientTop||0))+'');
	if( cmd=="uninstall" ) {
		var bn=extensions_pkgs[i].url.split('/').pop().split('.')[0];
		extensions_me=(bn=="PHPPE_extmgr");
		if(extensions_me && !confirm("<?=L("You are going to remove the Extension Manager itself!")."\\n".L("sure")?>") ) return;
	} else
		extensions_me=false;

	if( i!=-1 && Math.floor(extensions_pkgs[i].price)!=0) t=prompt("<?=L("Product key?")?>" );
	if( i==-1 || Math.floor(extensions_pkgs[i].price)==0 || (t!=null && t!=undefined && t!="") ) {
		extensions_url="<?=url("extensions")?>"+cmd+(i!=-1?"?item="+encodeURIComponent(extensions_pkgs[i].url+(t?"&key="+Sha256.hash(t):"")+"#"+extensions_pkgs[i].id):"");
		extensions_lastcmd=cmd;
		document.getElementById('statusbg').style.visibility='visible';
		if(<?=empty(PHPPE::$core->noanim)?'false':'true'?> || typeof jQuery=='undefined'){
			document.getElementById('status').style.visibility='visible';
			extensions_loadmodal();
		} else {
			obj.style.position='relative';
		    var a=obj,x=0,y=0;
		    var ww=(window.innerWidth?window.innerWidth:document.body.offsetWidth);var wh=(window.innerHeight?window.innerHeight:document.body.offsetHeight);
			do {
			    x += a.offsetLeft - a.scrollLeft ;//+ a.clientLeft;
			    y += a.offsetTop - a.scrollTop ;//+ a.clientTop;
			} while (a = a.offsetParent);
			x-=Math.floor((window.pageXOffset?window.pageXOffset:document.body.scrollLeft));
			y-=Math.floor((window.pageYOffset?window.pageYOffset:document.body.scrollTop));
		    document.getElementById('status').setAttribute('style','left:'+x+'px;top:'+y+'px;width:'+obj.offsetWidth+'px;height:'+obj.offsetHeight+'px;visibility:visible;');
			extensions_return={left:x+'px',top:y+'px',width:obj.offsetWidth+'px',height:obj.offsetHeight+'px'};
			x=Math.floor(/*(window.pageXOffset?window.pageXOffset:document.body.scrollLeft)+*/ww*0.2);
			y=Math.floor(/*(window.pageYOffset?window.pageYOffset:document.body.scrollTop)+*/wh*0.2);
			$('#status').animate({left:x+'px',top:y+'px',width:'60%',height:'60%'},750,extensions_loadmodal);
		}
	}
}
function extensions_loadmodal()
{
		var t;
		if( window.XMLHttpRequest ) {
			var r = new XMLHttpRequest();
			r.open('GET', extensions_url, false); r.send(null);
			t=r.status==200?r.responseText:'HTTP-E: '+r.status;
			document.getElementById('status').style.color=(t.substr(0,7)!="PHPPE-I"&&t.substr(0,5)!="DIAG-"?'#FF6060':'#60FF60');
			document.getElementById('status').innerHTML=t;
		} else
			document.getElementById('status').src=url;
}
function extensions_hidestatus()
{
	document.getElementById('status').style.visibility='hidden';
	document.getElementById('status').innerHTML='';
	if(extensions_lastcmd!='conf')
		document.location.href=(extensions_me?"<?=url("/")?>":"<?=url("extensions")?>");
}
function extensions_exitcmd()
{
	document.getElementById('statusbg').style.visibility='hidden';
	if(<?=empty(PHPPE::$core->noanim)?'false':'true'?> || typeof jQuery=='undefined'){
		extensions_hidestatus();
	} else {
		$('#status').animate(extensions_return,750,extensions_hidestatus);
	}
}
function extensions_searchbg()
{
	if(extensions_tmr) { clearTimeout(extensions_tmr); extensions_tmr=null; }
	extensions_tmr = setTimeout("extensions_search(document.getElementById('search').value);",500);
}
function extensions_init()
{
	var i,j,t,ctx=document.getElementById('content'),sc=cookie_get('extensions_scroll'),cnt=0;
	for(i=0;i<extensions_pkgs.length;i++) {
		if(extensions_licenses.indexOf(extensions_pkgs[i].license)==-1)
			extensions_licenses.push(extensions_pkgs[i].license);
		if(extensions_cats.indexOf(extensions_pkgs[i].category)==-1)
			extensions_cats.push(extensions_pkgs[i].category);
		for(j=1;j<extensions_installed.length;j++) {
			if(extensions_pkgs[i].id==extensions_installed[j].id) {
				extensions_installed[j].idx=i;
				extensions_pkgs[i].installed=extensions_installed[j].version;
				if(extensions_pkgs[i].installed<extensions_pkgs[i].version) cnt++;
			}
		}
	}
	for(j=1;j<extensions_installed.length;j++)
		if(extensions_installed[j].idx==null||extensions_installed[j].idx==undefined)
			extensions_pkgs.push({"id":extensions_installed[j].id,"name":"NA "+extensions_installed[j].id,"version":"?.?.?","installed":(extensions_installed[j].version!=undefined&&extensions_installed[j].version!=""?extensions_installed[j].version:"?.?.?"),"desc":"*<?=L("installed, but not in repo")?>*"});
	t="<br style='line-height:1px;clear:both;'><div id='extensions_panel'>";
	t+="<div style='float:right;width:50%;'><nobr><input id='search' type='text' style='width:70%;height:26px;' onkeyup='return extensions_searchbg();'><input type='button' class='button' style='font-size:28px;padding-top:0px;line-height:24px;vertical-align:middle;width:8%;' onclick='extensions_search(document.getElementById(\"search\").value);' value='⌕'></nobr></div>";
	t+="<div style='padding:5px 10px;'><b>"+(extensions_installed[0]!=null?extensions_installed[0].name:"<?=L("No name")?>")+"</b><br><small>&nbsp;&nbsp;<?=(!empty(PHPPE::$user->data['remote']['user'])?PHPPE::$user->data['remote']['user']."@":"").PHPPE::$user->data['remote']['host'].(!empty(PHPPE::$user->data['remote']['path'])?":".PHPPE::$user->data['remote']['path']:"")?></small></div>";
	t+="<center><nobr><button class='button' style='margin:5px;line-height:20px;width:20%;' onclick='document.getElementById(\"search\").value=\"\";extensions_search();'><?=L("All")?> <small dir='ltr'>("+extensions_pkgs.length+")</small></button>";
	t+="<button class='button' style='margin:5px;line-height:20px;width:20%;' onclick='document.getElementById(\"search\").value=\"<?=L("installed")?>\";extensions_search();'><?=L("Installed")?> <small dir='ltr'>("+(extensions_installed.length-1)+")</small></button>";
	t+="<button id='upbtn' class='button' style='margin:5px;line-height:20px;width:20%;"+(cnt>0?"color:green;background:#B0F0B0 linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%);":"")+"' onclick='document.getElementById(\"search\").value=\"<?=L("upgrade")?>\";extensions_search();'><?=L("Upgrade")?> <small dir='ltr'>("+cnt+")</small></button>";
	t+="<button class='button' style='margin:5px;line-height:20px;width:20%;' onclick='extensions_cmd(this,\"bootstrap\",-1);'><?=L("Diagnostics")?></button></nobr></center>";
	t+="<div style='display:inline-block;margin-right:30px;'>";
	for(i=0;i<extensions_licenses.length;i++){
		t+="<span><input class='input' type='checkbox' name='license_filter"+i+"' id='license_filter"+i+"' value='"+extensions_licenses[i]+"' checked onchange='extensions_search(document.getElementById(\"search\").value);'><label for='license_filter"+i+"'><small>"+L(extensions_licenses[i])+"</small></label></span>";
	}
	t+="</div><div style='display:inline-block;'>";
	for(i=0;i<extensions_cats.length;i++){
		t+="<span><input class='input' type='checkbox' name='category_filter"+i+"' id='category_filter"+i+"' value='"+extensions_cats[i]+"' checked onchange='extensions_search(document.getElementById(\"search\").value);'><a href='extensions#category_label"+i+"'><small>"+L(extensions_cats[i]!=""?extensions_cats[i]:"Framework")+"</small></a></span>";
	}
	t+="</div></div>";
	t+="<div id='pkgs'></div><div id='statusbg' onclick='extensions_exitcmd();'></div>";
	if (window.XMLHttpRequest)
		t+="<pre id='status'><?=L("Connecting to host")?>...</pre>";
	else
		t+="<iframe id='status' src='data:text/plain,<?=urlencode(L("Connecting to host"))?>...'></iframe>";
	t+="<br style='clear:both;'>";
	if(ctx==null||ctx==undefined) ctx=document.body;
	ctx.innerHTML=t;
	extensions_search();
	document.getElementById('search').focus();
	if(sc>0) {
		window.scrollTo(0,0);
		window.scrollBy(0,sc);
		cookie_set('extensions_scroll','',-1);
	}
    if ( window.addEventListener )
        window.addEventListener( "scroll", extensions_header, false );
    else if ( window.attachEvent )
        window.attachEvent( "onscroll", extensions_header );
    else
        window["onscroll"]=extensions_header;
}
function extensions_header(evt) {
	var scrx=window.pageXOffset?window.pageXOffset:document.body.scrollLeft,scry=window.pageYOffset?window.pageYOffset:document.body.scrollTop;
	var ep,t,ow=0,oh=0,ox=0,oy=0;
	ep=document.getElementById('extensions_panel');
	if(ep==null) return;
	t=ep;
	ow=t.offsetWidth;
	oh=t.offsetHeight;
	if(ep.getAttribute("data-x")!=null) {
		ox=ep.getAttribute("data-x");
		oy=ep.getAttribute("data-y");
	} else {
		while (t.offsetParent) {
			ox += t.offsetLeft - t.scrollLeft ;//+ t.clientLeft;
			oy += t.offsetTop - t.scrollTop ;//+ t.clientTop;
			t = t.offsetParent;
		}
		ox-=Math.floor((window.pageXOffset?window.pageXOffset:document.body.scrollLeft));
		oy-=Math.floor((window.pageYOffset?window.pageYOffset:document.body.scrollTop));
		ep.setAttribute("data-x",ox);
		ep.setAttribute("data-y",oy);
   	}
	if(scry>oy-(pe_ot>0?pe_ot:0) || scrx>ox) {
		ep.setAttribute('style','position:fixed;top:'+(pe_ot>0?pe_ot:0)+'px;left:'+(ox-scrx)+'px;width:'+ow+'px;');
		ep.nextSibling.setAttribute('style','margin-top:'+oh+'px;');
	} else {
		ep.setAttribute('style','position:relative;width:100%;');
		ep.nextSibling.setAttribute('style','margin-top:0px;');
	}}
function cookie_get(name) {var index=document.cookie.indexOf(name + "=");if(index==-1)return null;index=document.cookie.indexOf("=",index)+1;var endstr=document.cookie.indexOf(";",index);if(endstr==-1)endstr=document.cookie.length;return unescape(document.cookie.substring(index,endstr));}
function cookie_set(name,value,nDays) {var today=new Date();var expire=new Date();if(nDays==null||nDays<1)nDays=1;expire.setTime(today.getTime()+3600000*24*nDays);document.cookie=name+"="+escape(value)+";path=/;expires="+expire.toGMTString();}
