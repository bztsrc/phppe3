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
    if(!empty($_FILES['upload'])) {
        if($_FILES['upload']['type']=="text/plain"){
            $choose=@file_get_contents($_FILES['upload']['tmp_name']);
        }elseif($_FILES['upload']['type']=="text/html") {
            $d=@file_get_contents($_FILES['upload']['tmp_name']);
            preg_match_all("|<body[^>]*>(.*?)<\/body|ims",$d,$b,PREG_SET_ORDER);
            $choose=!empty($b[0][1])?$b[0][1]:$d;
        } else {
            $err=L("Upload HTML only!");
        }
    }
    die("<html><head><meta charset='utf-8'/></head><body>".($choose?"<div>".$choose."</div><script type='text/javascript'>parent.window['wyswyg_importdone'](\"".$_REQUEST['impform']."\");</script>":
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

<?php if($bs) {?>
var wyswyg_classes = {
    "toggle": "glyphicon glyphicon-eye",
    "import": "glyphicon glyphicon-upload",
    "font": "glyphicon glyphicon-font",
    "bold": "glyphicon glyphicon-bold",
    "italic": "glyphicon glyphicon-italic",
    "underline": "glyphicon glyphicon-text-color",
    "strikethrough": "glyphicon glyphicon-gbp",
    "superscript": "glyphicon glyphicon-superscript",
    "subscript": "glyphicon glyphicon-subscript",
    "outdent": "glyphicon glyphicon-indent-right",
    "indent": "glyphicon glyphicon-indent-left",
    "left": "glyphicon glyphicon-align-left",
    "center": "glyphicon glyphicon-align-center",
    "right": "glyphicon glyphicon-align-right",
    "justify": "glyphicon glyphicon-align-justify",
    "unordered": "glyphicon glyphicon-list",
    "ordered": "glyphicon glyphicon-list-alt",
    "link": "glyphicon glyphicon-globe",
    "unlink": "glyphicon glyphicon-erase",
    "table": "glyphicon glyphicon-th",
    "content": "glyphicon glyphicon-file",
    "image": "glyphicon glyphicon-picture",
    "video": "glyphicon glyphicon-film",
    "attachment": "glyphicon glyphicon-paperclip",
    "undo": "glyphicon glyphicon-step-backward",
    "redo": "glyphicon glyphicon-step-forward",
};
<?php } ?>
var wyswyg_sel=null;

function wyswyg_init()
{
    var i, allinstance=document.querySelectorAll("TEXTAREA.wyswyg");
    var icons = {
        "style": [ "font", "bold", "italic", "underline", "strikethrough", "superscript", "subscript" ],
        "align": [ "outdent", "indent", "left", "center", "justify", "right" ],
        "insert": [ "unordered", "ordered", "link", "unlink", "table" ],
        "hooks": <?=json_encode($toolbar)?>,
        "undo": [ "undo", "redo" ]
    };
    //! load toolbar hooks
    var plugins=document.querySelectorAll('[data-wyswyg-toolbar]');
    for(var i=0;i<plugins.length;i++) {
        icons['hooks'].concat(plugins[i].getAttribute('data-wyswyg-toolbar').split(","));
    }
    for(i=0;i<allinstance.length;i++) {
        wyswyg_open(allinstance[i], icons);
    }
}

function wyswyg_open(source, icons)
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
        edit.setAttribute('class', 'wyswyg input');
        edit.setAttribute('style', 'height:'+(source.offsetHeight-0)+'px;padding:0px;background:#fff;color:#000;display:none;overflow:auto;');
        if(typeof window.parent['cms_getitem'] == 'function') {
            var style=null, item=window.parent['cms_getitem']();
            if(item!=null) {
                style=window.getComputedStyle(item, null);
                edit.setAttribute('class', 'wyswyg input '+item.className);
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
        edit.setAttribute('data-wyswyg-select-a', 'wyswyg_link');
        edit.setAttribute('data-wyswyg-select-img', 'wyswyg_image');
        //! set up event handlers and design mode
        edit.setAttribute('onmouseup','wyswyg_event(event,"'+id+'","select-@TAG");');
        edit.setAttribute('onkeyup','wyswyg_setvalue("'+id+'");');
        edit.setAttribute('onmouseout','wyswyg_setvalue("'+id+'");');
        edit.setAttribute("ondrop",'wyswyg_drop(event,"'+id+'");');
        edit.setAttribute("contentEditable",true);
        edit.setAttribute("designMode","on");
        source.parentNode.insertBefore(edit,source);

        //! html toggle button
        var toggle = document.createElement('BUTTON');
        toggle.setAttribute('title',L('Toggle HTML/Source'));
        toggle.setAttribute('onclick','event.preventDefault();wyswyg_togglesrc(this, true);');
        tb.appendChild(toggle);

        //! import button
        var imp = document.createElement('BUTTON');
        imp.setAttribute('title',L('wyswyg import'));
        imp.setAttribute('class', <?=($bs?"wyswyg_classes['import']":"'wyswyg_icon wyswyg_icon-import'")?>);
        imp.setAttribute('onclick','event.preventDefault();wyswyg_import(event, "'+id+'");');
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
                    txt+=tag.replace('>'," onclick='event.preventDefault();pe_p();' onmouseover='event.preventDefault();wyswyg_setfont(event,\""+i.substr(12)+"\",\""+id+"\");'>")+LANG[i]+tag.replace('<','</');
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
                    if(typeof window['wyswyg_'+func] == 'function') {
                        var mi = document.createElement('BUTTON');
                        mi.setAttribute('class',
                        <?=$bs?"wyswyg_classes[name]!=null?wyswyg_classes[name]:":""?>'wyswyg_icon wyswyg_icon-'+name);
                        mi.setAttribute('title',L('wyswyg_'+name));
                        mi.setAttribute('onclick','event.preventDefault();wyswyg_'+func+'(event,\"'+id+'\"'+ext+');wyswyg_setvalue(\"'+id+'\");');
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
        link.setAttribute('onkeyup','wyswyg_setlink(event,this,\"'+id+'\");');
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

        //! switch to html mode
        wyswyg_togglesrc(toggle);
    }
}

function wyswyg_drop(evt,id)
{
    setTimeout(function(){
        var source=document.getElementById(id);
        var h,hooks=source.getAttribute('data-drophook');
        if(hooks!=null) hooks=hooks.split(',');
        for(h in hooks) {
            if(typeof window[hooks[h]]=='function')
                window[hooks[h]](evt,id);
        }
    },50);
}

function wyswyg_togglesrc(toggle,focus)
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
        var output=source.value.toString().replace(/<(!?)\/([^>]+)>\n/g,"<$1/$2>").replace(/<\/form>/gi,"<!/form>").replace(/([{};])\n/g,"$1");
        edit.innerHTML=output;
        if(focus!=null)
            edit.focus();
     }
}

function wyswyg_setvalue(id)
{
    var source = document.getElementById(id);
    var edit = source.previousSibling;
    source.value = edit.innerHTML;
    //rte.value=rte.value.replace(">&quot;\"",">\"").replace(/&lt;!([^-])/gi,"<!$1").replace(/\=\"\"/g,"").replace(/\"\=\"\"/g,"\"").replace(/\=\"\"/g,"").replace(/alt=\"[\ ]+/gmi,"alt=\"").replace(/&lt;img/g,"<img");
    var i,txt=source.value.match(/<img class=[\"\'][\ ]?wyswyg_icon[^>]+>([\'\"][^>]*?>)?/gmi,"$1");
    if(txt!=null&&txt.length>0) for(i=0;i<txt.length;i++) {
        var tmp=txt[i].match(/alt=\"[^\"]*[\">]?/gmi,"$1");
        if(tmp&&tmp[0]) {var t=tmp[0].substring(5,tmp[0].length-1).trim();t=t.replace("<!/form>","</form>");
        if(t.charAt(t.length-1)!='>') t=t+'>';
        source.value=source.value.replace(txt[i],t).replace("&lt;"+txt[i].substring(1,txt[i].length),t);}
    }
}

function wyswyg_selected(evt, type)
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
                if (sel.anchorNode.childNodes)
                    obj=sel.anchorNode.childNodes[ sel.anchorOffset ];
                if (sel.rangeCount) {
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
}

function wyswyg_exec(id,cmd,val)
{
//    var div=document.getElementById(id+':edit');
//    if(div)div.focus();
    try { return document.execCommand(cmd,false,val); }
    catch(e) { alert(e); return null; }
}
function wyswyg_import(evt,id) {document.getElementById(id+':impframe').contentWindow.document.getElementsByTagName('INPUT')[4].click();}
function wyswyg_importdone(id){
var choose=choose=document.getElementById(id+':impframe').contentWindow.document.getElementsByTagName('DIV')[0].innerHTML;
document.getElementById(id+':impframe').src='js/wyswyg.js?impform='+id;
document.getElementById(id+':edit').innerHTML=choose;
wyswyg_setvalue(id);
}

function wyswyg_bold(evt,id) {wyswyg_exec(id,"bold","");}
function wyswyg_italic(evt,id) {wyswyg_exec(id,"italic","");}
function wyswyg_underline(evt,id) {wyswyg_exec(id,"underline","");}
function wyswyg_strikethrough(evt,id) {wyswyg_exec(id,"strikethrough","");}
function wyswyg_superscript(evt,id) {wyswyg_exec(id,"superscript","");}
function wyswyg_subscript(evt,id) {wyswyg_exec(id,"subscript","");}
function wyswyg_left(evt,id) {wyswyg_exec(id,"justifyleft","");}
function wyswyg_center(evt,id) {wyswyg_exec(id,"justifycenter","");}
function wyswyg_justify(evt,id) {wyswyg_exec(id,"justifyfull","");}
function wyswyg_right(evt,id) {wyswyg_exec(id,"justifyright","");}
function wyswyg_indent(evt,id) {wyswyg_exec(id,"indent","");}
function wyswyg_outdent(evt,id) {wyswyg_exec(id,"outdent","");}
function wyswyg_list(evt,id) {wyswyg_exec(id,"insertunorderedlist","");}
function wyswyg_numbered(evt,id) {wyswyg_exec(id,"insertorderedlist","");}
function wyswyg_undo(evt,id) {wyswyg_exec(id,"undo","");}
function wyswyg_redo(evt,id) {wyswyg_exec(id,"redo","");}
function wyswyg_unlink(evt,id) {wyswyg_exec(id,"unlink","");}
function wyswyg_font(evt,id) {pe_p(id+"_style");}
function wyswyg_setfont(evt,tag,id) {
    if(tag!='' && tag!=null) {
        wyswyg_exec(id,"formatblock",tag);
    } else {
        //! remove formating
//        wyswyg_exec(id,"removeFormat");
        wyswyg_exec(id,"formatblock","<p>");
    }
}
function wyswyg_link(evt,id) {
    var link=document.getElementById(id+':link');
    var sel=wyswyg_selected(evt,"sel");
    var obj=wyswyg_selected(evt,"obj");
    //! if neither text selected nor an A tag
    if((sel.rangeCount<1 || sel.isCollapsed==true) && obj.tagName!='A')
        return;
    //! if text selected not an A tag
    if(obj.tagName!='A') {
        var a=document.createElement('A');
        a.setAttribute('href', 'http<?=(Core::$core->sec?"s":"")?>://');
        a.innerHTML=wyswyg_selected(evt,"txt");
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
    wyswyg_sel=obj;
    link.selectionStart = link.selectionEnd = link.value.length;
    link.focus();
}
function wyswyg_setlink(evt,hrf,id){
    if(!hrf||!hrf.value||hrf.value=='')wyswyg_exec(id,"unlink","");
    if(wyswyg_sel&&wyswyg_sel.tagName=='A')wyswyg_sel.href=hrf.value;
    else alert(hrf.value);
}

function wyswyg_event(evt,id,name){
    //! get plugins for subscribed for an event
    var ret=[],hookname='data-wyswyg-'+name;
    if(evt!=null && evt.target!=null){
        hookname=hookname.replace('@TAG',evt.target.tagName.toLowerCase());
        if(evt.target.id==id+':edit')
            hookname='data-wyswyg-focus';
    }
    var i,plugins=document.querySelectorAll('['+hookname+']');
    for(i=0;i<plugins.length;i++) {
        var hooks=plugins[i].getAttribute(hookname).split(",");
        for(var h in hooks)
            if(typeof window[hooks[h]] == 'function')
                ret.concat(window[hooks[h]](evt,id));
    }
    return ret;
}

function wyswyg_popup(evt, id, url)
{
    var popup=document.getElementById(id+'_popup');
    popup.innerHTML='';
    pe_p(id+'_popup',null,5);
    var r = new XMLHttpRequest();
    r.open('GET', url, false); r.send(null);
    if(r.status==200) {
        popup.innerHTML=r.responseText;
    } else
        popup.innerHTML=L("Unable to load AJAX hook");
}

function wyswyg_search(inp,div)
{
    pe_w();
    var r=new RegExp(inp.value,'i');
    for(i in div.children) {
        if(inp.value=='' ||
            (div.children[i].src!=null && div.children[i].src.match(r)) ||
            (div.children[i].href!=null && div.children[i].href.match(r)) ||
            (div.children[i].innerHTML!=null && div.children[i].innerHTML.match(r))
        ) {
            try{ div.children[i].getAttribute('style'); } catch(e) {}
            try{ div.children[i].removeAttribute('style'); } catch(e) {}
        } else {
            try{ div.children[i].setAttribute('style', 'display:none;'); } catch(e) {}
        }
    }
}
