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
 * @file vendor/phppe/wyswyg/js/wyswyg.js.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief HTML5 compatible wyswyg Editor
 */
use PHPPE\Core as Core;

//! convert text to image (neat trick that makes the whole tag draggable in edit mode)
if(!empty(Core::$core->item)){
    header( "Content-Type: image/gif" );
    header( "Pragma:cache" );
    header( "Cache-Control:cache,public,max-age=86400" );
    header( "Connection:close");
    $str=str_replace(array("!2F!","!2B!","!2f!","!2b!"),array("/","+","/","+"),urldecode(stripslashes(Core::$core->item)));
    if(strtolower(substr($str,0,8))=="<!widget") {
        $d=explode(" ",trim(substr($str,9,strlen($str)-10)));
		if(!empty($d[0])) {
        	if($d[0][0]=="@") array_shift($d);
        	list($d)=explode("(",$d[0]);
			if(!empty($d)){
        		$f=glob("vendor/phppe/*/addons/".str_replace("..","",$d).".png");
        		if(!empty($d) && !empty($f[0]) && file_exists($f[0]) && $data=@file_get_contents($f[0])) die($data);
			}
		}
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
    if(!empty($_FILES['upload'])) {
        if($_FILES['upload']['type']=="text/plain"){
            $choose=@file_get_contents($_FILES['upload']['tmp_name']);
        }elseif($_FILES['upload']['type']=="text/html") {
            $d=@file_get_contents($_FILES['upload']['tmp_name']);
            preg_match_all("|<body[^>]*>(.*?)<\/body|ims",$d,$b,PREG_SET_ORDER);
            $choose=!empty($b[0][1])?$b[0][1]:$d;
        } else {
            $err=L("wyswyg_htmlonly");
        }
    }
    die("<html><head><meta charset='utf-8'/></head><body>".($choose?"<div>".$choose."</div><script type='text/javascript'>parent.pe.wyswyg.importdone(\"".$_REQUEST['impform']."\");</script>":
        \PHPPE\View::_t("<!form>").
        "<input type='hidden' name='impform' value='".$_REQUEST['impform']."'>".
        "<input type='file' name='upload' onchange='this.form.submit();' id='".$_REQUEST['impform'].":import'>".
        ($err?"alert('".$err."');":"").
        "</form>")."</body></html>");
}

$bs = Core::isInst("bootstrap");

//! get toolbars
$toolbar = [];
$libs = \PHPPE\Core::lib();
foreach($libs as $l)
    if(!empty($l->wyswyg_toolbar))
        $toolbar = array_merge($toolbar, $l->wyswyg_toolbar);
header("Pragma:no-cache");
?>
pe.wyswyg = {
<?php if($bs) {?>
    classes: {
    "toggle": "glyphicon glyphicon-eye",
    "import": "glyphicon glyphicon-open",
    "font": "glyphicon glyphicon-font",             //L("wyswyg_font") L("wyswyg_style") L("wyswyg_style<h2>") L("wyswyg_style<h3>") L("wyswyg_style<h4>") L("wyswyg_style<h5>") L("wyswyg_style<pre>")
    "bold": "glyphicon glyphicon-bold",             //L("wyswyg_bold")
    "italic": "glyphicon glyphicon-italic",         //L("wyswyg_italic")
    "underline": "glyphicon glyphicon-text-color",  //L("wyswyg_underline")
    "strikethrough": "glyphicon glyphicon-gbp",     //L("wyswyg_strikethrough")
    "superscript": "glyphicon glyphicon-superscript",//L("wyswyg_superscript")
    "subscript": "glyphicon glyphicon-subscript",   //L("wyswyg_subscript")
    "outdent": "glyphicon glyphicon-indent-right",  //L("wyswyg_outdent")
    "indent": "glyphicon glyphicon-indent-left",    //L("wyswyg_indent")
    "left": "glyphicon glyphicon-align-left",       //L("wyswyg_left")
    "center": "glyphicon glyphicon-align-center",   //L("wyswyg_center")
    "right": "glyphicon glyphicon-align-right",     //L("wyswyg_right")
    "justify": "glyphicon glyphicon-align-justify", //L("wyswyg_justify")
    "unordered": "glyphicon glyphicon-list",        //L("wyswyg_unordered")
    "ordered": "glyphicon glyphicon-list-alt",      //L("wyswyg_ordered")
    "link": "glyphicon glyphicon-link",             //L("wyswyg_link")
    "unlink": "glyphicon glyphicon-erase",          //L("wyswyg_unlink")
    "table": "glyphicon glyphicon-th",              //L("wyswyg_table")
    "content": "glyphicon glyphicon-file",          //L("wyswyg_content")
    "zoom": "glyphicon glyphicon-fullscreen",       //L("wyswyg_zoom")
    "image": "glyphicon glyphicon-picture",         //L("wyswyg_image")
    "video": "glyphicon glyphicon-film",            //L("wyswyg_video")
    "attachment": "glyphicon glyphicon-paperclip",  //L("wyswyg_attachment")
    "tag": "glyphicon glyphicon-cog",               //L("wyswyg_tag")
    "undo": "glyphicon glyphicon-step-backward",    //L("wyswyg_undo")
    "redo": "glyphicon glyphicon-step-forward",     //L("wyswyg_redo")
    },
<?php } ?>
    sel:null,
    selImg:null,

init: function()
{
    var i, allinstance=document.querySelectorAll("TEXTAREA.wyswyg");
    var icons = {
        "style": [ "font", "bold", "italic", "underline", "strikethrough", "superscript", "subscript" ],
        "align": [ "outdent", "indent", "left", "center", "justify", "right" ],
        "insert": [ "unordered", "ordered", "link", "unlink",/* "table",*/ "zoom" ],
        "hooks": <?=json_encode($toolbar)?>,
        "undo": [ "undo", "redo" ]
    };
    //! load toolbar hooks
    var plugins=document.querySelectorAll('[data-wyswyg-toolbar]');
    for(var i=0;i<plugins.length;i++) {
        icons['hooks'].concat(JSON.parse(plugins[i].getAttribute('data-wyswyg-toolbar')));
    }
    for(i=0;i<allinstance.length;i++) {
        pe.wyswyg.open(allinstance[i], icons);
    }
},

open: function(source, icons)
{
    var id = source.id;
    //! get configuration
    var cfg = decodeURIComponent(source.getAttribute('data-conf'));
    var conf=[];
    if(cfg!=null)
        try{
            conf=JSON.parse(cfg);
        }catch(e) {
            console.log(cfg);
            console.log(e.message);
        }
    //! add toolbar above textarea
    var tb=document.createElement('div');
    tb.setAttribute('id', id+':toolbar');
    tb.setAttribute('class', 'wyswyg_toolbar');
    source.parentNode.insertBefore(tb,source);
    //! populate toolbar in designmode
    if(document.designMode){
        //! html edit area
        var edit=document.createElement('div');
        edit.setAttribute('id', id+':edit');
        edit.setAttribute('class', 'input wyswyg');
        edit.setAttribute('style', 'height:'+(source.offsetHeight-0)+'px;padding:0px;background:#fff;color:#000;display:none;overflow:auto;');
        if(function_exists('pe.cms.getitem')) {
            var style=null, item=eval('pe.cms.getitem()');
            if(item!=null) {
                style=window.getComputedStyle(item, null);
                edit.setAttribute('class', 'input wyswyg '+item.className);
            }
            if(style!=null) {
                //! copy only a few attributes, because designmode would fail otherwise...
                //edit.style.cssText = style.cssText;
                var attr=[ "background-color", "background-image", "background-repeat", "background-position", "background-clip",
                "font-family", "font-size", "font-kerning", "font-style", "font-weight", "font-scretch", "line-height", "color" ];
                for (var a in attr) {
                    edit.style.setProperty(attr[a], style.getPropertyValue(attr[a]));
                }
                edit.style.height=(source.offsetHeight-0)+'px';
                edit.style.padding='0px';
                edit.style.display='none';
            }
        }
        //! selection hooks
        edit.setAttribute('data-wyswyg-select-a', "pe.wyswyg.link");
        edit.setAttribute('data-wyswyg-select-img', "pe.wyswyg.image"+(conf[1]!=null?","+conf[1]:""));
        if(conf[2]!=null) {
        	var i,h=JSON.parse(conf[2]);
        	for(i in h) if(h.hasOwnProperty(i)) icons['hooks'][i]=h[i];
		}
        //! set up event handlers and design mode
        edit.setAttribute('onmouseup','pe.wyswyg.event(event,"'+id+'","select-@TAG");');
        edit.setAttribute('ontouchend','pe.wyswyg.event(event,"'+id+'","select-@TAG");');
        edit.setAttribute('onkeyup','pe.wyswyg.setvalue("'+id+'");');
        edit.setAttribute('onmouseout','pe.wyswyg.setvalue("'+id+'");');
        edit.setAttribute("ondrop",'pe.wyswyg.drop(event,"'+id+'");');
        edit.setAttribute("contentEditable",true);
        edit.setAttribute("designMode","on");
        source.parentNode.insertBefore(edit,source);

        //! html toggle button
        var toggle = document.createElement('BUTTON');
        toggle.setAttribute('title',L('wyswyg_source'));
        toggle.setAttribute('onclick','event.preventDefault();pe.wyswyg.togglesrc(this, true, event);');
        tb.appendChild(toggle);

        //! import button
        var imp = document.createElement('BUTTON');
        imp.setAttribute('title',L('wyswyg_import'));
        imp.setAttribute('class', <?=($bs?"pe.wyswyg.classes['import']":"'wyswyg_icon wyswyg_icon-import'")?>);
        imp.setAttribute('onclick','event.preventDefault();pe.wyswyg.import(event, "'+id+'");');
        tb.appendChild(imp);

        //! add iconbar
        var ib = document.createElement('SPAN');
        ib.setAttribute('id', id+':icons');
        tb.appendChild(ib);

        //! font style popup
        var txt="", style=document.createElement('div');
        style.setAttribute('id',id+'_style');
        style.setAttribute('class','wyswyg_style');
        style.setAttribute('onmousemove','pe_w();');
        if(typeof LANG!='undefined'){
            if(LANG['rtl']!=null&&LANG['rtl']!=''&&LANG['rtl']!=false) style.setAttribute('dir','rtl');
            for(i in LANG)
                if(i.substr(0,12)=='wyswyg_style') {
                    var tag=i.substr(12);
                    if(tag=='') tag='<span>';
                    txt+=tag.replace('>'," onclick='event.preventDefault();pe_p();' onmouseover='event.preventDefault();pe.wyswyg.setfont(event,\""+i.substr(12)+"\",\""+id+"\");'>")+LANG[i]+tag.replace('<','</');
                }
        }

        style.innerHTML=txt;
        ib.appendChild(style);

        //! add icons
        for (var menu in icons)
            if(icons.hasOwnProperty(menu)) {
                var ms = document.createElement('SPAN');
                ms.setAttribute('id', id+':'+menu);
                ms.setAttribute('class', 'wyswyg_menu');
                for(var i in icons[menu]) {
                    var func=icons[menu][i],name=icons[menu][i],ext="";
                    if(menu=="hooks") {
                        func="popup";
                        name=i;
                        ext=",'"+icons[menu][i]+"'";
                    }
                    if(function_exists('pe.wyswyg.'+func)) {
                        var mi = document.createElement('BUTTON');
                        mi.setAttribute('id',id+':'+menu+'_'+func);
                        mi.setAttribute('class',
                        <?=$bs?"pe.wyswyg.classes[name]!=null?pe.wyswyg.classes[name]:":""?>'wyswyg_icon wyswyg_icon-'+name);
                        mi.setAttribute('title',L('wyswyg_'+name));
                        mi.setAttribute('onclick','event.preventDefault();pe.wyswyg.'+func+'(event,\"'+id+'\"'+ext+');pe.wyswyg.setvalue(\"'+id+'\");');
                        ms.appendChild(mi);
                    }
                }
                ib.appendChild(ms);
            }

        //! add url input for links
        var link=document.createElement('input');
        link.setAttribute('id',id+':link');
        link.setAttribute('dir','ltr');
        link.setAttribute('type','text');
        link.setAttribute('class','sub');
        link.setAttribute('style','width:300px;position:fixed;z-index:2000;display:none;');
        link.setAttribute('value','');
        link.setAttribute('onkeyup','pe.wyswyg.setlink(event,this,\"'+id+'\");');
        link.setAttribute('onblur',"this.style.display='none';");
        tb.appendChild(link);

        //! add import form
        var impframe=document.createElement('iframe');
        impframe.setAttribute('id',id+':impframe');
        impframe.setAttribute('src','js/wyswyg.js?impform='+id);
        impframe.setAttribute('style','display:none;');
        tb.appendChild(impframe);

        //! popup for plugins
        var popup=document.createElement('div');
        popup.setAttribute('id',id+'_popup');
        popup.setAttribute('class','wyswyg_popup');
        popup.setAttribute('onmousemove','pe_w();');
        popup.setAttribute('ondragleave','pe_p();');
        popup.setAttribute('style','position:fixed;display:none;visibility:visible;');
        tb.appendChild(popup);

        pe.wyswyg.event(null,id,'init');

        //! switch to html mode
        pe.wyswyg.togglesrc(toggle);
    }
},

drop: function(evt,id)
{
    setTimeout(function(){
        var source=document.getElementById(id);
        var h,hooks=source.getAttribute('data-drophook');
        if(hooks!=null) hooks=hooks.split(',');
        for(h in hooks) {
            if(function_exists(hooks[h]))
                eval(hooks[h]+"(evt,id)");
        }
    },50);
},

togglesrc: function(toggle,focus,evt)
{
    var edit = toggle.parentNode.nextSibling;
    var source = edit.nextSibling;
    //! check if it's in html mode
    if(edit.style.display=='block') {
        //! it is, switch to source mode
        toggle.setAttribute('class', '<?=$bs?"glyphicon glyphicon-eye":"wyswyg_icon wyswyg_toggle"?>-open');
        toggle.nextSibling.nextSibling.style.visibility='hidden';
        edit.style.display='none';
        source.style.width='100%';
        source.style.display='block';
        source.focus();
    } else {
        //! no, switch to html mode
        toggle.setAttribute('class', '<?=$bs?"glyphicon glyphicon-eye":"wyswyg_icon wyswyg_toggle"?>-close');
        toggle.nextSibling.nextSibling.style.visibility='visible';
        edit.style.display='block';
        edit.style.height=(source.offsetHeight)+'px';
        source.style.display='none';
        //! copy textarea value to edit div
        var output=source.value.toString();//.replace(/<(!?)\/([^>]+)>\n/g,"<$1/$2>").replace(/<\/form>/gi,"<!/form>").replace(/([{};])\n/g,"$1");
//    output=output.replace(/<(!?)\/([^>]+)>\n/g,"<$1/$2>").replace(/<!-/g,"<span class='comment'>&lt;!-").replace('-->','--></span>').replace(/<\/form>/gi,"<!/form>").replace(/([{};])\n/g,"$1");
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
        output=output.replace(tags[i],"<img class='wyswyg_icon' "+(url.substr(0,7)!="!widget"?"height='14' width='"+(url.length*8)+"'":"")+" src='js/wyswyg.js?item="+urlencode("<"+url+">")+"' alt=\"&lt;"+tmp.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;")+"&gt;\">");
    }

        edit.innerHTML=output;
        if(focus!=null)
            edit.focus();
     }
},

setvalue:function(id)
{
    var source = document.getElementById(id);
    var edit = source.previousSibling;
    //! copy edit div to textarea
    var output=edit.innerHTML;
    //rte.value=rte.value.replace(">&quot;\"",">\"").replace(/&lt;!([^-])/gi,"<!$1").replace(/\=\"\"/g,"").replace(/\"\=\"\"/g,"\"").replace(/\=\"\"/g,"").replace(/alt=\"[\ ]+/gmi,"alt=\"").replace(/&lt;img/g,"<img");
    var i,txt=output.match(/<img class=[\"\'][\ ]?wyswyg_icon[^>]+>([\'\"][^>]*?>)?/gmi,"$1");
//"
    if(txt!=null&&txt.length>0) for(i=0;i<txt.length;i++) {
        var tmp=txt[i].match(/alt=\"[^\"]*[\">]?/gmi,"$1");
//"
        if(tmp&&tmp[0]) {var t=tmp[0].substring(5,tmp[0].length-1).trim();t=t.replace("<!/form>","</form>");
        if(t.charAt(t.length-1)!='>') t=t+'>';
        output=output.replace(txt[i],t).replace("&lt;"+txt[i].substring(1,txt[i].length),t);}
    }
    source.value=output;
},

selected:function(evt, type)
{
        var sel=null, obj=null, txt=null, html=null;
        //function to get current selection
        if (document.selection) {
                sel=document.selection;
                if (sel!=null) {
                        txt=selection.createRange();
                        if (txt.item) {
                                obj=txt.item(0);
                        }
                        html=txt.htmlText;
                }
        } else {
                sel=window.getSelection();
                if (sel && sel.anchorNode && sel.anchorNode.childNodes)
                    obj=sel.anchorNode.childNodes[ sel.anchorOffset ];
                if (sel && sel.rangeCount) {
                    txt=sel.getRangeAt(sel.rangeCount - 1).cloneRange();
                    var container = document.createElement("div");
                    for (var i = 0, l = sel.rangeCount; i < l; ++i) {
                        container.appendChild(sel.getRangeAt(i).cloneContents());
                    }
                    html = container.innerHTML;
                }
        }
        if(obj==null&&evt!=null) obj=evt.target; 
        if(type=="sel") return sel; else
        if(type=="txt") return txt; else
        if(type=="html") return html; else
        return obj;
},

exec:function(id,cmd,val)
{
//    var div=document.getElementById(id+':edit');
//    if(div)div.focus();
    try { return document.execCommand(cmd,false,val); }
    catch(e) { alert(e); return null; }
},

import:function(evt,id) {document.getElementById(id+':impframe').contentWindow.document.getElementsByTagName('INPUT')[4].click();},

importdone:function(id){
var choose=choose=document.getElementById(id+':impframe').contentWindow.document.getElementsByTagName('DIV')[0].innerHTML;
document.getElementById(id+':impframe').src='js/wyswyg.js?impform='+id;
document.getElementById(id+':edit').innerHTML=choose;
pe.wyswyg.setvalue(id);
},

bold:function(evt,id) {pe.wyswyg.exec(id,"bold","");},
italic:function(evt,id) {pe.wyswyg.exec(id,"italic","");},
underline:function(evt,id) {pe.wyswyg.exec(id,"underline","");},
strikethrough:function(evt,id) {pe.wyswyg.exec(id,"strikethrough","");},
superscript:function(evt,id) {pe.wyswyg.exec(id,"superscript","");},
subscript:function(evt,id) {pe.wyswyg.exec(id,"subscript","");},
left:function(evt,id) {pe.wyswyg.exec(id,"justifyleft","");},
center:function(evt,id) {pe.wyswyg.exec(id,"justifycenter","");},
justify:function(evt,id) {pe.wyswyg.exec(id,"justifyfull","");},
right:function(evt,id) {pe.wyswyg.exec(id,"justifyright","");},
indent:function(evt,id) {pe.wyswyg.exec(id,"indent","");},
outdent:function(evt,id) {pe.wyswyg.exec(id,"outdent","");},
list:function(evt,id) {pe.wyswyg.exec(id,"insertunorderedlist","");},
numbered:function(evt,id) {pe.wyswyg.exec(id,"insertorderedlist","");},
undo:function(evt,id) {pe.wyswyg.exec(id,"undo","");},
redo:function(evt,id) {pe.wyswyg.exec(id,"redo","");},
unlink:function(evt,id) {pe.wyswyg.exec(id,"unlink","");},
table:function(evt,id) {pe.wyswyg.exec(id,"table","");},
image:function(evt,id) {
    pe.wyswyg.selImg=evt.target;
    if(evt.target.className==null||evt.target.className!="wyswyg_icon")
    document.getElementById(id+':insert_zoom').style.display='inline-block';
},
zoom:function(evt,id) {
    obj=pe.wyswyg.selImg;
    if(obj==null||obj.tagName!="IMG")return;
    if(obj.getAttribute('data-zoom')==null)
        obj.setAttribute('data-zoom',function_exists("pe.zoom.src")?pe.zoom.src(obj.src):obj.src);
    else
        obj.removeAttribute('data-zoom');
},
font:function(evt,id) {pe_p(id+"_style");},
setfont:function(evt,tag,id) {
    if(tag!='' && tag!=null) {
        pe.wyswyg.exec(id,"formatblock",tag);
    } else {
        //! remove formating
//        pe.wyswyg.exec(id,"removeFormat");
        pe.wyswyg.exec(id,"formatblock","<p>");
    }
},

link:function(evt,id)
{
    var link=document.getElementById(id+':link');
    var sel=pe.wyswyg.selected(evt,"sel");
    var obj=pe.wyswyg.selected(evt,"obj");
    if(sel.rangeCount==1 && sel.isCollapsed==true && sel.anchorNode.tagName=='A')
        obj=sel.anchorNode;
    //! if neither text selected nor an A tag
    if((sel.rangeCount<1 || sel.isCollapsed==true) && obj.tagName!='A')
        return;
    //! if text selected not an A tag
    if(obj.tagName!='A') {
        var a=document.createElement('A');
        a.setAttribute('href', 'http<?=(Core::$core->sec?"s":"")?>://');
        a.innerHTML=pe.wyswyg.selected(evt,"txt");
        sel.getRangeAt(sel.rangeCount-1).surroundContents(a);
        obj=a;
    }
    //! load url
    link.value=obj.getAttribute('href');
    //! get position
    var rt=obj.getBoundingClientRect();
    link.style.left=rt.left+'px';
    link.style.top=(rt.top+obj.offsetHeight)+'px';
    link.style.display='block';
    //! save object for setlink()
    pe.wyswyg.sel=obj;
    link.selectionStart = link.selectionEnd = link.value.length;
    link.focus();
},

setlink:function(evt,hrf,id){
    if(!hrf||!hrf.value||hrf.value=='')pe.wyswyg.exec(id,"unlink","");
    if(pe.wyswyg.sel&&pe.wyswyg.sel.tagName=='A')pe.wyswyg.sel.href=hrf.value;
    else alert(hrf.value);
},

event:function(evt,id,name,ctx)
{
    //! get plugins for subscribed for an event
    var ret=[],hookname='data-wyswyg-'+name;
    var obj=pe.wyswyg.selected("obj");
    pe.wyswyg.selImg=null;
    document.getElementById(id+':insert_zoom').style.display='none';
    if(evt!=null && evt.target!=null){
        hookname=hookname.replace('@TAG',evt.target.tagName.toLowerCase());
        if(evt.target.id==id+':edit')
            hookname='data-wyswyg-focus';
    }
    var i,plugins=document.querySelectorAll('['+hookname+']');
    for(i=0;i<plugins.length;i++) {
        var hooks=plugins[i].getAttribute(hookname).split(",");
        for(var h in hooks)
            if(function_exists(hooks[h]))
                ret.concat(eval(hooks[h]+"(evt,id,ctx)"));
    }
    return ret;
},

prevent: function(evt)
{
    evt.preventDefault();
    return false;
},

popup:function(evt, id, url)
{
    var popup=document.getElementById(id+'_popup');
    popup.innerHTML='';
    pe_p(id+'_popup',null,5);
    var r = new XMLHttpRequest();
    r.open('GET', url, false); r.send(null);
    if(r.status==200) {
        popup.innerHTML=r.responseText;
        var i,a=popup.querySelectorAll('a');
        for(i=0;i<a.length;i++){
            a[i].addEventListener( "click", pe.wyswyg.prevent, false );
        }
    } else
        popup.innerHTML=L("wyswyg_nohook");
},

search:function(inp,div)
{
    pe_w();
    var r=new RegExp(inp.value,'i');
    for(i in div.children) {
        if(inp.value=='' ||
            (div.children[i].src!=null && div.children[i].src.match(r)) ||
            (div.children[i].href!=null && div.children[i].href.match(r)) ||
            (div.children[i].alt!=null && div.children[i].alt.match(r)) ||
            (div.children[i].innerHTML!=null && div.children[i].innerHTML.match(r))
        ) {
            try{ div.children[i].getAttribute('style'); } catch(e) {}
            try{ div.children[i].removeAttribute('style'); } catch(e) {}
        } else {
            try{ div.children[i].setAttribute('style', 'display:none;'); } catch(e) {}
        }
    }
}
};
