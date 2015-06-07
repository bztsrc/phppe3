<?php
use PHPPE\Core as PHPPE;

if(isset($_REQUEST['txt2img'])){
    header( "Content-Type: image/gif" );
    header( "Pragma:cache" );
    header( "Cache-Control:cache,public,max-age=86400" );
    header( "Connection:close");
    $str=str_replace(array("!2F!","!2B!"),array("/","+"),urldecode(stripslashes($_REQUEST['txt2img'])));
    if(strtolower(substr($str,0,8))=="<!widget") {
        list($d)=explode(" ",trim(substr($str,9,strlen($str)-10)));
        list($d)=explode("(",$d);
        $f=glob("vendor/phppe/*/addons/".str_replace("..","",$d).".icon");
        if(!empty($f[0]) && file_exists($f[0]))
            die(file_get_contents($f[0]));
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
header("Content-type:text/javascript;charset=utf-8");
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
       if(tools) tools.style.display='inline';
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
    rte.value=rte.value.replace(/<br[\/]?>([^\n])/gi,"<br>\n$1").replace(/<\/([a-z]+)>([^\n])/gi,"</$1>\n$2").replace(/<!\/([^>]+)>([^\n])/g,"<!/$1>\n$2").replace(/([{};])([^\n])/g,"$1\n$2").replace(/(&[a-z0-9#]+;)\n/gi,"$1");
}

function wysiwyg_zoom(evt,id)
{
    if(!evt)evt=window.event;
    if(wysiwyg_selected_obj.tagName!="IMG")return;
    if(!wysiwyg_selected_obj.className || !wysiwyg_selected_obj.className.match(/zoom/))
        wysiwyg_selected_obj.className=(wysiwyg_selected_obj.className?wysiwyg_selected_obj.className+' ':'')+'zoom';
    else
        wysiwyg_selected_obj.className=wysiwyg_selected_obj.className.replace(/zoom/g,'').replace(/^[\ ]+/,'').replace(/[\ ]+$/,'').replace(/[\ ]+/g,' ');
}
function wysiwyg_togglesrc(id)
{
    var tools=document.getElementById(id+':tools');
    var conf=document.getElementById(id+':conf');
    var icons=document.getElementById(id+':icons');
    var imgicons=document.getElementById(id+':imgicons');
    var tblicons=document.getElementById(id+':tblicons');
    var tw=document.getElementById(id).offsetWidth-1;
    var th=document.getElementById(id).offsetHeight-2;
    document.getElementById(id+':frame').style.display=(wysiwyg_src[id]?"block":"none");
    document.getElementById(id).style.display=(wysiwyg_src[id]?"none":"block");
    document.getElementById(id+':source').src='images/wysiwyg/'+(wysiwyg_src[id]?'design':'source')+'.gif';
    wysiwyg_src[id]=1-wysiwyg_src[id];
    //if(imgicons) imgicons.style.display="none";
    if(tblicons) tblicons.style.display="none";
    if(wysiwyg_src[id]){
	if(tools) tools.style.display="none";
	if(conf) conf.style.display="none";
	icons.style.display="none";
	wysiwyg_setvalue(id);
    } else {
	var output,tags,i;
	if(tools) { tools.style.display="inline"; }
	if(conf) conf.style.display="none";
	if(icons) icons.style.display="inline";
	output=document.getElementById(id).value.toString().replace(/<(!?)\/([^>]+)>\n/g,"<$1/$2>").replace(/<!-/g,"<span class='comment'>&lt;!-").replace('-->','--></span>').replace(/<\/form>/gi,"<!/form>").replace(/([{};])\n/g,"$1");
    document.getElementById(id+':frame').style.width=tw+'px';
    document.getElementById(id+':frame').style.height=th+'px';
	tags=output.match(/<!.+?>/gm,"$1");
	if(tags!=null&&tags.length>0)for(i=0;i<tags.length;i++) {
	    var tmp=tags[i].substring(1,tags[i].length-1);
	    var t=tmp.split(' ');
	    var url=(t[1]==null?t[0]:t[0]+' '+(t[1].match(/^[a-z]+=['"]/)?t[1].substring(t[1].indexOf('=')+2,t[1].length-1):t[1]))+(t[2]!=null?' '+t[2]:'');
//+(t[0].substr(1,5)=='field'||t[0].substr(1,3)=='var'&&t[2]!=null&&t[2]?' '+t[2]:''));
	    output=output.replace(tags[i],"<img class='wysiwyg_icon' "+(url.substr(0,7)!="!widget"?"height='14' width='"+(url.length*8)+"'":"")+" src='js/wysiwyg.js?txt2img="+escape("<"+url.replace(/\//g,"!2F!").replace(/\+/g,"!2B!")+">")+"' alt=\"&lt;"+tmp.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;")+"&gt;\">");
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
function wysiwyg_setfont(id,sel){wysiwyg_exec(id+':frame',"formatblock",sel.value);sel.selectedIndex=0;}
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
function wysiwyg_link(id,obj) {document.getElementById(id+':link_href').value=wysiwyg_selected_obj&&wysiwyg_selected_obj.tagName=='A'?wysiwyg_selected_obj.getAttribute('href'):'https://';if(wysiwyg_selected_obj&&wysiwyg_selected_obj.tagName!='A'&&wysiwyg_selected_sel) {var a=document.createElement('a');a.setAttribute('href','https://');a.innerHTML=wysiwyg_selected_txt;wysiwyg_selected_sel.getRangeAt(wysiwyg_selected_sel.rangeCount-1).surroundContents(a);wysiwyg_selected_obj=a;}popup_open(obj,id+':link',-100,24);}
function wysiwyg_setlink(id,obj){if(!obj||!obj.value||obj.value=='')wysiwyg_exec(id+':frame',"unlink","");if(!wysiwyg_selected_obj)wysiwyg_selected(id);if(wysiwyg_selected_obj&&wysiwyg_selected_obj.tagName=='A')wysiwyg_selected_obj.href=obj.value;else alert(obj.value);}
function wysiwyg_image(id,obj) {popup_open(obj,id+':image',0,24);}
function wysiwyg_table(id,obj) {popup_open(obj,id+':table',0,24);}
function wysiwyg_tableresize(event,obj){if(!event)event=window.event;var cols=Math.floor((event.clientX-parseInt(obj.style.left)+window.pageXOffset+15)/16),rows=Math.floor((event.clientY-parseInt(obj.style.top)+window.pageYOffset+15)/16);
obj.style.width=((cols+1)*16)+'px';obj.style.height=((rows+1)*16)+'px';obj.innerHTML=cols+' x '+rows;}
function wysiwyg_tableinsert(event,id,obj){if(!event)event=window.event;var cols=Math.floor((event.clientX-parseInt(obj.style.left)+window.pageXOffset+15)/16),rows=Math.floor((event.clientY-parseInt(obj.style.top)+window.pageYOffset+15)/16);
var r,c,h='<table>';for(r=0;r<rows;r++){h+='<tr>';for(c=0;c<cols;c++)h+='<td>&nbsp;</td>';h+='</tr>';}h+='</table>';wysiwyg_insert(id,h);
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
	var i,n=0,menu=["bold","italic","underline","strike","","outdent","indent","left","center","justify","right","","list","numbered","link","unlink","image","table","","undo","redo","history",""];
	var icons=document.createElement('div'); icons.setAttribute('id',fn+':icons'); icons.setAttribute('class','wysiwyg_icons'); icons.setAttribute('style','width:'+source.offsetWidth+'px;height:36px;padding:0px;vertical-align:middle;display:none;');
	var imgicons=document.createElement('span'); imgicons.setAttribute('id',fn+':imgicons'); imgicons.setAttribute('style','display:none;');
	var tgsrc=document.createElement('img'); tgsrc.setAttribute('id',fn+':source'); tgsrc.setAttribute('src','images/wysiwyg/source.gif'); tgsrc.setAttribute('alt','HTML'); tgsrc.setAttribute('title','HTML'); tgsrc.setAttribute('onclick','wysiwyg_togglesrc(\"'+fn+'\");'); tgsrc.setAttribute('style','padding:2px 0px 2px 2px;vertical-align:middle;cursor:pointer;');
    var tgdiv=document.createElement('div'); tgdiv.setAttribute('style','display:inline;'); tgdiv.appendChild(tgsrc);
	var imglink=document.createElement('img'); imglink.setAttribute('src','images/wysiwyg/imagelink.gif'); imglink.setAttribute('alt',L('wysiwyg_zoom')); imglink.setAttribute('title',L('wysiwyg_zoom')); imglink.setAttribute('onclick','wysiwyg_zoom(event,\"'+fn+'\");');imglink.setAttribute('style','vertical-align:middle;');
	var sep=document.createElement('img'); sep.setAttribute('src','images/wysiwyg/sep.gif'); sep.setAttribute('alt','|');sep.setAttribute('width','1');sep.setAttribute('height','24');sep.setAttribute('style','padding:0px 2px 0px 2px;vertical-align:middle;');
	var opt,st=document.createElement('select'); st.setAttribute('class','wysiwyg_select'); st.setAttribute('onchange','wysiwyg_setfont(\"'+fn+'\",this);'); st.setAttribute('style','margin-left:6px;height:24px;');
	var toc=document.createElement('input'); toc.setAttribute('id',fn+':toc'); toc.setAttribute('type','checkbox'); toc.setAttribute('value','1'); if(fv!=null&&fv.match(/^<div[\ \t\n\r]+class=[\'\"]toc/i))toc.setAttribute('checked',''); toc.setAttribute('onchange','wysiwyg_toc(\"'+fn+'\",this.checked);');
	var tocl=document.createElement('label'); tocl.setAttribute('for',fn+':toc'); tocl.innerHTML=L('TOC');
	var edit=document.createElement('div'); edit.setAttribute('id',fn+':frame');edit.setAttribute('class','wysiwyg_edit');edit.setAttribute('style','overflow:auto;width:'+(source.offsetWidth-0)+'px !important;height:'+(source.offsetHeight-4)+'px;padding:0px;');
	var link=document.createElement('div'); link.setAttribute('id',fn+':link');link.setAttribute('class','popup');link.setAttribute('style','position:absolute;top:0px;left:0px;display:none;');
	var image=document.createElement('div'); image.setAttribute('id',fn+':image');image.setAttribute('class','popup');image.setAttribute('style','position:absolute;top:0px;left:0px;width:300px;height:300px;background:#ffffff;display:none;');
	var table=document.createElement('div'); table.setAttribute('id',fn+':table');table.setAttribute('class','popup');table.setAttribute('style','position:absolute;top:0px;left:0px;width:32px;height:32px;background:#ffffff url(images/wysiwyg/tablebg.gif);display:none;');table.setAttribute('onmousemove','wysiwyg_tableresize(event,this);');table.setAttribute('onclick','wysiwyg_tableinsert(event,\"'+fn+'\",this);');table.innerHTML='1 x 1';
	var link_href=document.createElement('input'); link_href.setAttribute('id',fn+':link_href'); link_href.setAttribute('type','text'); link_href.setAttribute('style','width:300px;');link_href.setAttribute('value','');link_href.setAttribute('onkeyup','wysiwyg_setlink(\"'+fn+'\",this);');
    var hooks=wysiwyg_customhooks+(wysiwyg_customhooks!=''&&conf!=null&&conf[0]!=null?",":"")+(conf!=null&&conf[0]!=null?conf[0]:"");
    var custom=document.createElement('div'); custom.setAttribute('id',fn+':custom'); custom.setAttribute('style','display:'+(hooks!=''?'inline':'none')+';'); custom.setAttribute('draggable','false');
	edit.setAttribute("onmouseup",'wysiwyg_refreshtools(event,"'+fn+'");');edit.setAttribute('onkeyup','wysiwyg_setvalue("'+fn+'");');edit.setAttribute("contentEditable",true);edit.setAttribute("designMode","on");
	//container.setAttribute('style','width:'+source.offsetWidth+'px;height:'+(source.offsetHeight+36)+'px;');
	edit.setAttribute('onmouseout','wysiwyg_setvalue("'+fn+'");');
	link.appendChild(link_href);
	container.appendChild(link);
	container.appendChild(image);
	container.appendChild(table);
    for(i in LANG)
        if(i.substr(0,13)=='wysiwyg_style') {
            opt=document.createElement('option');
            opt.value=i.substr(13);
            opt.innerHTML=LANG[i];
            st.appendChild(opt);
        }
	icons.appendChild(sep.cloneNode());
	icons.appendChild(st);
	for(i=0;i<menu.length;i++) {
	    if(menu[i]){
		if(menu[i]==".") { icons.appendChild(sep.cloneNode()); n=1; continue; }
		if(menu[i]=="image" && typeof wysiwyg_image_list!='function') continue;
		if(menu[i]=="history" && typeof wysiwyg_history!='function') continue;
		var menuimg=document.createElement('img'); menuimg.setAttribute('src','images/wysiwyg/'+menu[i]+'.gif');
		menuimg.setAttribute('alt','wysiwyg_'+menu[i]);
		menuimg.setAttribute('title',L('wysiwyg_'+menu[i]));
		menuimg.setAttribute('onclick','wysiwyg_'+menu[i]+'(\"'+fn+'\",this);');
        menuimg.setAttribute('style','vertical-align:middle;');
		if(n)menuimg.setAttribute('onmouseover','wysiwyg_'+menu[i]+'(\"'+fn+'\",this);');
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

    wysiwyg_togglesrc(fn);
}
function wysiwyg_update()
{
    var i,alldiv=document.getElementsByTagName("DIV");
    for(i=0;i<alldiv.length;i++)
	if((' '+alldiv[i].className+' ').indexOf(' wysiwyg_edit ') > -1) wysiwyg_setvalue(alldiv[i].id.replace(':frame',''));
}

function wysiwyg_init()
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