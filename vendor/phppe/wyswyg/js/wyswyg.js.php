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
    if(is_array($_FILES['upload'])) {
        if($_FILES['upload']['type']!="text/html")
            $err=L("Upload HTML only!");
        else {
            $d=@file_get_contents($_FILES['upload']['tmp_name']);
            preg_match_all("|<body[^>]*>(.*?)<\/body|ims",$d,$b,PREG_SET_ORDER);
            $choose=!empty($b[0][1])?$b[0][1]:$d;
        }
    }
    die("<html><head><meta charset='utf-8'/></head><body>".($choose?"<div>".$choose."</div><script type='text/javascript'>parent.window['wyswyg_importdone'](\"".$_REQUEST['impform']."\");</script>":
        \PHPPE\View::_t("<!form>").
        "<input type='hidden' name='impform' value='".$_REQUEST['impform']."'>".
        "<input type='file' name='upload' onchange='parent.window[\"wyswyg_importstart\"](\"".$_REQUEST['impform']."\");this.form.submit();' id='".$_REQUEST['impform'].":import'>".
        ($err?"alert('".$err."');":"").
        "</form>")."</body></html>");
}

$bs = Core::isInst("bootstrap");
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
    "image": "glyphicon glyphicon-picture",
    "video": "glyphicon glyphicon-film",
    "attachment": "glyphicon glyphicon-paperclip",
    "undo": "glyphicon glyphicon-step-backward",
    "redo": "glyphicon glyphicon-step-forward",
};
<?php } ?>

function wyswyg_init()
{
    var i, allinstance=document.getElementsByTagName("TEXTAREA");
    for(i=0;i<allinstance.length;i++) {
        if(allinstance[i].className.indexOf("wyswyg")>-1)
            wyswyg_open(allinstance[i]);
    }
}

