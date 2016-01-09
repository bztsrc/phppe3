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
 * @file vendor/phppe/wysiwyg/js/wysiwyg.js.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief HTML5 compatible WYSIWYG Editor
 */
use PHPPE\Core as PHPPE;

//! convert text to image (neat trick that makes the whole tag draggable in edit mode)
if(!empty(PHPPE::$core->item)){
    header( "Content-Type: image/gif" );
    header( "Pragma:cache" );
    header( "Cache-Control:cache,public,max-age=86400" );
    header( "Connection:close");
    $str=str_replace(array("!2F!","!2B!","!2f!","!2b!"),array("/","+","/","+"),urldecode(stripslashes(PHPPE::$core->item)));
    if(strtolower(substr($str,0,8))=="<!widget") {
        $d=explode(" ",trim(substr($str,9,strlen($str)-10)));
        if($d[0][0]=="@") array_shift($d);
        list($d)=explode("(",$d[0]);
        $f=glob("vendor/phppe/*/addons/".str_replace("..","",$d).".png");
        if(!empty($f[0]) && file_exists($f[0]) && $data=@file_get_contents($f[0])) die($data);
    }
    $im=imagecreate(mb_strlen($str)*6+4,14);
    if(strtolower(substr($str,0,5))=="<!cms") {
        $bg=imagecolorallocate($im,220,0,0);
        $fg=imagecolorallocate($im, 239, 254, 255);
    } else {
        $bg=imagecolorallocate($im,0,0,108);
        $fg=imagecolorallocate($im, 239, 254, 255);
    }
    imagestring($im, 2, 2, 0, $str,$fg);
    imagegif($im);
    imagedestroy($im);
    die();
}
//! file import helper
if(isset($_REQUEST['impform'])){
    $err=$choose="";
    if(is_array($_FILES['upload'])) {
        if($_FILES['upload']['type']!="text/html")
            $err=L("Upload HTML only!");
        else {
            $d=@file_get_contents($_FILES['upload']['tmp_name']);
            preg_match_all("|<body[^>]*>(.*?)<\/body|ims",$d,$b,PREG_SET_ORDER);
            $choose=!empty($b[0][1])?$b[0][1]:$d;
        }
    }
    die("<html><head><meta charset='utf-8'/></head><body>".($choose?"<div>".$choose."</div><script type='text/javascript'>parent.window['wysiwyg_importdone'](\"".$_REQUEST['impform']."\");</script>":
        PHPPE::_t("<!form>").
        "<input type='hidden' name='impform' value='".$_REQUEST['impform']."'>".
        "<input type='file' name='upload' onchange='parent.window[\"wysiwyg_importstart\"](\"".$_REQUEST['impform']."\");this.form.submit();' id='".$_REQUEST['impform'].":import'>".
        ($err?"alert('".$err."');":"").
        "</form>")."</body></html>");
}
?>
var wysiwyg_value=null;
var wysiwyg_gecko=false;
var wysiwyg_webkit=false;
var wysiwyg_src=new Array();
var wysiwyg_selected_sel=null;
var wysiwyg_selected_txt=null;
var wysiwyg_selected_obj=null;
var wysiwyg_selected_par=null;
var wysiwyg_zc=1;
var wysiwyg_customhooks='';

