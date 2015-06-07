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
 * @file vendor/phppe/core/js/core.js.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief PHPPE Core JavaScript support
 */
use PHPPE\Core as PHPPE;

//! fallback to english if a specific language translation not found for a module
$d=array_unique(array_merge(
  glob("vendor/*/lang/".$_SESSION['pe_l'].".php"),
  glob("vendor/*/*/lang/".$_SESSION['pe_l'].".php"),
  glob("vendor/*/lang/en.php"),
  glob("vendor/*/*/lang/en.php")
  ));
//make sure it's not empty otherwise json_encode() generates JS syntax error
$lang = ["lang"=>$_SESSION['pe_l']];
foreach($d as $f) {$la=array(); $la=include($f);if( is_array( $la ) )$lang += $la; }
PHPPE::$core->nocache = false;
header( "Cache-Control: cache,public,max-age=86400" );
?>
/*
 *   PHP Portal Engine v3.0.0
 *   https://github.com/bztphp/phppe3/
 *
 *   Copyright 2014 bzt, LGPLv3
 *
 *   *** MERGED INTO SINGLE FILE, CACHE ME! ***
 */

/*
 *   Escape attributes
 */
function htmlspecialchars(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text==null||text==''?'':text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/*
 *   Multilanguage support
 */
var LANG=<?=json_encode($lang)?>;
function L(t){return LANG[t]!=null && LANG[t]!=undefined ? LANG[t] : t.replace(/_/g,' ');}

/*
 *   JS Cookie support
 */
function cookie_get(name) {var index=document.cookie.indexOf(name + "=");if(index==-1)return null;index=document.cookie.indexOf("=",index)+1;var endstr=document.cookie.indexOf(";",index);if(endstr==-1)endstr=document.cookie.length;return unescape(document.cookie.substring(index,endstr));}
function cookie_set(name,value,nDays,path) {var today=new Date();var expire=new Date();if(nDays==null||nDays<1)nDays=1;expire.setTime(today.getTime()+3600000*24*nDays);document.cookie=name+"="+escape(value)+";path="+(path!=null?path:"/")+";expires="+expire.toGMTString();}

/*
 *   Table of Contents
 */
function toc_init() {
	var k,tocs=document.getElementsByTagName("DIV");
	for(k=0;k<tocs.length;k++) {
	if(tocs[k].className.indexOf('toc')>-1) {
    var str="<span id='toc_i"+k+"' onclick='toc_switch("+k+");' style='cursor:pointer;'>▼</span><b>"+L("Table of Contents")+"<\/b><span id='toc_s"+k+"' onclick='toc_sticky("+k+");' style='cursor:pointer;'>◎</span><div id='toc_d"+k+"' style='display:block;'><br/>";
    var ok=0,i,j,subs;
    var cnt=new Array(0,0,0,0);
    var sections=tocs[k].parentNode.getElementsByTagName("*")||[];
    tocs[k].style.position='relative';
    tocs[k].style.display='inline';
    for(i=0;i<sections.length;i++) {
		var tag=sections[i].tagName+"";
		if(sections[i]==tocs[k]) { ok=1;continue; }
		var t=tag.match(/^h([3-5])$/i);
		if(!t || !ok) continue;
		cnt[t[1]-2]++;
		if(t[1]=='4') cnt[3]=0;
		if(t[1]=='3') cnt[3]=cnt[2]=0;
		if(t[1]=='2') cnt[3]=cnt[2]=cnt[1]=0;
		var x=0,y=0,a=sections[i];
	    if(a.offsetParent) {
		do {
		    x += a.offsetLeft;
		    y += a.offsetTop;
		} while (a = a.offsetParent);
	    }
		str+='<div onclick="window.scrollTo('+x+','+y+');" style="padding-left:'+(t[1]-2)+'0px;cursor:pointer;">';
		str+=(cnt[0]?cnt[0]+'.':'')+(cnt[1]?cnt[1]+'.':'')+(cnt[2]?cnt[2]+'.':'')+(cnt[3]?cnt[3]+'.':'');
		str+='&nbsp;&nbsp;'+sections[i].innerHTML+'<\/div>';
    }
    tocs[k].innerHTML=str+"<\/div>";
	}
	}
}
function toc_switch(i) {var obj=document.getElementById('toc_d'+i),icn=document.getElementById('toc_i'+i);
if(obj.style.display=='block'){ obj.style.display='none'; icn.innerHTML='▶'; }else { obj.style.display='block'; icn.innerHTML='▼'; }}
function toc_sticky(i) {var t=document.getElementById('toc_s'+i),obj=t.parentNode;
if(obj.style.position!='fixed'){
	obj.style.position='fixed';
	obj.style.right='3px';
	obj.style.boxShadow='3px 3px 8px #000';
	t.innerHTML='◉';
} else {
	obj.style.position='relative';
	obj.style.right='';
	obj.style.boxShadow='';
	t.innerHTML='◎';
}
}

/*
 *   PE Panel
 */
var pe_t=null,pe_c=null;
function pe_p(i) {var o=document.getElementById('pe_'+i);if(pe_t!=null)clearTimeout(pe_t);if(typeof jQuery=='undefined'){if(pe_c&&pe_c!=i)document.getElementById('pe_'+pe_c).style.visibility='hidden';pe_t=pe_c=null;if(o!=null){if(o.style.visibility=='visible')o.style.visibility='hidden';else{o.style.visibility='visible';pe_c=i;}}}else{if(pe_c&&pe_c!=i)$('#pe_'+pe_c).fadeOut('slow');pe_t=pe_c=null;if(o!=null){var x=parseInt($('#pe_'+i).css('left'));if(o.getAttribute('data-x')==null){o.setAttribute('data-x',x);o.style.visibility='visible';o.style.display='none';}$('#pe_'+i).css({left: o.getAttribute('data-x')+'px'});}$('#pe_'+i).fadeToggle();pe_c=i;}pe_t=setTimeout(function(){pe_p('');},2000);return false;}
function pe_w() {if(pe_t!=null)clearTimeout(pe_t);pe_t=setTimeout(function(){pe_p('');},2000);return false;}

/*
 *   Pop-up calendar support
 */
/*PRIVATE VARS*/
var calendar_div=null,calendar_bg=null,calendar_year=null,calendar_month=null,calendar_table=null,calendar_firstday=0,calendar_yesterday=null,calendar_today=null,calendar_tomorrow=null,calendar_day=new Array(),calendar_fo=null,calendar_fi=null,calendar_sel=null,calendar_cellwidth=20,calendar_format=null,calendar_flipdays=new Array();

/*PUBLIC METHODS*/
//flipdayurl on http protocol must return text/plain,
//comma separated values of ISO 8601 dates of national holidays
//Eg: 2010-02-03,2013-10-25,2013-10-26
function calendar_init(flipdayurl)
{
    if(typeof(LANG)!='undefined'){
	calendar_format=LANG['dateformat']!=null?LANG['dateformat']:'Y-m-d';
	calendar_firstday=parseInt(LANG['calendar_firstday']!=null?LANG['calendar_firstday']:1);
	calendar_yesterday=LANG['yesterday']!=null?LANG['yesterday']:'Yesterday';
	calendar_today=LANG['today']!=null?LANG['today']:'Today';
	calendar_tomorrow=LANG['tomorrow']!=null?LANG['tomorrow']:'Tomorrow';
	calendar_day=new Array(LANG['calendar_day0']!=null?LANG['calendar_day0']:'Su',
	    LANG['calendar_day1']!=null?LANG['calendar_day1']:'Mo',
	    LANG['calendar_day1']!=null?LANG['calendar_day2']:'Tu',
	    LANG['calendar_day1']!=null?LANG['calendar_day3']:'We',
	    LANG['calendar_day1']!=null?LANG['calendar_day4']:'Th',
	    LANG['calendar_day1']!=null?LANG['calendar_day5']:'Fr',
	    LANG['calendar_day1']!=null?LANG['calendar_day6']:'St');
    } else {
	calendar_format='Y-m-d';
	calendar_firstday=0;
	calendar_yesterday='Yesterday';
	calendar_today='Today';
	calendar_tomorrow='Tomorrow';
	calendar_day=new Array('Su','Mo','Tu','We','Th','Fr','St');
    }
    if(flipdayurl!=null) {
	calendar_flipdayurl=flipdayurl;
	var http_request = new XMLHttpRequest();
	http_request.open('GET',flipdayurl,false);
	http_request.send();
	if(http_request.status==200)
	    var i,d=http_request.responseText.explode(",");
	    for(i=0;i<d.length;i++)
		calendar_flipdays[d[i]]=true;
    }
}
function calendar_open(obj,fo,fi)
{
	var x=0,y=0,w=(calendar_cellwidth+5)*8+16,t;
	if(fo==null||fi==null||fo[fi+':y']==null) {
	    obj.style.display='none';
	    return;
	}
	if(calendar_format==null) calendar_init();
	if(calendar_bg==null) {
	    calendar_bg=document.createElement('div');
	    calendar_bg.setAttribute('class','calendar_bg');
	    calendar_bg.setAttribute('style','position:fixed;top:0px;left:0px;width:100%;height:100%;z-index:0;display:none;');
	    calendar_bg.setAttribute('onclick','calendar_close(event);');
	    document.body.appendChild(calendar_bg);
	}
	if(calendar_div==null) {
	    var tbl=document.createElement('table'),tblbdy=document.createElement('tbody'),tbltr=document.createElement('tr');
	    var tbltdp=document.createElement('td');
	    var tblimgp=document.createElement('span');
	    var tbltdy=document.createElement('td');
	    var tbltdm=document.createElement('td');
	    var tbltdn=document.createElement('td');
	    var tblimgn=document.createElement('span');
	    var tb=document.createElement('table');
	    var f=calendar_format.replace(/[^mMyY]+/g,"").toLowerCase();
	    calendar_table=document.createElement('tbody');
	    tb.setAttribute('class','calendar_table');
	    tb.appendChild(calendar_table);
	    calendar_div=document.createElement('div');
	    calendar_div.setAttribute('class','calendar_div');
	    calendar_div.setAttribute('style','position:absolute;top:0px;left:0px;width:'+w+'px;z-index:0;display:none;');
	    calendar_year=fo[fi+':y'].cloneNode(true);
	    calendar_year.setAttribute('style','margin-right:4px;');
	    calendar_year.setAttribute('onchange','calendar_redraw();');
	    calendar_month=fo[fi+':m'].cloneNode(true);
	    calendar_month.setAttribute('onchange','calendar_redraw();');
	    tbl.setAttribute('style','width:'+w+'px;');
	    tblimgp.innerHTML='◀';
	    tbltdp.setAttribute('onclick','calendar_prev();');
	    tbltdp.setAttribute('style','cursor:pointer;text-align:center;font-weight:bold;font-size:'+Math.round(calendar_cellwidth*0.6)+'px;');
	    tbltdp.setAttribute('width','50%');
	    tbltdp.appendChild(tblimgp);
	    tbltdy.appendChild(f=="my"?calendar_month:calendar_year);
	    tbltdm.appendChild(f=="my"?calendar_year:calendar_month);
	    tblimgn.innerHTML='▶';
	    tbltdn.setAttribute('onclick','calendar_next();');
	    tbltdn.setAttribute('style','cursor:pointer;text-align:center;font-weight:bold;font-size:'+Math.round(calendar_cellwidth*0.6)+'px;');
	    tbltdn.setAttribute('width','50%');
	    tbltdn.appendChild(tblimgn);
	    tbltr.appendChild(tbltdp);
	    tbltr.appendChild(tbltdy);
	    tbltr.appendChild(tbltdm);
	    tbltr.appendChild(tbltdn);
	    tblbdy.appendChild(tbltr);
	    tbl.appendChild(tblbdy);
	    calendar_div.appendChild(tbl);
	    calendar_div.appendChild(tb);
	    document.body.appendChild(calendar_div);
	}
	calendar_year.value=fo[fi+':y'].value;
	calendar_month.value=fo[fi+':m'].value;
        calendar_bg.style.zIndex=Math.round(obj.style.zIndex+1);
        calendar_div.style.zIndex=Math.round(obj.style.zIndex+2);
        t=obj;
        while (t.offsetParent) {
            x += t.offsetLeft;
            y += t.offsetTop;
            t = t.offsetParent;
        }
        calendar_bg.style.display='block';
        calendar_div.style.display='block';
        calendar_div.style.top=Math.round(y-7)+'px';
        calendar_div.style.left=Math.round(x-w+36)+'px';
	calendar_fo=fo;
	calendar_fi=fi;
	calendar_sel=new Date(fo[fi+':y'].value,Math.floor(fo[fi+':m'].value)-1,fo[fi+':d'].value,0,0,0);
	calendar_sel=Math.ceil(calendar_sel/86400000);
	calendar_redraw();
}

/*PRIVATE METHODS*/
function calendar_iso8601(d){var m=d.getUTCMonth()+1,n=d.getUTCDate();return d.getUTCFullYear()+'-'+(m<10?'0':'')+m+'-'+(n<10?'0':'')+n;}
function calendar_close(evt){if(calendar_bg!=null) calendar_bg.style.display='none';if(calendar_div!=null) calendar_div.style.display='none';}
function calendar_prev(){var d=new Date(Math.floor(calendar_year.value),Math.floor(calendar_month.value)-2);calendar_year.value=d.getFullYear();calendar_month.value=Math.floor(d.getMonth()+1);calendar_redraw();}
function calendar_next(){var d=new Date(Math.floor(calendar_year.value),Math.floor(calendar_month.value));calendar_year.value=d.getFullYear();calendar_month.value=Math.floor(d.getMonth()+1);calendar_redraw();}
function calendar_setdate(day){calendar_fo[calendar_fi+':y'].value=calendar_year.value;calendar_fo[calendar_fi+':m'].value=calendar_month.value;calendar_fo[calendar_fi+':d'].value=day;calendar_close();}
function calendar_settoday(){var t=new Date();calendar_year.value=t.getFullYear();calendar_month.value=Math.floor(t.getMonth()+1);calendar_setdate(t.getDate());}
function calendar_setyesterday(){var t=new Date();t.setDate(t.getDate() - 1);calendar_year.value=t.getFullYear();calendar_month.value=Math.floor(t.getMonth()+1);calendar_setdate(t.getDate());}
function calendar_settomorrow(){var t=new Date();t.setDate(t.getDate() + 1);calendar_year.value=t.getFullYear();calendar_month.value=Math.floor(t.getMonth()+1);calendar_setdate(t.getDate());}
function calendar_redraw()
{
    var tr=document.createElement('tr'),i,md,td,inl=0,w,today=new Date();
    if(calendar_table==null) return;
    while(calendar_table.firstChild!=null) calendar_table.removeChild(calendar_table.lastChild);
    tr.setAttribute('class','calendar_headerbg');
    td=document.createElement('td');
    td.setAttribute('style','width:20px;');
    tr.appendChild(td);
    for(i=0;i<7;i++){
	var td=document.createElement('td');
	td.setAttribute('class','calendar_header');
	td.setAttribute('style','text-align:center;width:20px;');
	td.innerHTML=calendar_day[Math.floor((i+calendar_firstday)%7)]+"";
	tr.appendChild(td);
    }
    calendar_table.appendChild(tr); tr=document.createElement('tr');
    md=new Date(Math.floor(calendar_year.value),Math.floor(calendar_month.value)-1,1);
    w=new Date(Math.floor(calendar_year.value),0,1);
    w=Math.ceil((((md-w)/86400000)+md.getDay()+1)/7);
    td=document.createElement('td');
    td.setAttribute('class','calendar_week');
    td.setAttribute('style','text-align:right;');
    td.innerHTML=w+".";
    tr.appendChild(td);
    md=md.getDay();
    for(i=calendar_firstday;i<7+calendar_firstday&&md!=(i%7);i++) {
	var td=document.createElement('td');
	td.setAttribute('class','calendar_empty');
	td.innerHTML=' ';
	tr.appendChild(td);
	inl++;
    }
    md=new Date(Math.floor(calendar_year.value),Math.floor(calendar_month.value),0).getDate();
    today.setHours(0);today.setMinutes(0);today.setSeconds(0);today.setMilliseconds(0);
    today=Math.ceil(today/86400000);
    for(i=1;i<=md;i++){
        var d=new Date(Math.floor(calendar_year.value),Math.floor(calendar_month.value)-1,i);
	var cur=calendar_iso8601(d);
	var wd=d.getDay(),isw=(wd==0||wd==6);
	var td=document.createElement('td'),j=Math.floor((wd+calendar_firstday)%7);

	td.setAttribute('class',(today==Math.ceil(d/86400000)?'calendar_today ':'')+'calendar_'+(calendar_sel==Math.ceil(d/86400000)?'selected':((!calendar_flipdays[cur]&&isw)||(calendar_flipdays[cur]&&!isw)?'weekend':'workday')));
	td.setAttribute('style','text-align:center;cursor:pointer;');
	td.setAttribute('onclick','calendar_setdate("'+i+'");');
	td.innerHTML=i;
	tr.appendChild(td);
	inl++; if(inl>=7) {
	    inl=0; w++;
	    calendar_table.appendChild(tr);tr=document.createElement('tr');
	    td=document.createElement('td');
	    td.setAttribute('class','calendar_week');
	    td.setAttribute('style','text-align:right;');
	    td.innerHTML=w+".";
	    tr.appendChild(td);
	}
    }
    if(inl!=0) {
	for(i=inl;i<7;i++) {
	    var td=document.createElement('td');
	    td.setAttribute('class','calendar_empty');
	    td.innerHTML=' ';
	    tr.appendChild(td);
	}
	calendar_table.appendChild(tr);
    }
    tr=document.createElement('tr');
    tr.setAttribute('class','calendar_footer');
    td=document.createElement('td');
    td.setAttribute('style','text-align:center;cursor:pointer;');
    td.setAttribute('colspan','3');
    td.setAttribute('onclick','calendar_setyesterday();');
    td.innerHTML='<small>'+calendar_yesterday+'</small>';
    tr.appendChild(td);
    td=document.createElement('td');
    td.setAttribute('style','text-align:center;cursor:pointer;');
    td.setAttribute('colspan','2');
    td.setAttribute('onclick','calendar_settoday();calendar_redraw();');
    td.innerHTML='<small>'+calendar_today+'</small>';
    tr.appendChild(td);
    td=document.createElement('td');
    td.setAttribute('style','text-align:center;cursor:pointer;');
    td.setAttribute('colspan','3');
    td.setAttribute('onclick','calendar_settomorrow();');
    td.innerHTML='<small>'+calendar_tomorrow+'</small>';
    tr.appendChild(td);
    calendar_table.appendChild(tr);
}

/*
 *   Popup menu support
 *    popups: <div id='mypopup'>menu</div>
 *    triggers: onmouseover='popup_open(this,"mypopup",10,10);'
 */
/*PUBLIC*/
var popup_timeout=1000;

/*PRIVATE*/
var popup_tmr=null;
var popup_currentobj;
var popup_current='';
var popup_dontclose=false;

/*PUBLIC METHODS*/
function popup_open(triggerobj,id,deltax,deltay)
{
	var obj,oldobj=triggerobj,i,t;
        var curleft = 0;
        var curtop = 0;
	if(popup_tmr!=null) clearTimeout(popup_tmr);
	if(popup_current!='') obj=document.getElementById(popup_current);
	if(obj!=null) obj.style.display='none';
	obj=document.getElementById(id);
	if(obj!=null) {
		obj.style.zIndex=Math.round(triggerobj.style.zIndex+10);
		popup_currentobj=t=triggerobj;
		obj.style.display='block';
		triggerobj=oldobj;
		if(deltay!=null) {
			while (t.offsetParent) {
				curleft += t.offsetLeft;
				curtop += t.offsetTop;
				t = t.offsetParent;
   	    	}
			obj.style.position='absolute';
			obj.style.top=Math.round(curtop+deltay>0?curtop+deltay:0)+'px';
			obj.style.left=Math.round(curleft+deltax>0?curleft+deltax:0)+'px';
		}
		obj.onmouseover=popup_over;
		obj.onmouseout=popup_out;
		oldobj.onmouseout=popup_out;
		popup_current=id;
		popup_dontclose=true;
		popup_tmr=setTimeout('popup_close();',popup_timeout);
	}
}

/*PRIVATE METHODS*/
function popup_over(){popup_dontclose=true;};
function popup_out(){popup_dontclose=false;if(popup_tmr==null)popup_tmr=setTimeout('popup_close();',popup_timeout);};
function popup_close(){var obj,i;if(popup_dontclose) popup_tmr=setTimeout('popup_close();',popup_timeout);else {obj=document.getElementById(popup_current);if(obj!=null)obj.style.display='none';popup_current=0;popup_currentobj=null;popup_tmr=null;}}

/*
 *   JS Drag'n'drop support (legacy HTML4)
 *     Dragable items: onselectstart='return dnd_drag(<what>,<icon>[,size]);'
 *     Drop areas: onmouseup='return dnd_drop(event,myfunc[,context]);' and implement myfunc(what,context,event){} callback
 */
/*PRIVATE VARS*/
var dnd_dragged=null,dnd_icon=null,dnd_start;

/*PUBLIC METHODS*/
function dnd_drag(id,icon,size){var ti=new Date();if(dnd_icon==null)dnd_init();dnd_dragged=id;if(icon.tagName=='IMG'){dnd_icon.src=icon.src;if(size!=null)dnd_icon.width=size;}dnd_start=ti.getTime();return false;}
function dnd_drop(evt,callback,ctx){if(!evt)evt=window.event;var ti=new Date();if(dnd_icon!=null) dnd_icon.style.display='none';if(dnd_dragged!=null&&callback!=null&&ti.getTime()-dnd_start>200)callback(dnd_dragged,ctx,evt);dnd_dragged=null;return false;}
function dnd_init(){document.body.onmousemove=dnd_display;document.body.onmouseup=new Function("dnd_dragged=null;dnd_drop();");dnd_icon=document.createElement('img'); dnd_icon.setAttribute('src','');dnd_icon.setAttribute('style','position:absolute;z-index:1000;left:0px;top:0px;');dnd_icon.style.opacity=0.8;dnd_icon.style.display='none';document.body.appendChild(dnd_icon);

/*PRIVATE METHODS*/
function dnd_display(e){if(dnd_dragged!=null){if(window.Event){if(e.pageX){ml=e.pageX;mt=e.pageY;}}else{ml=(event.clientX + document.body.scrollLeft);mt=(event.clientY+document.body.scrollTop);}dnd_icon.style.display='block';dnd_icon.style.left=Math.floor(ml+10)+'px';dnd_icon.style.top=Math.floor(mt+10)+'px';}}}

/*
 *   Zooming images and boxes
 *     images: <img class='zoom' title='commentary' src='data/1.gif' [rel='galery1'] [data-zoom-large='data/1_large.jpg'] [data-zoom-max=80]>
 *     non-images: <span id='thumbX' title='commentary' onclick='zoom_open("X");'>click me</span>
 *                 <div id='largeX' class='fullscrdiv' style='display:none;'>blah blah blah</div>
*/
/*PUBLIC VARS*/
var zoom_divw=640;		//div fullscreen window's width
var zoom_divh=480;		//div height
var zoom_animmaxw=0;		//thumbnail zoom effect, maximum thumbnail width (will not grow above)
var zoom_animminw=0;		//minimum thumbnail width (will not shrink below)
var zoom_step=16;		//number of phases in fullscreen zoom animation
var zoom_maxfade=0.625;	//maximum opacity for background fade
var zoom_thumbext=/^(.*)[gp][in][fg]$/;	//suffix of thumbnails...
var zoom_largeext="$2jpg";	//...replaced by this gives higher resolution images
				//only used if no rel attribute given on trigger element
/*PRIVATE VARS*/
var zoom_numthumb=0,zoom_previmg,zoom_nextimg,zoom_closeimg,zoom_largediv,zoom_largeimg,zoom_imgdiv,zoom_fadediv,zoom_titlediv,zoom_cacheimg,zoom_cacheprocessed,zoom_pw,zoom_ph,zoom_tmr;
var zoom_idx=0,zoom_onthumb=-1,zoom_dofade=0,zoom_dohide=0,zoom_df=0.0;
var zoom_cx=0.0,zoom_cy=0.0,zoom_steps=new Array(),zoom_imgs=new Array(),zoom_dosteps=-1;

/*PUBLIC METHODS*/
function zoom_init(minw,maxw){zoom_animminw=((minw!=null&&minw>=0)?minw:60);zoom_animmaxw=((maxw!=null&&maxw>=0)?maxw:80);var i,preload=new Array();var x=((window.innerWidth?window.innerWidth:document.body.offsetWidth)-16)/2;var y=((window.innerHeight?window.innerHeight:document.body.offsetHeight)-16)/2;var allimg=document.getElementsByTagName("img");for(i=0;i<allimg.length;i++) {var s=allimg[i].id,m=s.match(/^thumb(.*)$/);if(m!=null&&m[1]!=null){zoom_imgs[zoom_numthumb]=allimg[i]; allimg[i].setAttribute('onclick','zoom_open(\"'+m[1]+'\");'); allimg[i].setAttribute('onmouseover','zoom_over(\"'+m[1]+'\");'); allimg[i].setAttribute('onmouseout','zoom_over(-1);');preload[zoom_numthumb]=new Image(); preload[zoom_numthumb].src=zoom_imgs[zoom_numthumb].getAttribute("rel")?zoom_imgs[zoom_numthumb].getAttribute("rel"):zoom_imgs[zoom_numthumb].src.replace(zoom_thumbext,zoom_largeext); zoom_numthumb++; }}loadimg=document.createElement('img'); loadimg.setAttribute('src','images/loading.gif'); loadimg.setAttribute('style','position:fixed;left:'+Math.floor(x-16)+'px;top:'+Math.floor(y-16)+'px;z-index:99;opacity:0.25;display:none;'); document.body.appendChild(loadimg);zoom_previmg=document.createElement('img'); zoom_previmg.setAttribute('src','<?=file_exists("public/images/zoom/prev.png")?"images/zoom/prev.png":"data:image/png;base64,R0lGODlhEgASAOMIAAIFASwuK0ZIRWZoZYmLiK2vq8/Rzvn7+P///////////////////////////////yH5BAEKAAgALAAAAAASABIAAARmEMl5qj0zy2oKIYVRadtBBECaDmNWCWoMBCJ1wLJcI2Y+4CmBxYCKEQ7EWKFSiAWOvRgrmhJRVUIq9JbLNlXQpJQpoyGLqaMImKJ9VbVXTodELLlzs2FyP8mmGBocHiBWJDYXLRMRADs="?>'); zoom_previmg.setAttribute('alt','◀'); zoom_previmg.setAttribute('style','position:fixed;left:0px;top:0px;z-index:98;display:none;'); zoom_previmg.setAttribute('onclick','zoom_prevthumb();'); document.body.appendChild(zoom_previmg);zoom_nextimg=document.createElement('img'); zoom_nextimg.setAttribute('src','<?=file_exists("public/images/zoom/next.png")?"images/zoom/next.png":"data:image/png;base64,R0lGODlhEgASAOMIAAIFASwuK0ZIRWZoZYmLiK2vq8/Rzvn7+P///////////////////////////////yH5BAEKAAgALAAAAAASABIAAARmEEl5qj0zz2oKIYVRadQxACgaEGN2GEEqA0KLvHNeY5UgC6cZ61aQBUSE2bESTLEOhFiq0Ms9DbKnb7aDOplKkXdaSaaWsBkVJwtJUzWDCJv7teZvbqWwARuffBocHiBiJH0XNhIRADs="?>'); zoom_nextimg.setAttribute('alt','▶'); zoom_nextimg.setAttribute('style','position:fixed;left:0px;top:0px;z-index:98;display:none;'); zoom_nextimg.setAttribute('onclick','zoom_nextthumb();'); document.body.appendChild(zoom_nextimg);zoom_closeimg=document.createElement('img'); zoom_closeimg.setAttribute('src','<?=file_exists("public/images/zoom/close.png")?"images/zoom/close.png":"data:image/gif;base64,R0lGODlhEgASAOMIAAIFASwuK0ZIRWZoZYmLiK2vq8/Rzvn7+P///////////////////////////////yH5BAEKAAgALAAAAAASABIAAARzEMl5qj0zy2oKIYVRadtBBECaDmNWCWoMBCJ1wLJcI2YaDKgVTmAxBFlGAOGQBBQqBRWNueytKoTYFBsjWlXVWxcaCzODANYhmvKeRAYwUwwQGLKzjqr28uVScAhPdDlTBhODJzJqGBocHiAijSQ8FxYaEQA7"?>');zoom_closeimg.setAttribute('alt','X'); zoom_closeimg.setAttribute('style','position:fixed;left:0px;top:0px;z-index:98;display:none;'); zoom_closeimg.setAttribute('onclick','zoom_hide();'); document.body.appendChild(zoom_closeimg);zoom_largeimg=document.createElement('img'); zoom_largeimg.setAttribute('alt',''); zoom_largeimg.setAttribute('style','position:fixed;left:0px;top:0px;z-index:97;box-shadow:3px 3px 8px #000000;display:none;'); zoom_largeimg.setAttribute('src',''); zoom_largeimg.setAttribute('onclick','zoom_hide();'); document.body.appendChild(zoom_largeimg);zoom_largediv=document.createElement('div'); zoom_largediv.setAttribute('style','position:fixed;overflow:hidden;top:0px;left:0px;width:0px;height:0px;z-index:97;background:#FFF;box-shadow:3px 3px 8px #000000;display:none;'); document.body.appendChild(zoom_largediv);zoom_fadediv=document.createElement('div'); zoom_fadediv.setAttribute('style','position:fixed;top:0px;left:0px;width:100%;height:100%;z-index:96;background:#000000;display:none;opacity:0.0;'); zoom_fadediv.setAttribute('onclick','zoom_hide();'); document.body.appendChild(zoom_fadediv);zoom_titlediv=document.createElement('div'); zoom_titlediv.setAttribute('style','position:fixed;top:0px;left:0px;background:#f0f0f0;text-align:center;padding-top:3px;padding-bottom:3px;z-index:97;box-shadow:3px 3px 8px #000000;display:none;'); zoom_titlediv.setAttribute('onclick','doshow=-1;'); document.body.appendChild(zoom_titlediv);zoom_cacheimg=new Image();zoom_cacheprocessed=false;zoom_tmr=setTimeout("zoom_animate();",175);}
function zoom_open(id,pw,ph){var obj=document.getElementById('thumb'+id),obj2=document.getElementById('large'+id);if(obj==null)alert('thumb'+id);if(zoom_largediv==null)zoom_init();zoom_pw=pw?pw:0;zoom_ph=ph?ph:0;zoom_idx=id;if(obj.tagName=="IMG") {zoom_imgdiv=0;zoom_largeimg.src=obj.src;zoom_largeimg.width=obj.width;zoom_largeimg.height=obj.height;}else{zoom_imgdiv=1;zoom_largediv.className=obj2.className;zoom_largediv.innerHTML=obj2.innerHTML;if(obj2.style.width){zoom_divw=parseInt(obj2.style.width);zoom_divh=parseInt(obj2.style.height);}zoom_largediv.style.width=Math.floor(obj.style.width)+'px';zoom_largediv.style.height=Math.floor(obj.style.height)+'px';zoom_titlediv.innerHTML=obj.title;}zoom_getpos(obj);zoom_steps[0]={ 'x':zoom_cx, 'y':zoom_cy, 'w':(zoom_imgdiv?32:zoom_largeimg.width), 'h':(zoom_imgdiv?32:zoom_largeimg.height) };zoom_movelarge(zoom_cx,zoom_cy);zoom_loadlarge(obj);zoom_fadediv.style.display='block';zoom_dofade=1; zoom_df=0.0; zoom_dohide=0;minstep=1;}

/*PRIVATE METHODS*/
function zoom_opendecor(){var x=zoom_steps[zoom_step].x;var y=zoom_steps[zoom_step].y;var w=zoom_pw>0?zoom_pw:(zoom_imgdiv?zoom_divw:zoom_largeimg.width);var h=zoom_ph>0?zoom_ph:(zoom_imgdiv?zoom_divh:zoom_largeimg.height);var ww=(window.innerWidth?window.innerWidth:document.body.offsetWidth);var wh=(window.innerHeight?window.innerHeight:document.body.offsetHeight);if(w>ww-64){ow=w;w=ww-64;h*=w/ow;}if(h>wh-64){ow=h;h=wh-64;w*=h/ow;}zoom_titlediv.style.top=Math.floor(y+h)+'px';zoom_titlediv.style.left=Math.floor(x)+'px';zoom_titlediv.style.width=Math.floor(w)+'px';if(zoom_titlediv.innerHTML!='')zoom_titlediv.style.display='block';if(zoom_imgdiv==0){zoom_previmg.style.display='block';zoom_previmg.style.top=Math.floor(y+Math.floor(h/2)-Math.floor(zoom_previmg.height/2))+'px';zoom_previmg.style.left=Math.floor(x-Math.floor(3*zoom_previmg.width/4))+'px';zoom_nextimg.style.display='block';zoom_nextimg.style.top=Math.floor(y+Math.floor(h/2)-Math.floor(zoom_previmg.height/2))+'px';zoom_nextimg.style.left=Math.floor(x+Math.floor(w)-Math.floor(zoom_nextimg.width/4))+'px';}zoom_closeimg.style.display='block';zoom_closeimg.style.top=Math.floor(y-Math.floor(zoom_closeimg.height/2))+'px';zoom_closeimg.style.left=Math.floor(x+Math.floor(w)-Math.floor(zoom_closeimg.width/2))+'px';}
function zoom_hidedecor(){loadimg.style.display='none';zoom_titlediv.style.display='none';zoom_previmg.style.display='none';zoom_nextimg.style.display='none';zoom_closeimg.style.display='none';if(zoom_imgdiv) zoom_largediv.style.overflow='hidden';}
function zoom_calczoom_steps(x,y,w,h){var i=0,dx=0.0,dy=0.0,dw=0.0,dh=0.0,zoom_cx=0.0,zoom_cy=0.0,cw=0,ch=0,dzoom_cx=0.0,dzoom_cy=0.0,dcw=0.0,dch=0.0,hs=Math.floor(zoom_step/2);dx=(x-zoom_steps[0].x); zoom_cx=dzoom_cx=dx/2;dy=(y-zoom_steps[0].y); zoom_cy=dzoom_cy=dy/2;dw=(w-zoom_steps[0].w); cw=dcw=dw/2;dh=(h-zoom_steps[0].h); ch=dch=dh/2;zoom_steps[hs]={ 'x':(zoom_steps[0].x+zoom_cx), 'y':(zoom_steps[0].y+zoom_cy), 'w':(zoom_steps[0].w+cw), 'h':(zoom_steps[0].h+ch) };for(i=1;i<hs;i++){dzoom_cx/=2;	zoom_cx+=dzoom_cx;dzoom_cy/=2;	zoom_cy+=dzoom_cy;dcw/=2;	cw+=dcw;dch/=2;	ch+=dch;zoom_steps[hs-i]={ 'x':(zoom_steps[hs].x+dx/2-zoom_cx), 'y':(zoom_steps[hs].y+dy/2-zoom_cy), 'w':(zoom_steps[hs].w+dw/2-cw), 'h':(zoom_steps[hs].h+dh/2-ch) };zoom_steps[hs+i]={ 'x':(zoom_steps[0].x+zoom_cx), 'y':(zoom_steps[0].y+zoom_cy), 'w':(zoom_steps[0].w+cw), 'h':(zoom_steps[0].h+ch) };}zoom_steps[zoom_step]={ 'x':x, 'y':y, 'w':w, 'h':h };if(((dx>0?dx:-dx)>3) || ((dy>0?dy:-dy)>3)) { zoom_hidedecor(); zoom_dosteps=1; }else zoom_dosteps=zoom_step-1;}
function zoom_movelarge(x,y){(zoom_imgdiv?zoom_largediv:zoom_largeimg).style.left=Math.floor(x)+'px';(zoom_imgdiv?zoom_largediv:zoom_largeimg).style.top=Math.floor(y)+'px';}
function zoom_resizelarge(w,h){(zoom_imgdiv?zoom_largediv:zoom_largeimg).style.width=Math.floor(w)+'px';(zoom_imgdiv?zoom_largediv:zoom_largeimg).style.height=Math.floor(h)+'px';}
function zoom_getpos(obj){var container=obj;zoom_cx=0;zoom_cy=0;while(container!=null){if(container.scrollTop||container.scrollLeft){zoom_cx-=Math.round(container.scrollLeft);zoom_cy-=Math.round(container.scrollTop);}container=container.parentNode;}while(obj.offsetParent!=null){if(obj==document.body)break;zoom_cx+=Math.floor(obj.offsetLeft);zoom_cy+=Math.floor(obj.offsetTop);obj=obj.offsetParent;}}
function zoom_hide(){if(zoom_dohide)return;var obj=document.getElementById('thumb'+zoom_idx);zoom_hidedecor();zoom_getpos(obj);minstep=1; zoom_calczoom_steps(zoom_cx,zoom_cy,zoom_imgdiv?Math.floor(obj.style.width):obj.width,zoom_imgdiv?Math.floor(obj.style.height):obj.height); zoom_dosteps=2;zoom_dofade=-1; zoom_df=zoom_maxfade; zoom_dohide=1;}
function zoom_nextthumb() {zoom_idx++; if(zoom_idx>=zoom_numthumb) zoom_idx=0;minstep=Math.floor(zoom_step/2);zoom_loadlarge(zoom_imgs[zoom_idx]);}
function zoom_prevthumb() {zoom_idx--; if(zoom_idx<0) zoom_idx=Math.floor(zoom_numthumb-1);minstep=Math.floor(zoom_step/2);zoom_loadlarge(zoom_imgs[zoom_idx]);}
function zoom_getfullscreenmax(w,h){var x=0.0,y=0.0;var ww=(window.innerWidth?window.innerWidth:document.body.offsetWidth);var wh=(window.innerHeight?window.innerHeight:document.body.offsetHeight);if(w>ww-64){ow=w;w=ww-64;h*=w/ow;}if(h>wh-64){ow=h;h=wh-64;w*=h/ow;}x=(ww-w)/2;y=(wh-h)/2;zoom_calczoom_steps(x,y,w,h);}
function zoom_loadlarge(obj){if(zoom_imgdiv==0){var fn=obj.getAttribute("rel")?obj.getAttribute("rel"):obj.src.replace(zoom_thumbext,zoom_largeext);zoom_cacheimg.complete=false; zoom_cacheimg.width=0;zoom_cacheimg.src=fn;zoom_cacheprocessed=false;loadimg.style.display='block';}else{zoom_cacheprocessed=true;zoom_getfullscreenmax(zoom_pw>0?zoom_pw:zoom_divw,zoom_ph>0?zoom_ph:zoom_divh);zoom_largediv.style.display='block';}zoom_titlediv.innerHTML=obj.title;}
function zoom_cacheready(){loadimg.style.display='none';zoom_largeimg.style.display='block';zoom_cacheprocessed=true;var w=zoom_cacheimg.naturalWidth>0?zoom_cacheimg.naturalWidth:zoom_cacheimg.width;var h=zoom_cacheimg.naturalHeight>0?zoom_cacheimg.naturalHeight:zoom_cacheimg.height;	var ow=zoom_largeimg.width;zoom_largeimg.src=zoom_cacheimg.src;zoom_largeimg.width=ow;zoom_getfullscreenmax(w,h);}
function zoom_over(id) {zoom_onthumb=id;}
function zoom_animate(){var i;for(i=0;i<zoom_numthumb;i++){if(i==zoom_onthumb){if(zoom_animmaxw>0&&zoom_imgs[i].width<zoom_animmaxw) zoom_imgs[i].width+=5;} else {if(zoom_animminw>0&&zoom_imgs[i].width>zoom_animminw) zoom_imgs[i].width-=5;}}if(zoom_dosteps!=-1){zoom_movelarge(zoom_steps[zoom_dosteps].x,zoom_steps[zoom_dosteps].y);zoom_resizelarge(zoom_steps[zoom_dosteps].w,zoom_steps[zoom_dosteps].h);zoom_dosteps++; if(zoom_dosteps>zoom_step) { zoom_dosteps=-1; if(!zoom_dohide) zoom_opendecor(); zoom_steps[0]=zoom_steps[zoom_step]; zoom_dohide=0; }}if(zoom_dofade==1){zoom_df+=(zoom_maxfade/zoom_step);if(zoom_df>zoom_maxfade) { zoom_df=zoom_maxfade; zoom_dofade=0; }zoom_fadediv.style.opacity=zoom_df;}if(zoom_dofade==-1){zoom_df-=(zoom_maxfade/zoom_step);if(zoom_df<=(zoom_maxfade/zoom_step)) {zoom_df=0.0; zoom_dofade=0;zoom_fadediv.style.display='none';zoom_largeimg.style.display='none';zoom_largediv.style.display='none';}zoom_fadediv.style.opacity=zoom_df;}if(!zoom_cacheprocessed&&zoom_cacheimg.complete&&(zoom_cacheimg.naturalWidth>0||zoom_cacheimg.width>0)) zoom_cacheready();zoom_tmr=setTimeout("zoom_animate();",75);}