function wyswyg_open(source)
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
    var adj=28, tb=document.createElement('div');
    if(typeof conf[0] == 'number') adj=conf[0];
    if(typeof conf[0] == 'object') {
        for(var n in conf[0])
            if(parseInt(n)==0 || parseInt(n)>source.offsetWidth)
                adj=parseInt(conf[0][n]);
    }
    tb.setAttribute('id', id+':toolbar');
    tb.setAttribute('class', 'wyswyg_toolbar');
    tb.setAttribute('style', 'height:'+adj+'px;');
    source.parentNode.insertBefore(tb,source);

    //! populate toolbar in designmode
    if(document.designMode){
        var icons = {
            "file": [ "import" ],
            "style": [ "font", "bold", "italic", "underline", "strikethrough", "superscript", "subscript" ],
            "align": [ "outdent", "indent", "left", "center", "justify" ],
            "insert": [ "unordered", "ordered", "link", "unlink", "table", "image", "video", "attachment" ],
            "undo": [ "undo", "redo" ]
        };
        //! html edit area
        var edit=document.createElement('div');
        edit.setAttribute('id', id+':edit');
        edit.setAttribute('class', 'wyswyg');
        edit.setAttribute('style', 'height:'+(source.offsetHeight-0)+'px;padding:0px;background:#fff;color:#000;display:none;overflow:auto;');
        if(typeof window.parent['cms_getitem'] == 'function') {
            var style=null, item=window.parent['cms_getitem']();
            if(item!=null) {
                style=window.getComputedStyle(item, null);
                edit.setAttribute('class', 'wyswyg '+item.className);
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
        edit.setAttribute('onkeyup','wyswyg_setvalue("'+id+'");');
        edit.setAttribute('onmouseout','wyswyg_setvalue("'+id+'");');
        edit.setAttribute("ondrop",'wyswyg_drop(event,"'+id+'");');
        edit.setAttribute("contentEditable",true);
        edit.setAttribute("designMode","on");
        source.parentNode.insertBefore(edit,source);

        //! html toggle button
        var toggle = document.createElement('BUTTON');
        toggle.setAttribute('title',L('Toggle HTML/Source'));
        toggle.setAttribute('onclick','event.preventDefault();wyswyg_togglesrc(this);');
        tb.appendChild(toggle);

        //! add iconbar
        var ib = document.createElement('SPAN');
        ib.setAttribute('id', id+':icons');
        tb.appendChild(ib);

        //! add icons
        for (var menu in icons)
            if(icons.hasOwnProperty(menu)) {
                var ms = document.createElement('SPAN');
                ms.setAttribute('id', id+':'+menu);
                ms.setAttribute('class', 'wyswyg_menu');
                for(var i=0;i<icons[menu].length;i++)
                    if(typeof window['wyswyg_'+icons[menu][i]] == 'function') {
                        var mi = document.createElement('BUTTON');
                        mi.setAttribute('class', <?=($bs?"wyswyg_classes[icons[menu][i]]":"'wyswyg_icon wyswyg_icon-'+icons[menu][i]")?>);
                        mi.setAttribute('title',L('wyswyg_'+icons[menu][i]));
                        mi.setAttribute('onclick','event.preventDefault();wyswyg_'+icons[menu][i]+'(event,\"'+id+'\");wyswyg_setvalue(\"'+id+'\");');
                        ms.appendChild(mi);
                    }
                ib.appendChild(ms);
            }

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

function wyswyg_togglesrc(toggle)
{
    var edit = toggle.parentNode.nextSibling;
    var source = edit.nextSibling;
    //! check if it's in html mode
    if(edit.style.display=='block') {
        //! it is, switch to source mode
        toggle.setAttribute('class', '<?=$bs?"glyphicon glyphicon-eye":"wyswyg_icon wyswyg_toggle"?>-open');
        toggle.nextSibling.style.visibility='hidden';
        edit.style.display='none';
        source.style.width='100%';
        source.style.display='block';
        source.focus();
    } else {
        //! no, switch to html mode
        toggle.setAttribute('class', '<?=$bs?"glyphicon glyphicon-eye":"wyswyg_icon wyswyg_toggle"?>-close');
        toggle.nextSibling.style.visibility='visible';
        edit.style.display='block';
        edit.style.height=(source.offsetHeight)+'px';
        source.style.display='none';
        //! copy textarea value to edit div
        var output=source.value.toString().replace(/<(!?)\/([^>]+)>\n/g,"<$1/$2>").replace(/<\/form>/gi,"<!/form>").replace(/([{};])\n/g,"$1");
        edit.innerHTML=output;
        edit.focus();
     }
}

function wyswyg_setvalue(id)
{
    var source = document.getElementById(id);
    var edit = source.previousSibling;
    source.value = edit.innerHTML;
    //rte.value=rte.value.replace(">&quot;\"",">\"").replace(/&lt;!([^-])/gi,"<!$1").replace(/\=\"\"/g,"").replace(/\"\=\"\"/g,"\"").replace(/\=\"\"/g,"").replace(/alt=\"[\ ]+/gmi,"alt=\"").replace(/&lt;img/g,"<img");
    var i,txt=source.value.match(/<img class=[\"\'][\ ]?wysiwyg_icon[^>]+>([\'\"][^>]*?>)?/gmi,"$1");
    if(txt!=null&&txt.length>0) for(i=0;i<txt.length;i++) {
        var tmp=txt[i].match(/alt=\"[^\"]*[\">]?/gmi,"$1");
        if(tmp&&tmp[0]) {var t=tmp[0].substring(5,tmp[0].length-1).trim();t=t.replace("<!/form>","</form>");
        if(t.charAt(t.length-1)!='>') t=t+'>';
        source.value=source.value.replace(txt[i],t).replace("&lt;"+txt[i].substring(1,txt[i].length),t);}
    }
}

function wyswyg_exec(id,cmd,val)
{
//    var div=document.getElementById(id+':edit');
//    if(div)div.focus();
    try { return document.execCommand(cmd,false,val); }
    catch(e) { alert(e); return null; }
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