function wysiwyg_selected(id,evt)
{
        //function to store range of current selection
        wysiwyg_selected_obj=null;
        if (document.selection) {
                var selection=document.selection;
                if (selection!=null) {
			            wysiwyg_selected_sel=selection;
                        wysiwyg_selected_txt=selection.createRange();
                        if (wysiwyg_selected_txt.item) {
                                wysiwyg_selected_obj=wysiwyg_selected_txt.item(0);
                                wysiwyg_selected_par=wysiwyg_selected_obj.parentNode;
                        } else {
                                wysiwyg_selected_par=wysiwyg_selected_txt.parentElement();
                        }
                }
        } else {
                var selection=window.getSelection();
		        wysiwyg_selected_sel=selection;
                wysiwyg_selected_txt=selection.getRangeAt(selection.rangeCount - 1).cloneRange();
                wysiwyg_selected_obj=selection.anchorNode.childNodes[ selection.anchorOffset ];
                wysiwyg_selected_par=selection.anchorNode.parentNode;
        }
        if(wysiwyg_selected_obj==null&&evt!=null)wysiwyg_selected_obj=evt.target;
        return wysiwyg_selected_txt;
}
function wysiwyg_refreshtools(evt,id)
{
    if(!evt)evt=window.event;
    var o=evt.target;
    wysiwyg_selected(id,evt);
    var tools=document.getElementById(id+':tools');
    var conf=document.getElementById(id+':conf');
    var imgicons=document.getElementById(id+':imgicons');
    var tblicons=document.getElementById(id+':tblicons');
    if(evt.target!=null&&evt.target.tagName=="A"){
        var t="";
        if(conf) {
        var h,hooks=conf.getAttribute('data-linkhook');
        if(hooks) hooks=hooks.split(',');
        for(h in hooks) {
            if(typeof window[hooks[h]]=='function')
                t+=window[hooks[h]](evt,id,conf);
        }
        if(t) {
            conf.style.display='inline';
            conf.innerHTML=t;
        } else conf.style.display='none';
        }
       if(tblicons) tblicons.style.display='none';
       if(imgicons) imgicons.style.display='none';
       if(tools) tools.style.display='none';
       wysiwyg_link(id,evt.target);
    } else
    if(evt.target!=null&&evt.target.tagName=="IMG"){
	if(((evt.target.alt!=null)&&(evt.target.alt.substr(0,2)=="<!"))||(evt.target.className.indexOf("wysiwyg_icon")>-1)) {
        var t="";
        if(conf) {
        var h,hooks=conf.getAttribute('data-hook');
        if(hooks) hooks=hooks.split(',');
	    conf.style.display='inline';
        for(h in hooks) {
            if(typeof window[hooks[h]]=='function')
                t+=window[hooks[h]](evt,id,conf);
        }
	    if(t) conf.innerHTML=t;
	    else conf.innerHTML=evt.target.alt.replace('<','&lt;')+"<br>No hook";
	    var f=conf.getElementsByTagName('FORM');if(f!=null&&f[0]!=null&&f[0].elements[0]!=null)f[0].elements[0].focus();
    }
	} else if(imgicons) {
        var t="";
        if(conf) {
            var h,hooks=conf.getAttribute('data-imghook');
            if(hooks) hooks=hooks.split(',');
            for(h in hooks) {
                if(typeof window[hooks[h]]=='function')
                    t+=window[hooks[h]](evt,id,conf);
            }
            if(t) {
                    conf.style.display='inline';
                    conf.innerHTML=t;
                } else conf.style.display='none';
            }
            imgicons.style.display='inline';
        }
	   if(tblicons) tblicons.style.display='none';
       if(tools) tools.style.display='none';
    } else {
       if(tblicons) tblicons.style.display='none';
       if(imgicons) imgicons.style.display='none';
       if(tools) tools.style.display='inline-block';
       if(conf) conf.style.display='none';
    }
    return false;
}
function wysiwyg_setattr(obj)
{
    if(wysiwyg_selected_obj==null||obj==null||obj.name==null||obj.name=='') return;
    if(obj.value==null||obj.value==''||obj.value=='0') wysiwyg_selected_obj.removeAttribute(obj.name);
    else wysiwyg_selected_obj.setAttribute(obj.name,obj.value);
}
function wysiwyg_keypress(evt)
{
    if(!evt)evt=window.event;
    var c=evt.keyCode?evt.keyCode:evt.which;
}
function wysiwyg_setvalue(id)
{
    var rte = document.getElementById(id);
    if (rte.value==null) rte.value="";
    rte.value = document.getElementById(id+':frame').innerHTML;
    //rte.value=rte.value.replace(">&quot;\"",">\"").replace(/&lt;!([^-])/gi,"<!$1").replace(/\=\"\"/g,"").replace(/\"\=\"\"/g,"\"").replace(/\=\"\"/g,"").replace(/alt=\"[\ ]+/gmi,"alt=\"").replace(/&lt;img/g,"<img");
    var i,txt=rte.value.match(/<img class=[\"\'][\ ]?wysiwyg_icon[^>]+>([\'\"][^>]*?>)?/gmi,"$1");
    if(txt!=null&&txt.length>0) for(i=0;i<txt.length;i++) {
    	var tmp=txt[i].match(/alt=\"[^\"]*[\">]?/gmi,"$1");
    	if(tmp&&tmp[0]) {var t=tmp[0].substring(5,tmp[0].length-1).trim();t=t.replace("<!/form>","</form>");
        //if(t.match(/^<!iframe/i))t="<"+t.substring(2,t.length)+(t.length<10||t.substr(t.length-9,9)!="<"+"/iframe>"?"<"+"/iframe>":"");else
        t=t.replace(/&lt;/g,"<").replace(/&gt;/g,">").replace(/&quot;/g,"\"").replace(/&amp;/g,"&").trim();
    	if(t.charAt(t.length-1)!='>') t=t+'>';
    	rte.value=rte.value.replace(txt[i],t).replace("&lt;"+txt[i].substring(1,txt[i].length),t);}
    }
    rte.value=rte.value.replace(/(<!for[^>]+>)(^<!\/for)*?(<table(.|[\r\n])+?\/tr>)/im,"$3$1");
    rte.value=rte.value.replace(/(<\/table>)((^<!for)*?<br[^>]*?>)*?(.|[\r\n])*?(<!\/for[^>]+>)/im,"$5$1");
    rte.value=rte.value.replace(/(<\/tbody>)((^<!for)*?<br[^>]*?>)*?(.|[\r\n])*?(<!\/for[^>]+>)/im,"$5$1");
    rte.value=rte.value.replace(/(<!for[^>]+>)(^<!\/for)*?(<[uo]l[^>]*?>)/im,"$3$1");
    rte.value=rte.value.replace(/(<\/[uo]l>)(.|[\r\n])*?(<!\/for[^>]+>)/im,"$3$1");
    rte.value=rte.value.replace(/<br[\/]?>([^\n])/gi,"<br>\n$1").replace(/<\/([a-z]+)>([^\n])/gi,"</$1>\n$2").replace(/<!\/([^>]+)>([^\n]?)/g,"<!/$1>\n$2").replace(/([{};])([^\n])/g,"$1\n$2").replace(/(&[a-z0-9#]+;)\n/gi,"$1");
}

function wysiwyg_zoom(evt,id)
{
    if(!evt)evt=window.event;
    if(wysiwyg_selected_obj.tagName!="IMG")return;
    if(wysiwyg_selected_obj.getAttribute('data-zoom')==null)
        wysiwyg_selected_obj.setAttribute('data-zoom',typeof wysiwyg_zoomsrc=='function'?wysiwyg_zoomsrc(wysiwyg_selected_obj.src):wysiwyg_selected_obj.src);
    else
        wysiwyg_selected_obj.removeAttribute('data-zoom');
}
function wysiwyg_togglesrc(id)
{
    var tools=document.getElementById(id+':tools');
    var conf=document.getElementById(id+':conf');
    var icons=document.getElementById(id+':icons');
    var style=document.getElementById(id+':style');
    var imgicons=document.getElementById(id+':imgicons');
    var tblicons=document.getElementById(id+':tblicons');
    var tw=document.getElementById(id).offsetWidth-1;
    var th=document.getElementById(id).offsetHeight-1;
    if(!wysiwyg_src[id])
        document.getElementById(id).style.height=document.getElementById(id+':frame').style.height;
    document.getElementById(id+':frame').style.display=(wysiwyg_src[id]?"block":"none");
    document.getElementById(id).style.display=(wysiwyg_src[id]?"none":"block");
    document.getElementById(id+':source').style='background-position:'+(wysiwyg_src[id]?'0':'-24')+'px 0px';
    wysiwyg_src[id]=1-wysiwyg_src[id];
    if(tblicons) tblicons.style.display="none";
    if(wysiwyg_src[id]){
	if(tools) tools.style.display="none";
	if(conf) conf.style.display="none";
    style.style.display="none";
	icons.style.display="none";
	wysiwyg_setvalue(id);
    } else {
	var output,tags,i;
	if(tools) { tools.style.display="inline-block"; }
	if(conf) conf.style.display="none";
	if(icons) icons.style.display="inline";
	output=document.getElementById(id).value.toString().replace(/<(!?)\/([^>]+)>\n/g,"<$1/$2>").replace(/<!-/g,"<span class='comment'>&lt;!-").replace('-->','--></span>').replace(/<\/form>/gi,"<!/form>").replace(/([{};])\n/g,"$1");
    document.getElementById(id+':frame').style.width=tw+'px';
    document.getElementById(id+':frame').style.height=th+'px';
    output=output.replace(/(<table(.|[\r\n])+?\/tr>)[^<]*?(<!for[^>]+>)/im,"$3$1");
    output=output.replace(/(<!\/for[^>]+>)[^<]*?(<\/tbody>)/im,"$2$1");
    output=output.replace(/(<!\/for[^>]+>)[^<]*?(<\/table>)/im,"$2$1");
    output=output.replace(/(<[uo]l[^>]*?>)[^<]*?(<!for[^>]+>)/im,"$2$1");
    output=output.replace(/(<!\/for[^>]+>)[^<]*?(<\/[uo]l>)/im,"$2$1");
	tags=output.replace(/=['"][^<>'"]*<!/,"").replace(/=['"]<!/,"");
    tags=tags.match(/<![^>]+>/gm,"$1");
	if(tags!=null&&tags.length>0)for(i=0;i<tags.length;i++) {
	    var tmp=tags[i].substring(1,tags[i].length-1);
	    var t=tmp.split(' ');
	    var url=(t[1]==null?t[0]:t[0]+' '+(t[1].match(/^[a-z]+=['"]/)?t[1].substring(t[1].indexOf('=')+2,t[1].length-1):t[1]))+(t[2]!=null?' '+t[2]+(t[1].substr(0,1)=='@'&&t[3]!=''?' '+t[3]:''):'');
//+(t[0].substr(1,5)=='field'||t[0].substr(1,3)=='var'&&t[2]!=null&&t[2]?' '+t[2]:''));
	    output=output.replace(tags[i],"<img class='wysiwyg_icon' "+(url.substr(0,7)!="!widget"?"height='14' width='"+(url.length*8)+"'":"")+" src='js/wysiwyg.js/"+escape("<"+url.replace(/\//g,"!2F!").replace(/\+/g,"!2B!")+">")+"' alt=\"&lt;"+tmp.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;")+"&gt;\">");
	}
        document.getElementById(id+':frame').innerHTML=output;
        if(output.match(/class=['"]toc/))   //'
            document.getElementById(id+':toc').checked=true;
    }
}
function wysiwyg_exec(id,cmd,val)
{
    var rte=document.getElementById(id);
    if(rte)rte.focus();
    try { return document.execCommand(cmd,false,val); }
    catch(e) { alert(e); return null;}
}
function wysiwyg_insert(id,html)
{
    var fid=id.replace(/:[^:]+$/,'')+':frame';
    var rte=document.getElementById(fid);
    if(rte)rte.focus();
//FIXME: pasteHTML fails on newer Firefox
    if(document.all) {var rng=rte.document.selection.createRange();rng.pasteHTML(html);rng.collapse(false);rng.select();}
    else {
	if(wysiwyg_selected_sel &&
       wysiwyg_selected_sel.getRangeAt(wysiwyg_selected_sel.rangeCount-1)!=null &&
       typeof wysiwyg_selected_sel.getRangeAt(wysiwyg_selected_sel.rangeCount-1).pasteHTML == 'function'
    ) wysiwyg_selected_sel.getRangeAt(wysiwyg_selected_sel.rangeCount-1).pasteHTML(html);
	else wysiwyg_exec(fid,'insertHTML',html);
    }
}
function wysiwyg_toc(id,onoff)
{
    var rte=document.getElementById(id);
    var rtef=document.getElementById(id+':frame');
    if(rte==null||rtef==null) return;
    if(rtef.firstChild!=null&&rtef.firstChild.tagName=="DIV"&&rtef.firstChild.className=="toc") rtef.removeChild(rtef.firstChild);
    if(onoff) {
	var d=document.createElement("DIV");
	d.setAttribute("class","toc");
	rtef.insertBefore(d,rtef.firstChild);
    }
    wysiwyg_setvalue(id);
}
function wysiwyg_sfreent(obj,s,e) {

}
function wysiwyg_setfont(id,sel){if(sel==null||sel==''){
    wysiwyg_selected(id);
console.log('SELECTED ');
var html='';
if(document.selection!=null) {
        html=wysiwyg_selected_txt.htmlText;
} else if (wysiwyg_selected_sel.rangeCount) {
            var container = document.createElement("div");
            for (var i = 0, len = wysiwyg_selected_sel.rangeCount; i < len; ++i) {
                container.appendChild(wysiwyg_selected_sel.getRangeAt(i).cloneContents());
            }
            html = container.innerHTML;
}
html=html.replace(/\<[bB][rR][^\>]*\>/,"@BR@").replace(/\<[^\>]+\>/,"").replace("@BR@","<br/>");
console.log(html);
    var range = wysiwyg_selected_sel.getRangeAt(0);
    if(range.startOffset==0 && range.endOffset==0 || range.collapsed)
        return;
    var t=document.getElementById(id+':frame').innerHTML;
    var n,nodes=range.commonAncestorContainer.childNodes;
console.log("AAA "+nodes.length);
    if(nodes.length==0) {
        nodes=new Array();
        nodes[0]=(range.commonAncestorContainer.parentNode==null||range.commonAncestorContainer.parentNode.className=='wysiwyg_edit')?range.commonAncestorContainer:range.commonAncestorContainer.parentNode;
    }
console.log(nodes);
    for(n=0;n<nodes.length;n++){
//        if(nodes[n]==range.startContainer) alert('start '+n);
    }
    return;
    var p=t.substring(0,range.startOffset);
    var m=t.substring(range.startOffset,range.endOffset);
    var s=t.substr(range.endOffset);
    var par=p.match(/<([a-zA-Z0-9]+)[^>]*>(.*?)$/);
    if(par) {
        var par2=s.match(new RegExp("^(.*?)</"+par[1]+"[^>]*>"));
        p=p.substring(0,p.length-par[0].length)+par[2];

        console.log('par');
         console.log(par);
        console.log('par2');
         console.log(par2);
}
    var d=t.match(/[<]?[^>]?>?(.*?)@@@@@(.*?)#####(.*?)[<]?[^>]?>?/g);
console.log(d);
//    document.getElementById(id+':frame').innerHTML=t;
    wysiwyg_setvalue(id);
} else wysiwyg_exec(id+':frame',"formatblock",sel);}
function wysiwyg_bold(id) {wysiwyg_exec(id+':frame',"bold","");}
function wysiwyg_italic(id) {wysiwyg_exec(id+':frame',"italic","");}
function wysiwyg_underline(id) {wysiwyg_exec(id+':frame',"underline","");}
function wysiwyg_strike(id) {wysiwyg_exec(id+':frame',"strikethrough","");}
function wysiwyg_left(id) {wysiwyg_exec(id+':frame',"justifyleft","");}
function wysiwyg_center(id) {wysiwyg_exec(id+':frame',"justifycenter","");}
function wysiwyg_justify(id) {wysiwyg_exec(id+':frame',"justifyfull","");}
function wysiwyg_right(id) {wysiwyg_exec(id+':frame',"justifyright","");}
function wysiwyg_indent(id) {wysiwyg_exec(id+':frame',"indent","");}
function wysiwyg_outdent(id) {wysiwyg_exec(id+':frame',"outdent","");}
function wysiwyg_list(id) {wysiwyg_exec(id+':frame',"insertunorderedlist","");}
function wysiwyg_numbered(id) {wysiwyg_exec(id+':frame',"insertorderedlist","");}
function wysiwyg_undo(id) {wysiwyg_exec(id+':frame',"undo","");}
function wysiwyg_redo(id) {wysiwyg_exec(id+':frame',"redo","");}
function wysiwyg_unlink(id) {wysiwyg_exec(id+':frame',"unlink","");}
function wysiwyg_link(id,obj) {document.getElementById(id+':link_href').value=wysiwyg_selected_obj&&wysiwyg_selected_obj.tagName=='A'?wysiwyg_selected_obj.getAttribute('href'):(wysiwyg_selected_obj&&wysiwyg_selected_obj.parentNode&&wysiwyg_selected_obj.parentNode.tagName=='A'?wysiwyg_selected_obj.parentNode.getAttribute('href'):'https://');if(wysiwyg_selected_obj&&wysiwyg_selected_obj.tagName!='A'&&(wysiwyg_selected_obj.parentNode==null|wysiwyg_selected_obj.parentNode.tagName!='A')&&wysiwyg_selected_sel) {var a=document.createElement('a');a.setAttribute('href','https://');a.innerHTML=wysiwyg_selected_txt;wysiwyg_selected_sel.getRangeAt(wysiwyg_selected_sel.rangeCount-1).surroundContents(a);wysiwyg_selected_obj=a;}popup_open(obj,id+':link',-100,24);}
function wysiwyg_setlink(id,obj){if(!obj||!obj.value||obj.value=='')wysiwyg_exec(id+':frame',"unlink","");if(!wysiwyg_selected_obj)wysiwyg_selected(id);if(wysiwyg_selected_obj&&wysiwyg_selected_obj.tagName=='A')wysiwyg_selected_obj.href=obj.value;else if(wysiwyg_selected_obj&&wysiwyg_selected_obj.parentNode&&wysiwyg_selected_obj.parentNode.tagName=='A')wysiwyg_selected_obj.parentNode.href=obj.value;else alert(obj.value);}
//function wysiwyg_image(id,obj) {popup_open(obj,id+':image',0,24);}
function wysiwyg_table(id,obj) {popup_open(obj,id+':table',0,24);}
function wysiwyg_style(id,obj) {popup_open(obj,id+':style',0,24);}
function wysiwyg_import(id,obj) {document.getElementById(id+':impframe').contentWindow.document.getElementsByTagName('INPUT')[4].click();}
function wysiwyg_importstart(id){document.getElementById(id+':icon_import').style='background:url(images/loading.gif) no-repeat center;';}
function wysiwyg_importdone(id){document.getElementById(id+':icon_import').style='background:url(images/wysiwyg.png) no-repeat -96px 0px;';
var choose=document.getElementById(id+':impframe').contentWindow.document.getElementsByTagName('DIV')[0].innerHTML;
document.getElementById(id+':impframe').setAttribute('src','js/wysiwyg.js?impform='+id);
document.getElementById('choosediv').innerHTML="<div class='wysiwyg_choosediv' data-chooseid='0' onmousemove='wysiwyg_divchoosemove(event,\""+id+"\");' onclick='wysiwyg_divchooseselect(event,\""+id+"\");'>"+choose+"</div>";

if(<?=(empty(PHPPE::$core->noanim)?'true':'false')?> && typeof zoom_open=='function')
    zoom_open(document.getElementById(id+':icon_import'),'choosediv');
else
    document.getElementById('choosediv').style.display='block';

}
function wysiwyg_tableresize(event,obj){
var x=event!=null?event.clientX-parseInt(obj.style.left)+window.pageXOffset:0,y=event!=null?event.clientY-parseInt(obj.style.top)+window.pageYOffset:0;
if(!event)event=window.event;var cols=Math.floor((x+15)/16),rows=Math.floor((y+15)/16);
if(cols < 1) cols=1; if(rows < 1) rows=1;
obj.style.width=((cols+1)*16)+'px';obj.style.height=((rows+1)*16)+'px';obj.innerHTML='<div style="float:left;position:absolute;">'+cols+' x '+rows+'</div><div style="float:right;width:16px;height:'+((rows+1)*16)+'px;background:#ffffff url(images/wysiwygbg.gif);"></div><div style="margin-top:'+(rows*16)+'px;width:'+(cols*16)+'px;height:16px;background:#ffffff url(images/wysiwygbg.gif);"></div>';
}
function wysiwyg_tableinsert(event,id,obj){if(!event)event=window.event;var cols=Math.floor((event.clientX-parseInt(obj.style.left)+window.pageXOffset+15)/16),rows=Math.floor((event.clientY-parseInt(obj.style.top)+window.pageYOffset+15)/16);
var r,c,h='<table class="resptable">';for(r=0;r < rows;r++){h+='<tr>';for(c=0;c < cols;c++)h+='<t'+(rows > 1 && r==0?'h':'d')+'><br></t'+(rows > 1 && r==0?'h':'d')+'>';h+='</tr>';}h+='</table><br>';
//document.getElementById(id+':frame').focus();
wysiwyg_insert(id,h);
}
function wysiwyg_new(fn,fv,cfg)
{
    var mx=Math.floor(window.innerWidth?window.innerWidth:document.body.offsetWidth);
    var my=Math.floor(window.innerHeight?window.innerHeight:document.body.offsetHeight);
    var container=document.getElementById(fn+':container');
    var source=document.getElementById(fn);
    var conf=[];
    if(container==null||source==null) return;
    if(cfg!=null)
        try{
            conf=JSON.parse(cfg);
        }catch(e) {
            console.log(cfg);
            console.log(e.message);
        }
    if(document.designMode){
	var i,n=0,menu=["import","","style","bold","italic","underline","strike","sup","sub","","outdent","indent","left","center","justify","right","","list","numbered","link","unlink","image","video","attachment","table","","undo","redo",""];
	var icons=document.createElement('div'); icons.setAttribute('id',fn+':icons'); icons.setAttribute('class','wysiwyg_icons'); icons.setAttribute('style','width:'+source.offsetWidth+'px;height:36px;padding:0px;vertical-align:middle;display:none;');
	var imgicons=document.createElement('div'); imgicons.setAttribute('id',fn+':imgicons'); imgicons.setAttribute('style','display:none;');
	var tgsrc=document.createElement('img');tgsrc.setAttribute('id',fn+':source'); tgsrc.setAttribute('title','HTML'); tgsrc.setAttribute('onclick','wysiwyg_togglesrc(\"'+fn+'\");');
    tgsrc.setAttribute('class','wysiwyg_menuicon');
    tgsrc.setAttribute('src','images/empty.gif');
    var tgdiv=document.createElement('div'); tgdiv.setAttribute('class','wysiwyg_toolbar'); tgdiv.setAttribute("style","height:24px;"); tgdiv.appendChild(tgsrc);
	var imglink=document.createElement('img'); imglink.setAttribute('class','wysiwyg_menuicon'); imglink.setAttribute('title',L('wysiwyg_zoom')); imglink.setAttribute('onclick','wysiwyg_zoom(event,\"'+fn+'\");');imglink.setAttribute('style','background-position:-48px 0px;');
	var sep=document.createElement('I'); sep.setAttribute('class','wysiwyg_sep');
	var toc=document.createElement('input'); toc.setAttribute('id',fn+':toc'); toc.setAttribute('type','checkbox'); toc.setAttribute('value','1'); if(fv!=null&&fv.match(/^<div[\ \t\n\r]+class=[\'\"]toc/i))toc.setAttribute('checked',''); toc.setAttribute('onchange','wysiwyg_toc(\"'+fn+'\",this.checked);');
	var tocl=document.createElement('label'); tocl.setAttribute('for',fn+':toc'); tocl.innerHTML=L('TOC');
	var edit=document.createElement('div'); edit.setAttribute('id',fn+':frame');edit.setAttribute('class','wysiwyg_edit');edit.setAttribute('style','overflow:auto;width:'+(source.offsetWidth-0)+'px;height:'+(source.offsetHeight-4)+'px;padding:0px;');
	var link=document.createElement('div'); link.setAttribute('id',fn+':link');link.setAttribute('class','popup');link.setAttribute('style','position:absolute;top:0px;left:0px;display:none;');
	var image=document.createElement('div'); image.setAttribute('id',fn+':image');image.setAttribute('class','popup');image.setAttribute('style','position:absolute;top:0px;left:0px;width:300px;height:300px;background:#ffffff;display:none;');
    var style=document.createElement('div'); style.setAttribute('id',fn+':style');style.setAttribute('class','popup wysiwyg_style');style.setAttribute('style','position:absolute;top:0px;left:0px;display:none;');
    if(document.getElementById('choosediv')==null) {
        var choosediv=document.createElement('div');
        choosediv.setAttribute('id','choosediv');
        choosediv.setAttribute('class','modal');
        choosediv.setAttribute('style','position:fixed;top:0px;left:0px;width:100%;height:100%;padding:0px;opacity:0.9 !important;background:#fff !important;z-index:1004;display:none;box-shadow:2px 2px 3px #000;');
        choosediv.setAttribute('title',L('wysiwyg_chooseadiv'));
        choosediv.setAttribute('data-zoom-nodecor','1');
        choosediv.setAttribute('data-zoom-w','100%');
        choosediv.setAttribute('data-zoom-h','100%');
        container.appendChild(choosediv);
    }
    var impframe=document.createElement('iframe');
    impframe.setAttribute('id',fn+':impframe');
    impframe.setAttribute('src','js/wysiwyg.js?impform='+fn);
    impframe.setAttribute('style','display:none;');
//    var imp=document.createElement('input'); imp.setAttribute('id',fn+':import'); imp.setAttribute('type','file'); imp.setAttribute('style','width:300px;display:none;');imp.setAttribute('onchange','wysiwyg_doimport(\"'+fn+'\",this);');
	var table=document.createElement('div'); table.setAttribute('id',fn+':table');table.setAttribute('class','popup');table.setAttribute('style','position:absolute;top:0px;left:0px;width:32px;height:32px;background:#8080ff url(images/wysiwygbg.gif);display:none;text-align:right;');table.setAttribute('onmousemove','wysiwyg_tableresize(event,this);');table.setAttribute('onclick','wysiwyg_tableinsert(event,\"'+fn+'\",this);');table.innerHTML='1 x 1';
	var link_href=document.createElement('input'); link_href.setAttribute('id',fn+':link_href'); link_href.setAttribute('type','text'); link_href.setAttribute('style','width:300px;');link_href.setAttribute('value','');link_href.setAttribute('onkeyup','wysiwyg_setlink(\"'+fn+'\",this);');
    var hooks=wysiwyg_customhooks+(wysiwyg_customhooks!=''&&conf!=null&&conf[0]!=null?",":"")+(conf!=null&&conf[0]!=null?conf[0]:"");
    var custom=document.createElement('div'); custom.setAttribute('id',fn+':custom'); custom.setAttribute('style','display:'+(hooks!=''?'inline':'none')+';'); custom.setAttribute('draggable','false');
	edit.setAttribute("onmouseup",'wysiwyg_refreshtools(event,"'+fn+'");');edit.setAttribute('onkeyup','wysiwyg_setvalue("'+fn+'");');edit.setAttribute("contentEditable",true);edit.setAttribute("designMode","on");
    edit.setAttribute("ondrop",'wysiwyg_drop(event,"'+fn+'");');
	edit.setAttribute('onmouseout','wysiwyg_setvalue("'+fn+'");');
	link.appendChild(link_href);
//    container.appendChild(imp);
    container.appendChild(impframe);
	container.appendChild(link);
	container.appendChild(image);
    container.appendChild(style);
	container.appendChild(table);
    var txt="";
    for(i in LANG)
        if(i.substr(0,13)=='wysiwyg_style') {
            var tag=i.substr(13);
            if(tag=='') tag='<span>';
            txt+=tag.replace('>'," onclick='wysiwyg_setfont(\""+fn+"\",\""+i.substr(13)+"\");' onmouseover='wysiwyg_setfont(\""+fn+"\",\""+i.substr(13)+"\");'>")+LANG[i]+tag.replace('<','</');
        }
    style.innerHTML=txt;
    var x=96;
	for(i=0;i<menu.length;i++) {
        var menuimg=document.createElement('img');
        menuimg.setAttribute('id',fn+':icon_'+menu[i]);
        menuimg.setAttribute('class','wysiwyg_menuicon');
        menuimg.setAttribute('src','images/empty.gif');
        menuimg.setAttribute('style','background-position:-'+x+'px 0px;');
	    if(menu[i]){
		if(menu[i]==".") { icons.appendChild(sep.cloneNode()); n=1; continue; }
        x+=24;
		if(menu[i]=="image" && typeof wysiwyg_image!='function') continue;
        if(menu[i]=="video" && typeof wysiwyg_video!='function') continue;
        if(menu[i]=="attachment" && typeof wysiwyg_attachment!='function') continue;
		menuimg.setAttribute('title',L('wysiwyg_'+menu[i]));
		menuimg.setAttribute('onclick','wysiwyg_'+menu[i]+'(\"'+fn+'\",this);');
		if(menu[i]=="style")menuimg.setAttribute('onmouseover','wysiwyg_'+menu[i]+'(\"'+fn+'\",this);');
		icons.appendChild(menuimg);
	    } else { icons.appendChild(sep.cloneNode()); n=0; }
	}
	imgicons.appendChild(imglink);
    <?php if(PHPPE::isinst("Core")) {?>
	icons.appendChild(toc);
	icons.appendChild(tocl);
    icons.appendChild(sep.cloneNode());
    <?php } ?>
	icons.appendChild(imgicons);
    icons.appendChild(custom);
	tgdiv.appendChild(icons);
	container.appendChild(edit);
    container.insertBefore(tgdiv,container.childNodes[0]);
    if(hooks!='') {
            var t="",d=hooks.split(',');
            for(i in d)
                if(typeof window[d[i]]=='function')
                    t+=window[d[i]](fn,fv,conf,source.offsetWidth,source.offsetHeight);
            custom.innerHTML=t;
    }
    }
//    var tarea=document.createElement('textarea');tarea.setAttribute('id',fn);tarea.setAttribute('name',fn);tarea.setAttribute('class','wysiwyg wysiwyg_edit input');
    source.setAttribute('style','display:none;padding:0px;');
//    tarea.value=fv.replace(/&#39;/g,'\'').replace(/&#60;&#33;/g,'<!');
    wysiwyg_src[fn]=true;
//    container.appendChild(tarea);

    wysiwyg_tableresize(null,table);
    wysiwyg_togglesrc(fn);
}
function wysiwyg_doimport(id,obj)
{
    alert("import to "+id);
}
function wysiwyg_drop(evt,id)
{
    setTimeout(function(){
        var conf=document.getElementById(id+':conf');
        if(conf) {
            var h,hooks=conf.getAttribute('data-drophook');
            if(hooks) hooks=hooks.split(',');
            for(h in hooks) {
                if(typeof window[hooks[h]]=='function')
                    window[hooks[h]](evt,id);
            }
        }
    },50);
}
function wysiwyg_update()
{
    var i,alldiv=document.getElementsByTagName("DIV");
    for(i=0;i<alldiv.length;i++)
	if((' '+alldiv[i].className+' ').indexOf(' wysiwyg_edit ') > -1) wysiwyg_setvalue(alldiv[i].id.replace(':frame',''));
}

function wysiwyg_init()
{
    setTimeout('wysiwyg_initreal();',1);
}
function wysiwyg_initreal()
{
    var i,ua=navigator.userAgent.toLowerCase();
    var allinstance=document.getElementsByTagName("DIV");
    wysiwyg_gecko=(ua.indexOf("gecko")!=-1);
    wysiwyg_webkit=(ua.indexOf("webkit")!=-1);
    for(i=0;i<allinstance.length;i++)
        if(allinstance[i].className.indexOf("wysiwyg")>-1&&allinstance[i].id.indexOf(":container")>-1) {
            wysiwyg_new(allinstance[i].id.split(":")[0],allinstance[i].value,decodeURIComponent(allinstance[i].getAttribute('data-conf')));
        }
}
function wysiwyg_toolbarhooks(funclist)
{
    wysiwyg_customhooks=funclist;
}
var wysiwyg_divchooselast=null;
function wysiwyg_divchoosemove(evt)
{
    var el=document.elementFromPoint(evt.clientX,evt.clientY);
    if(wysiwyg_divchooselast!=el) {
        if(wysiwyg_divchooselast!=null)
            wysiwyg_divchooselast.setAttribute('style',wysiwyg_divchooselast.getAttribute('data-style')?wysiwyg_divchooselast.getAttribute('data-style'):'');
        if(el!=null) {
            el.setAttribute('data-style',el.getAttribute('style')?el.getAttribute('style'):'');
            el.setAttribute('style','background:#801010;color:#FF0000;');
        }
        document.getElementById('choosediv').style.display='block';
        wysiwyg_divchooselast=el;
    }
}
function wysiwyg_divchooseselect(evt,id)
{
    var el=document.elementFromPoint(evt.clientX,evt.clientY);
    if(el==null) return;
    var t=el.innerHTML.replace(/^<br>/,'').replace(/data\-style=["'][^"']*["']/,'');
    if(<?=(empty(PHPPE::$core->noanim)?'true':'false')?> && typeof zoom_hide=='function')
        zoom_hide();
    else
        document.getElementById('choosediv').style.display='none';
    var h=document.getElementById(id+':frame').offsetHeight;
    document.getElementById(id).value=t;
    document.getElementById(id+':frame').innerHTML=t;
    document.getElementById(id).style.height=h+'px';
    document.getElementById(id+':frame').style.height=h+'px';
}
