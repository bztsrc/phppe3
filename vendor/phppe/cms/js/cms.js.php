<?php
use PHPPE\Core as PHPPE;

$addons=PHPPE::addon();
ksort($addons,SORT_FLAG_CASE | SORT_NATURAL);
?>
var cms_editdiv=null;
var cms_border=null;
var cms_configure=null;
var cms_conf='';
var cms_alladdon=<?=json_encode($addons)?>;
var cms_layoutonly,cms_anim=null,cms_animidx=0;
function cms_init()
{
    cms_editdiv=document.createElement('iframe');
    cms_editdiv.id='cms_editdiv';
    cms_editdiv.src='about:blank';
    cms_editdiv.scrolling='auto';
    cms_editdiv.style.display='none';
    document.getElementsByTagName('body')[0].appendChild(cms_editdiv);
}
function cms_pagemeta(o)
{
return cms_edit(o,'pagemeta');
}
function cms_pagepublish(o)
{
return cms_edit(o,'pagepublish');
}
function cms_pagefilters(o)
{
return cms_edit(o,'pagefilters');
}
function cms_layoutmeta(o)
{
return cms_edit(o,'layoutmeta');
}
function cms_pagedds(o)
{
return cms_edit(o,'pagedds');
}
function cms_pagerevert(o)
{
return cms_edit(o,'pagerevert');
}
function cms_pagedelete(o)
{
return cms_edit(o,'pagedelete');
}
function cms_layoutdelete(o)
{
return cms_edit(o,'layoutdelete');
}
function cms_pageadd(o)
{
return cms_edit(o,'pageadd');
}
function cms_layoutadd(o)
{
return cms_edit(o,'layoutadd');
}
function cms_pagelist(o,t,i)
{
return cms_edit(o,t,i);
}
function cms_animate(dir)
{
    cms_editdiv.style.left=zoom_steps[cms_animidx].x+'px';
    cms_editdiv.style.top=zoom_steps[cms_animidx].y+'px';
    cms_editdiv.style.width=zoom_steps[cms_animidx].w+'px';
    cms_editdiv.style.height=zoom_steps[cms_animidx].h+'px';
    cms_animidx++; if(zoom_steps[cms_animidx]==null) {
        clearInterval(cms_anim);
        if(dir==null) {
            var old=zoom_steps[0];
            zoom_steps[0]=zoom_steps[zoom_step];
            zoom_calczoom_steps(old.x,old.y,old.w,old.h);
            cms_animidx=0;
        } else {
            cms_animatefinish();
        }
    }
}
function cms_animatefinish() {
            if(cms_editdiv!=null) {cms_editdiv.style.display='none';cms_editdiv.src='about:blank';}
}
function cms_edit(o,t,i,c)
{
    var p=zoom_getpos(o);
    var ww=(window.innerWidth?window.innerWidth:document.body.offsetWidth);var wh=(window.innerHeight?window.innerHeight:document.body.offsetHeight);
    cms_editdiv.contentWindow.document.body.innerHTML='';
    p.y-=(t=="wysiwyg"?28:4);
    zoom_steps[0]=p;
    zoom_return=null;
<?php if(empty(PHPPE::$core->noanim)) { ?>
    cms_editdiv.setAttribute('style','position:absolute;overflow:hidden;border:none 0px;display:block;left:'+p.x+'px;top:'+p.y+'px;width:'+o.offsetWidth+'px;height:'+o.offsetHeight+'px;z-index:10001;background:#404040;color:#fff;opacity:0.8;box-shadow: 3px 3px 8px #000000;');
<?php } ?>
    var w=parseInt(o.parentNode.offsetWidth),h=parseInt(o.parentNode.offsetHeight)+22;
    if(w>ww-p.x) w=ww-p.x;
    if(t.substr(t.length-4)=="list"||t.substr(0,4)=="page") h=0;
    if(t=="color"||t=="pagerevert") { w=200; h=200; }
    if(t=="text"||t=="pass"||t=="num"||t=="select"||t=="check"||t=="email"||t=="phone"||t=="file"||t=="date"||t=="datetime") {w=400;h=20;}
    if(t=="wysiwyg" && h < 300) h=300;
    if(t=="pagepublish") h=310;
    if(t=="pageadd") h=220;
    if(t=="pagefilters"||t=="pagedelete"||t=="layoutadd"||t=="layoutdelete") h=110;
    if(t=="pagelist") h=400;
    if(h>wh) h=wh;
    var nx=i==null||t.substr(t.length-4)=="list"?Math.round(ww*0.2):p.x;
    var ny=i==null||t.substr(t.length-4)=="list"?Math.round(wh*0.2):p.y;
    var nw=i==null||t.substr(t.length-4)=="list"?Math.round(ww*0.6):w;
    var nh=i==null&&h<100||t.substr(t.length-4)=="list"?Math.round(wh*0.6):(h+26+(t=="wysiwyg"?30:0));
    if(ny+nh>wh) ny=wh-nh;
    cms_editdiv.src='cms/'+(i!=null?'param/'+i:t)+'?w='+w+'&h='+h;
    cms_editdiv.setAttribute('data-zoom-nodecor',true);
    zoom_steps[zoom_step]={x:nx,y:ny,w:nw,h:nh};
    zoom_open(o,'cms_editdiv',true);
}
function cms_getbreakpoints()
{
    var i,s=document.styleSheets,pr=document.getElementById('pe_pageresp'),cache=cookie_get('cms_brkpoints');
    if(pr!=null && cache!=null && cache!='') {
        if(cache=='none')
            document.getElementById('pageresp').style.display='none';
        else
            pr.innerHTML=cache;
        return;
    }
    var ul=document.createElement('UL');
    for(i=0;i < s.length;i++) {
        var m="";
        for(k in document.styleSheets[i].cssRules) {
            var sh=document.styleSheets[i].cssRules[k].styleSheet;
            if(sh==null||sh.href==null) continue;
            var http_request = new XMLHttpRequest();
            http_request.open('GET',sh.href,false);
            http_request.send();
            if(http_request.status==200) {
                m+=http_request.responseText+"";
            }
        }
    }
    var r=m.match(/@media([^{]+)/g);
    for(i=0;i<r.length;i++) {
        var w=r[i].match(/(min|max)-width[^0-9]*?([0-9]+[a-z\%]+)/);
        if(w!=null&&w[2]!=null){
            var li=document.createElement('LI');
            li.setAttribute('onclick','cms_resize("'+w[2]+'");');
            li.innerHTML=w[2];
            ul.appendChild(li);
        }
    }
    if(pr!=null && ul.childNodes.length) {
            var li=document.createElement('LI');
            li.setAttribute('onclick','cms_resize("100%");');
            li.innerHTML='100%';
            ul.appendChild(li);
            pr.appendChild(ul);
            cookie_set('cms_brkpoints',pr.innerHTML,1,'/');
    } else {
        document.getElementById('pageresp').style.display='none';
        cookie_set('cms_brkpoints','none',1,'/');
    }
}
function cms_pageresp()
{
    return pe_p("pageresp");
}
function cms_resize(size)
{
    document.body.style.width=size;
    if(cms_border==null) {
        cms_border=document.createElement('div');
        document.body.appendChild(cms_border);
    }
    cms_border.setAttribute('style','width:1px !important;height:100% !important;border-right:dotted 2px red !important;display:block;position:fixed;top:0px;left:'+size);
}
function cms_styleguide()
{
    return "<?=str_replace("\n","\\n",str_replace('"','\"',PHPPE::template("styleguide")))?>";
}
function cms_addons(wwid,tag)
{
    var t="",key;
    for(key in cms_alladdon){
        if(cms_alladdon[key].conf.substr(0,1)!='*'||cms_layoutonly!=null||tag=='var')
        t+="<img class='wysiwyg_icon' alt='<!"+tag+" "+key+">' id='"+wwid+":addons_"+key+"' title='"+htmlspecialchars(cms_alladdon[key].name)+"' data-search='"+htmlspecialchars(key+" "+cms_alladdon[key].name)+"' data-conf='"+htmlspecialchars(cms_alladdon[key].conf)+"' src='js/wysiwyg.js/"+escape("<!")+tag+" "+escape(key+">").replace(/\//g,"!2F!").replace(/\+/g,"!2B!")+"'>";
    }
    <?php if(!PHPPE::lib("CMS")->expert) { ?>
    t+="<small style='display:block;position:fixed;bottom:20px;color:#fff;'>"+L("help_templater")+"</small>";
    <?php } ?>
    return t.replace("\n","");
}
function cms_addonoptions(wwid)
{
    var t="",key;
    for(key in cms_alladdon){
        if(cms_alladdon[key].conf.substr(0,1)!='*'||cms_layoutonly!=null)
        t+="<option value='"+htmlspecialchars(key)+"'>"+htmlspecialchars(cms_alladdon[key].name?cms_alladdon[key].name:L(key))+"</option>";
    }
    return t.replace("\n","");
}
function cms_templater(wwid)
{
    <?php
        $t="";
        foreach([
            "="=>"expression",
            "L"=>"label",
            "date"=>"expression",
            "time"=>"expression",
            "difftime"=>"expression",
            "/form"=>"variable [url [onsubmitjs",
            "/if"=>"expression",
            "else"=>"",
            "/foreach"=>"variable",
            "/template"=>"",
            "include"=>"view",
            "app"=>"",
            "dump"=>"variable",
            "var"=>"addon ) variable",
            "field"=>"addon ) variable",
            "widget"=>"addon ) variable",
            "cms"=>"addon ) variable"
            ] as $k=>$v) {
            if($k[0]=="/") {
                $K=substr($k,1);
                $t.="<img class='wysiwyg_icon' alt='<!".$K.">' id='\"+wwid+\":templater_".($K=="="?"eval":$K)."' data-search='".htmlspecialchars($K)."' data-conf='".htmlspecialchars($v)."' src='js/wysiwyg.js/".urlencode("<!".$K.">")."'>";
                $t.="<img class='wysiwyg_icon' alt='<!".$k.">' id='\"+wwid+\":templater_".($k=="="?"eval":$k)."_close' src='js/wysiwyg.js/".urlencode("<!!2F!".$K.">")."' data-search='".htmlspecialchars($K)."'>";
            } else
                $t.="<img class='wysiwyg_icon' alt='<!".$k.">' id='\"+wwid+\":templater_".($k=="="?"eval":$k)."' data-search='".htmlspecialchars($k)."' data-conf='".htmlspecialchars($v)."' src='js/wysiwyg.js/".urlencode("<!".$k.">")."'>";
        }
        if(!PHPPE::lib("CMS")->expert)
            $t.="<small style='display:block;position:fixed;bottom:20px;color:#fff;'>".L("help_templater")."</small>";
    ?>
    return "<?=str_replace("\n","\\n",$t)?>";
}
function cms_layout(wwid,val,conf,w,h)
{
    var g=cms_styleguide();
    cms_layoutonly = true;
    var t="<div id='"+wwid+":conf' class='wysiwyg_conf confpanel' style='bottom:15px;' data-hook='cms_confhook' data-imghook='cms_imghook' data-linkhook='cms_linkhook'></div>";
    t+="<div id='"+wwid+":tools' class='wysiwyg_tools' style='bottom:15px;'>";
    t+="<div style='text-align:right;color:#fff;'><span id='"+wwid+":tools_min' onclick='cms_tools_min(this);' style='cursor:pointer;'>▼</span> ";
    if(g!='') t+="<span title='"+L('Guide')+"' id='"+wwid+":tools_gde' onclick='cms_tools_switch(\""+wwid+":tools_guide\");' style='cursor:pointer;'>☼</span> ";
    t+="<span title='"+L('Format')+"' id='"+wwid+":tools_tmp' onclick='cms_tools_switch(\""+wwid+":tools_templater\");' style='cursor:pointer;'>◎</span> ";
    t+="<span title='"+L("Fields")+"' id='"+wwid+":tools_fld' onclick='cms_tools_switch(\""+wwid+":tools_fields\");' style='cursor:pointer;'>⊞</span> ";
    t+="<span title='"+L("Add-Ons")+"' id='"+wwid+":tools_ads' onclick='cms_tools_switch(\""+wwid+":tools_addons\");' style='cursor:pointer;'>⊕</span> ";
    t+="<input type='text' class='input' id='"+wwid+":tools_searchinp' value='' onfocus='cms_tools_search(this,\""+wwid+"\");' onkeyup='cms_tools_search(this,\""+wwid+"\");' style='color:#000;width:100px;'></div>";
    t+="<div id='"+wwid+":tools_container' style='display:block;'>";
    t+="<div id='"+wwid+":tools_templater' style='display:none;width:200px;height:400px;overflow:auto;'>"+cms_templater(wwid)+"</div>";
    t+="<div id='"+wwid+":tools_guide' style='display:none;width:100%;height:400px;overflow:auto;color:#fff;'></div>";
    t+="<div id='"+wwid+":tools_fields' style='display:none;width:200px;height:400px;overflow:auto;'>"+cms_addons(wwid,"field")+"</div>";
    t+="<div id='"+wwid+":tools_addons' style='display:block;width:200px;height:400px;overflow:auto;'>"+cms_addons(wwid,"widget")+"</div>";
    t+="<div id='"+wwid+":tools_results' style='display:none;width:200px;height:400px;overflow:auto;color:#fff;'></div>";
    t+="</div></div><div id='"+wwid+":tools_guide_' class='cms_guide' style='display:none;'>"+g+"</div>";
    setTimeout("cms_generateguide('"+wwid+"')",10);
    return t;
}
function cms_wysiwyg(wwid,val,conf,w,h)
{
    var g=cms_styleguide();
    var t="<div id='"+wwid+":conf' class='wysiwyg_conf confpanel' style='top:1px;' data-hook='cms_confhook' data-imghook='cms_imghook' data-linkhook='cms_linkhook'></div>";
    t+="<div id='"+wwid+":tools' class='wysiwyg_tools' style='top:1px;'>";
    t+="<div style='text-align:right;color:#fff;'><span id='"+wwid+":tools_min' onclick='cms_tools_min(this);' style='cursor:pointer;'>▶</span> ";
    if(g!='') t+="<span title='"+L('Guide')+"' id='"+wwid+":tools_gde' onclick='cms_tools_switch(\""+wwid+":tools_guide\");' style='cursor:pointer;'>☼</span> ";
    t+="<span title='"+L('Format')+"' id='"+wwid+":tools_tmp' onclick='cms_tools_switch(\""+wwid+":tools_templater\");' style='cursor:pointer;'>◎</span> ";
    t+="<span title='"+L("Fields")+"' id='"+wwid+":tools_fld' onclick='cms_tools_switch(\""+wwid+":tools_fields\");' style='cursor:pointer;'>⊞</span> ";
    t+="<span title='"+L("Add-Ons")+"' id='"+wwid+":tools_ads' onclick='cms_tools_switch(\""+wwid+":tools_addons\");' style='cursor:pointer;'>⊕</span> ";
    t+="<input type='text' id='"+wwid+":tools_searchinp' value='' onfocus='cms_tools_search(this,\""+wwid+"\");' onkeyup='cms_tools_search(this,\""+wwid+"\");' style='color:#000;width:100px;'></div>";
    t+="<div id='"+wwid+":tools_container' style='display:none;'>";
    t+="<div id='"+wwid+":tools_templater' style='display:none;width:200px;height:"+h+"px;overflow:auto;'>";
    t+="<img class='wysiwyg_icon' alt='<!=>' id='"+wwid+":templater_eval' title='"+L("Expression")+"' data-search='=' data-conf='expression' src='js/wysiwyg.js/"+escape("<!=>")+"'>";
    t+="<img class='wysiwyg_icon' alt='<!L>' id='"+wwid+":templater_L' title='"+L("Translation")+"' data-search='L' data-conf='label' src='js/wysiwyg.js/"+escape("<!L>")+"'>";
    t+="<img class='wysiwyg_icon' alt='<!date>' id='"+wwid+":templater_date' title='"+L("Localized date")+"' data-search='date' data-conf='expression' src='js/wysiwyg.js/"+escape("<!date>")+"'>";
    t+="<img class='wysiwyg_icon' alt='<!time>' id='"+wwid+":templater_time' title='"+L("Localized time")+"' data-search='time' data-conf='expression' src='js/wysiwyg.js/"+escape("<!time>")+"'>";
    t+="<img class='wysiwyg_icon' alt='<!difftime>' id='"+wwid+":templater_difftime' title='"+L("Human readable time difference")+"' data-search='difftime' data-conf='expression' src='js/wysiwyg.js/"+escape("<!difftime>")+"'>";
<?php if(!PHPPE::lib("CMS")->expert) { ?>
    t+="<small style='display:block;position:fixed;bottom:20px;color:#fff;'>"+L("help_templater")+"</small>";
<?php } ?>
    t+="</div>";
    t+="<div id='"+wwid+":tools_guide' style='display:"+(g!=''?"block":"none")+";width:100%;height:"+h+"px;overflow:auto;color:#fff;'></div>";
    t+="<div id='"+wwid+":tools_fields' style='display:none;width:200px;height:"+h+"px;overflow:auto;'>"+cms_addons(wwid,"var")+"</div>";
    t+="<div id='"+wwid+":tools_addons' style='display:"+(g!=''?"none":"block")+";width:200px;height:"+h+"px;overflow:auto;'>"+cms_addons(wwid,"widget")+"</div>";
    t+="<div id='"+wwid+":tools_results' style='display:none;width:200px;height:"+h+"px;overflow:auto;color:#fff;'></div>";
    t+="</div></div><div id='"+wwid+":tools_guide_' class='cms_guide' style='display:none;'>"+g+"</div>";
    setTimeout("cms_generateguide('"+wwid+"')",10);
    return t;
}
function cms_generateguide(wwid)
{
    var i,src=document.getElementById(wwid+':tools_guide_').getElementsByTagName('DIV');
    var t="",dst=document.getElementById(wwid+':tools_guide');
    dst.innerHTML="";
    for(i=0;i<src.length;i++) {
        var tid=src[i].getAttribute('data-id');
        if(tid==null||tid=='') continue;

        t+="<img class='styleguide_icon wysiwyg_icon' data-search='"+htmlspecialchars(tid)+"'";
        t+=" src='js/wysiwyg.js/"+escape(tid).replace(/\//g,"!2F!").replace(/\+/g,"!2B!")+"'";
        t+=" onmouseover='popup_open(this,\""+wwid+':tools_guide_'+i+"\");' style='cursor:pointer;'>";
        src[i].setAttribute('id',wwid+':tools_guide_'+i);
        src[i].style.display='none';
/*
        var div=document.createElement('div');
        div.innerHTML=tid;
        div.setAttribute('onmouseover','popup_open(this,"'+wwid+':tools_guide_'+i+'");');
        div.setAttribute('onmousedown','cms_insertguide("'+wwid+'",'+i+');');
        div.setAttribute('data-search',tid);
        div.setAttribute('style','cursor:pointer;');
        dst.appendChild(div);
*/
    }
    dst.innerHTML=t;
    <?php if(!PHPPE::lib("CMS")->expert) {?>
    var sm=document.createElement('small');
    sm.innerHTML=L('help_guide');
    sm.setAttribute('style','display:block;position:fixed;bottom:20px;');
    dst.appendChild(sm);
    <?php } ?>
    document.getElementById(wwid+':tools_guide_').style.display='block';
}
function cms_insertguide(wwid,i)
{
    var t=document.getElementById(wwid+':tools_guide_'+i).innerHTML;
    wysiwyg_insert(wwid,t);
    document.getElementById(wwid+':frame').focus();
    return true;
}
function cms_tools_min(icn)
{
    var obj=document.getElementById(icn.id.replace(/:[^:]+$/,'')+':tools_container');
    if(obj.style.display=='block'){ obj.style.display='none'; icn.innerHTML='▶'; }else { obj.style.display='block'; icn.innerHTML='▼'; }
}
function cms_tools_switch(tab)
{
    var fid=tab.replace(/:[^:]+$/,'');
    var obj=document.getElementById(fid+':tools_container');
    if(obj.style.display!='block'){ obj.style.display='block'; document.getElementById(fid+':tools_min').innerHTML='▼'; }
    document.getElementById(fid+':tools_templater').style.display='none';
    document.getElementById(fid+':tools_guide').style.display='none';
    document.getElementById(fid+':tools_fields').style.display='none';
    document.getElementById(fid+':tools_addons').style.display='none';
    document.getElementById(fid+':tools_results').style.display='none';
    document.getElementById(tab).style.display='block';
}
function cms_tools_search(inp,wwid)
{
    var res=document.getElementById(wwid+':tools_results');
    res.innerHTML="";
    cms_tools_switch(wwid+":tools_results");
    if(inp.value!='') {
        var i,haystack;
        var re=new RegExp(inp.value,"i");
        haystack=document.getElementById(wwid+':tools_container').getElementsByTagName('IMG');
        for(i=0;i<haystack.length;i++){
            var s=haystack[i].getAttribute('data-search');
            if(s!=null && s!='' && haystack[i].parentNode.id!=wwid+':tools_results' && (s.indexOf(inp.value)>-1||s.match(re))){
             res.appendChild(haystack[i].cloneNode());
             res.appendChild(document.createElement('BR'));
            }
        }
    }
    if(res.firstChild==null) res.innerHTML='<small style="color:#808080;">'+L('No results')+'</small>';
}
function cms_objfld(obj,wwid)
{
    var s=obj.value;
    if(!s.match(/^[a-zA-Z\_][a-zA-Z\_0-9]*\.[a-zA-Z\_][a-zA-Z\_0-9]*/))
        obj.className+=' errinput';
    else
        obj.className=obj.className.replace(' errinput','');
    cms_setattr(obj,wwid);
}
function cms_setattr(obj,wwid)
{
    if(cms_configure==null||cms_configure.alt==null) return;
    var i,j,p=0,form=document.getElementById(wwid+':cfgform');
    var inp=new Array(form.rows[0].cells[0].firstChild!=null&&form.rows[0].cells[0].firstChild.value!=null?form.rows[0].cells[0].firstChild.value:cms_conf[0]);
    for(i=0;i<form.rows.length;i++) {
        if(cms_conf[i]=="(") { p=1; inp.push("("); }
        if(cms_conf[i+1]==")"&&p) { for(j=inp.length-1;j>0&&(inp[j]==""||inp[j]==",");j--) inp.pop(); p=0; inp.push(")"); }
        if(form.rows[i]==null||form.rows[i].cells[1]==null||form.rows[i].cells[1].firstChild==null) continue;
        inp.push(form.rows[i].cells[1].firstChild.value!=''||form.rows[i].cells[1].firstChild.type=="checkbox"?(form.rows[i].cells[1].firstChild.type=="checkbox"?(form.rows[i].cells[1].firstChild.checked?"1":""):form.rows[i].cells[1].firstChild.value):(p?"":"-"));
        if(p) inp.push(",");
    }
    for(i=inp.length-1;i>0&&inp[i]=="-";i--) inp.pop();
    var alt=inp.join(" ").replace(/[\ ]*\([\ ]*/g,"(").replace(/[\ ]*\)[\ ]*/g,") ").replace("()","").replace(/[\ ]*\,[\ ]*/g,",").replace(/[\ ]+$/,"");
    cms_configure.alt="<"+"!"+alt+">";
    var d=alt.split(' ');
    var url=(d[1]==null?d[0]:d[0]+' '+(d[1].match(/^[a-z]+=['"]/)?d[1].substring(d[1].indexOf('=')+2,d[1].length-1):d[1]))+(d[2]!=null?' '+d[2]:'');
    cms_configure.src=cms_configure.src.replace(/wysiwyg\.js\/.*$/,"wysiwyg.js/"+escape("<"+"!"+url+">").replace(/\//g,"!2F!").replace(/\+/g,"!2B!"));
}
function cms_confhook(evt,wwid,conf)
{
    var cfg='',t,item;
    if(evt.target.alt!=null && evt.target.alt!=''){
        var o,c=1,dc=null;
        t=evt.target.alt.substring(2,evt.target.alt.length-1).replace("(,","(-,").replace(/\,\,/g,",-,").replace("("," ").replace(")"," )").replace(/\,/g," ").replace(/[\ ]+/g," ").split(' ');
        if(evt.target.alt.substr(2,1)=='='){t[0]='=';t[1]=evt.target.alt.substring(3,evt.target.alt.length-1).replace(/[\\ \\n\\t]/mg,'');}
        else if(t[0]==null)t[0]=evt.target.alt.substring(2,evt.target.alt.length-1);
        if((t[0]=='var'||t[0]=='field'||t[0]=='widget'||t[0]=='cms')&&t[1]) o=document.getElementById(wwid+':addons_'+t[1]);
        if(o==null || !o.getAttribute("data-conf")) {c=0;o=document.getElementById(wwid+':templater_'+(t[0]=="="?"eval":t[0]));}
        if(o!=null) {
            dc=o.getAttribute("data-conf");
            if(dc!=null && dc.substr(0,1)=='*') dc=dc.substr(1);
            cfg=(c?'addon ':'')+dc;
        }
    }
    if(!cfg){
        return "<small style='color:#808080;'>"+L('Not configurable')+'</small><?=(!PHPPE::lib("CMS")->expert?"<br><small>'+L(\"help_addon_\"+(t[0]==\"=\"?\"eval\":(t[0].substr(0,1)==\"/\"?t[0].substr(1):(t[0]==\"else\"?\"if\":t[0]))))+'</small>":"")?>';
    }
    cfg=cfg.replace(/[\(]/g,"( ").replace(/[\)]/g," )").replace(/[\,]/g," ").replace(/[\[]/g," [").replace(/[\[][\ \t]+/g,"[").replace(/[\]]/g,"").replace(/[\ \t]+/g," ");
    if(t[0]=='var'||t[0]=="field"||t[0]=="widget"||t[0]=="cms") item=t[1]; else item=t[0];
    if(item==null) item="";
    var r="<b>"+L(t[0]=="="?"Evaluate":'addon_'+(item!=''?item:t[0]))+"</b><table id='"+wwid+":cfgform'>";
    var i,j=0,idx=0,c=cfg.split(' ');
    cms_configure=evt.target;
    cms_conf=new Array(t[0]).concat(c);
    if(t[0]=="var"||t[0]=="field"||t[0]=="widget"||t[0]=="cms") j=0; else j=1;
    for(i=0;i<c.length;i++){
        var k,n=c[i].substr(0,1)=="["?c[i].substr(1):c[i];
        if(c[i]==")") {idx--;while(j>1&&t[j]!=')') j--; }
        r+="<tr><td"+(idx==1?" align='right'":"")+"<?=(!PHPPE::lib("CMS")->expert?" title='\"+htmlspecialchars(L(\"help_arg_\"+item))+\"'":"")?>>";
        if(n=="addon") {
            r+="<select onchange='cms_setattr(this,\""+wwid+"\");'>";
            r+="<option value='var'"+(t[0]=="var"?" selected":"")+">var</option>";
            r+="<option value='field'"+(t[0]=="field"?" selected":"")+">field</option>";
            r+="<option value='widget'"+(t[0]=="widget"?" selected":"")+">widget</option>";
            if(t[0]=="cms") r+="<option value='cms' selected>cms</option>";
            r+="</select>";
        } else
        if(n=="("||n==")")
            r+=(n=="("||idx>=0?n:'');
        else
            r+=L('arg_'+n);
        r+="</td><td>";
        if(c[i]!="("&&c[i]!=")") {
            if(n=="addon"){
                r+="<select onchange='cms_setattr(this,\""+wwid+"\");'>";
                for(k in cms_alladdon)
                    r+="<option value='"+htmlspecialchars(k)+"'"+(k==t[1]?" selected":"")+">"+htmlspecialchars(k)+"</option>";
                if(cms_alladdon[t[1]]==null)
                    r+="<option value='"+htmlspecialchars(t[1])+"' selected>"+htmlspecialchars(t[1])+"</option>";
                r+="</select>";
            } else
            if(n=="label"){
                r+="<select onchange='cms_setattr(this,\""+wwid+"\");'>";
                for(k in LANG) {
                    var txt=LANG[k]+"";
                    txt=txt.replace("\n","").substr(0,80);
                    r+="<option value='"+htmlspecialchars(k)+"'"+(k==t[j]?" selected":"")+">"+htmlspecialchars(k.substr(0,10)+': '+txt)+"</option>";
                }
                r+="</select>";
            } else
            if(n=="size"||n=="maxlen"||n=="min"||n=="max"||n=="width"||n=="height"||n=="before"||n=="after"||n=="rows"||n.substr(0,3)=="num"){
                r+="<input type='number' style='width:100px;text-align:right;' onchange='cms_setattr(this,\""+wwid+"\");' onkeyup='cms_setattr(this,\""+wwid+"\");' value='"+htmlspecialchars(t[j]!=null&&t[j]!="-"?t[j]:"")+"'>";
            } else
            if(n.substr(0,2)=="is"){
                r+="<input type='checkbox' value='1' onchange='cms_setattr(this,\""+wwid+"\");' "+(t[j]!=null&&t[j]=="1"?" checked":"")+"'>";
            } else
            if(n=="obj.field") {
                r+="<input type='text' onchange='cms_objfld(this,\""+wwid+"\");' onkeyup='cms_objfld(this,\""+wwid+"\");' value='"+htmlspecialchars(t[j]!=null&&t[j]!="-"?t[j]:"")+"'>";
            } else
                r+="<input type='text' onchange='cms_setattr(this,\""+wwid+"\");' onkeyup='cms_setattr(this,\""+wwid+"\");' value='"+htmlspecialchars(t[j]!=null&&t[j]!="-"?t[j]:"")+"'>";
        }
        r+="</td></tr>";
        if(c[i]=="(") idx++;
        j++;
    }
    if(item=="") item="noaddon";
    return r+'</table><?=(!PHPPE::lib("CMS")->expert?"<small>'+L(\"help_addon_\"+(item==\"=\"?\"eval\":(item.substr(0,1)==\"/\"?item.substr(1):item)))+'</small>":"")?>';
}
function cms_imghook(evt,wwid,conf)
{
    var t="<b>"+L("Image")+"</b><table>";
    t+="<tr><td>"+L("src")+":</td><td><input type='text' name='src' value='"+htmlspecialchars(evt.target.src)+"' onchange='wysiwyg_setattr(this);'></td></tr>";
    t+="<tr><td>"+L("zoom-large")+":</td><td><input type='text' name='data-zoom-large' value='"+htmlspecialchars(evt.target.getAttribute('data-zoom-large')!=null?evt.target.getAttribute('data-zoom-large'):'')+"' onchange='wysiwyg_setattr(this);'></td></tr>";
    t+="<tr><td>"+L("zoom-max")+":</td><td><input type='number' style='width:100px;text-align:right;' name='data-zoom-max' value='"+Math.floor(evt.target.getAttribute('data-zoom-max')!=null?evt.target.getAttribute('data-zoom-max'):'')+"' onchange='wysiwyg_setattr(this);'></td></tr>";
    t+="<tr><td>"+L("cssclass")+":</td><td><input type='text' name='class' value='"+htmlspecialchars(evt.target.className)+"' onchange='wysiwyg_setattr(this);'></td></tr>";
    return t;
}
function cms_linkhook(evt,wwid,conf)
{
    var t="<b>"+L("Hyperlink")+"</b><table>";
    t+="<tr><td>"+L("href")+":</td><td><input type='text' name='href' value='"+htmlspecialchars(evt.target.href)+"' onchange='wysiwyg_setattr(this);'></td></tr>";
    t+="<tr><td>"+L("target")+":</td><td><select name='target' onchange='wysiwyg_setattr(this);'><option value=''>"+L("same tab")+"</option><option value='_new'"+(evt.target.getAttribute('target')=="_new"?" selected":"")+">"+L("new tab")+"</option></select></td></tr>";
    t+="<tr><td>"+L("cssclass")+":</td><td><input type='text' name='class' value='"+htmlspecialchars(evt.target.className)+"' onchange='wysiwyg_setattr(this);'></td></tr>";
    return t;
}

var cms_divchooselast=null;
function cms_divchoosemove(evt)
{
    var el=document.elementFromPoint(evt.clientX,evt.clientY);
    if(cms_divchooselast!=el) {
        if(cms_divchooselast!=null)
            cms_divchooselast.setAttribute('style',cms_divchooselast.getAttribute('data-style')?cms_divchooselast.getAttribute('data-style'):'');
        if(el!=null) {
            el.setAttribute('data-style',el.getAttribute('style')?el.getAttribute('style'):'');
            el.setAttribute('style','background:#801010;color:#FF0000;');
        }
        cms_divchooselast=el;
    }
}
function cms_divchooseclick(evt)
{
    var el=document.elementFromPoint(evt.clientX,evt.clientY);
    document.getElementById('divchoose').style.display='none';
    document.getElementById('loading').style.display='block';
    document.location.href=document.location.href.split('?')[0]+'?chooseid='+el.getAttribute('data-chooseid');
}
function cms_divchooseselect(evt)
{
    var el=document.elementFromPoint(evt.clientX,evt.clientY);
    if(el==null) return;
    var t=el.innerHTML.replace(/^<br>/,'');
    document.getElementById('divchoose').style.display='none';
    document.getElementById('layoutdiv').style.display='block';
    document.getElementById('layout_data').value=t;
    document.getElementById('layout_data:frame').innerHTML=t;
    wysiwyg_togglesrc('layout_data');
    wysiwyg_togglesrc('layout_data');
}
