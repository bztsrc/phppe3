<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
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

//! fallback to english if a specific language translation not found
$d=array_unique(array_merge(
  glob("vendor/*/lang/".$_SESSION['pe_l'].".php"),
  glob("vendor/*/*/lang/".$_SESSION['pe_l'].".php"),
  glob("vendor/*/lang/en.php"),
  glob("vendor/*/*/lang/en.php")
  ));
//make sure it's not empty otherwise json_encode() will report JS syntax error
$lang = ["lang"=>$_SESSION['pe_l']];
foreach($d as $f) {$la=array(); $la=include($f);if( is_array( $la ) )$lang += $la; }
//force cache
PHPPE::$core->nocache = false;
header( "Pragma: cache" );
header( "Cache-Control: cache,public,max-age=86400" );
?>
/*
 *   PHP Portal Engine v3.0.0
 *   https://github.com/bztsrc/phppe3/
 *
 *   Copyright 2015 bzt, LGPLv3
 *
 *   *** CACHE ME! ***
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
function number_format(number, decimals, dec_point, thousands_sep) {
  //  discuss at: http://phpjs.org/functions/number_format/
  // original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: davook
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Theriault
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Michael White (http://getsprink.com)
  // bugfixed by: Benjamin Lupton
  // bugfixed by: Allan Jensen (http://www.winternet.no)
  // bugfixed by: Howard Yeend
  // bugfixed by: Diogo Resende
  // bugfixed by: Rival
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
  //  revised by: Luke Smith (http://lucassmith.name)
  //    input by: Kheang Hok Chin (http://www.distantia.ca/)
  //    input by: Jay Klehr
  //    input by: Amir Habibi (http://www.residence-mixte.com/)
  //    input by: Amirouche
  //   example 1: number_format(1234.56);
  //   returns 1: '1,235'
  //   example 2: number_format(1234.56, 2, ',', ' ');
  //   returns 2: '1 234,56'
  //   example 3: number_format(1234.5678, 2, '.', '');
  //   returns 3: '1234.57'
  //   example 4: number_format(67, 2, ',', '.');
  //   returns 4: '67,00'
  //   example 5: number_format(1000);
  //   returns 5: '1,000'
  //   example 6: number_format(67.311, 2);
  //   returns 6: '67.31'
  //   example 7: number_format(1000.55, 1);
  //   returns 7: '1,000.6'
  //   example 8: number_format(67000, 5, ',', '.');
  //   returns 8: '67.000,00000'
  //   example 9: number_format(0.9, 0);
  //   returns 9: '1'
  //  example 10: number_format('1.20', 2);
  //  returns 10: '1.20'
  //  example 11: number_format('1.20', 4);
  //  returns 11: '1.2000'
  //  example 12: number_format('1.2000', 3);
  //  returns 12: '1.200'
  //  example 13: number_format('1 000,50', 2, '.', ' ');
  //  returns 13: '100 050.00'
  //  example 14: number_format(1e-8, 8, '.', '');
  //  returns 14: '0.00000001'

  number = (number + '')
    .replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + (Math.round(n * k) / k)
        .toFixed(prec);
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
    .split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '')
    .length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1)
      .join('0');
  }
  return s.join(dec);
}

/*
 *   Multilanguage support
 */
var LANG=<?=json_encode($lang)?>;
function L(t){
if(t==null||t==undefined){
  var stack = new Error().stack;
  console.log( stack );
}
return LANG[t]!=null&&LANG[t]!=undefined?LANG[t]:(t!=null?t.replace(/_/g,' '):'');}

/*
 *   JS Cookie support
 */
function cookie_get(name) {var index=document.cookie.indexOf(name + "=");if(index==-1)return null;index=document.cookie.indexOf("=",index)+1;var endstr=document.cookie.indexOf(";",index);if(endstr==-1)endstr=document.cookie.length;return unescape(document.cookie.substring(index,endstr));}
function cookie_set(name,value,nDays,path) {var today=new Date();var expire=new Date();if(nDays==null||nDays<1)nDays=1;expire.setTime(today.getTime()+3600000*24*nDays);document.cookie=name+"="+escape(value)+";path="+(path!=null?path:"/")+";expires="+expire.toGMTString();}

/*
 *   Table of Contents
 */
function toc_init() {
	setTimeout('toc_initreal();',1);
}
function toc_initreal() {
	var k,tocs=document.getElementsByTagName("DIV");
	for(k=0;k<tocs.length;k++) {
	if(tocs[k].className.indexOf('toc')>-1) {
    var str="<span id='toc_i"+k+"' onclick='toc_switch("+k+");' style='cursor:pointer;'>▼</span><b>"+L("Table of Contents")+"<\/b><span id='toc_s"+k+"' onclick='toc_sticky("+k+");' style='cursor:pointer;'>◎</span><div id='toc_d"+k+"' style='display:block;'><br/>";
    var ok=0,i,j,subs;
    var cnt=new Array(0,0,0,0);
    var sections=tocs[k].parentNode.getElementsByTagName("*")||[];
    tocs[k].style.position='relative';
    tocs[k].style.float='right';
    tocs[k].style.display='inline';
    for(i=0;i<sections.length;i++) {
		var tag=sections[i].tagName+"";
		if(sections[i]==tocs[k]) { ok=1;continue; }
		var t=tag.match(/^[hH]([3-5])$/i);
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
	obj.style.top='32px';
	obj.style.right='3px';
	obj.style.boxShadow='3px 3px 8px #000';
	t.innerHTML='◉';
} else {
	obj.style.position='relative';
	obj.style.top='';
	obj.style.right='';
	obj.style.boxShadow='';
	t.innerHTML='◎';
}
}

/*
 *   PE Panel
 */
var pe_t=null,pe_c=null;
function pe_p(i) {
var o=document.getElementById('pe_'+i);if(pe_t!=null)clearTimeout(pe_t);
if(<?=empty(PHPPE::$core->noanim)?'false':'true'?> || typeof jQuery=='undefined'){if(pe_c&&pe_c!=i)document.getElementById('pe_'+pe_c).style.visibility='hidden';pe_t=pe_c=null;
if(o!=null){if(o.style.visibility=='visible')o.style.visibility='hidden';else{o.style.visibility='visible';pe_c=i;}}}
else{if(pe_c&&pe_c!=i)$('#pe_'+pe_c).fadeOut('slow');pe_t=pe_c=null;if(o!=null){var x=parseInt($('#pe_'+i).css('left'));if(o.getAttribute('data-x')==null){o.setAttribute('data-x',x);
o.style.visibility='visible';o.style.display='none';}$('#pe_'+i).css({left: o.getAttribute('data-x')+'px'});}$('#pe_'+i).fadeToggle();pe_c=i;}
pe_t=setTimeout(function(){pe_p('');},2000);return false;}
function pe_w() {if(pe_t!=null)clearTimeout(pe_t);pe_t=setTimeout(function(){pe_p('');},2000);return false;}

/*
 *   Pop-up calendar support
 */
/*PRIVATE VARS*/
var cal_div=null,cal_bg=null,cal_year=null,cal_month=null,cal_table=null,cal_firstday=0,cal_yesterday=null,cal_today=null,cal_tomorrow=null,cal_day=new Array(),cal_fo=null,cal_fi=null,cal_sel=null,cal_cellwidth=20,cal_format=null,cal_flipdayurl=null,cal_flipdays=new Array();

/*PUBLIC METHODS*/
//flipdayurl on http protocol must return text/plain,
//comma separated values of ISO 8601 dates of national holidays
//Eg: 2010-02-03,2013-10-25,2013-10-26
function cal_init(flipdayurl)
{
	cal_flipdayurl=flipdayurl;
	setTimeout('cal_initreal();',1);
}
function cal_initreal()
{
    if(typeof(LANG)!='undefined'){
	cal_format=LANG['dateformat']!=null?LANG['dateformat']:'Y-m-d';
	cal_firstday=parseInt(LANG['cal_firstday']!=null?LANG['cal_firstday']:1);
	cal_yesterday=LANG['yesterday']!=null?LANG['yesterday']:'Yesterday';
	cal_today=LANG['today']!=null?LANG['today']:'Today';
	cal_tomorrow=LANG['tomorrow']!=null?LANG['tomorrow']:'Tomorrow';
	cal_day=new Array(LANG['cal_day0']!=null?LANG['cal_day0']:'Su',
	    LANG['cal_day1']!=null?LANG['cal_day1']:'Mo',
	    LANG['cal_day1']!=null?LANG['cal_day2']:'Tu',
	    LANG['cal_day1']!=null?LANG['cal_day3']:'We',
	    LANG['cal_day1']!=null?LANG['cal_day4']:'Th',
	    LANG['cal_day1']!=null?LANG['cal_day5']:'Fr',
	    LANG['cal_day1']!=null?LANG['cal_day6']:'St');
    } else {
	cal_format='Y-m-d';
	cal_firstday=0;
	cal_yesterday='Yesterday';
	cal_today='Today';
	cal_tomorrow='Tomorrow';
	cal_day=new Array('Su','Mo','Tu','We','Th','Fr','St');
    }
    if(cal_flipdayurl!=null) {
	var http_request = new XMLHttpRequest();
	http_request.open('GET',cal_flipdayurl,false);
	http_request.send();
	if(http_request.status==200)
	    var i,d=http_request.responseText.explode(",");
    for(i=0;i<d.length;i++)
		cal_flipdays[d[i]]=true;
    }
}
function cal_open(obj,fo,fi)
{
	var x=0,y=0,w=(cal_cellwidth+5)*8+20,t;
	if(fo==null||fi==null||fo[fi+':y']==null) {
	    obj.style.display='none';
	    return;
	}
	if(cal_format==null) cal_init();
	if(cal_bg==null) {
	    cal_bg=document.createElement('div');
	    cal_bg.setAttribute('class','cal_bg');
	    cal_bg.setAttribute('style','position:fixed;top:0px;left:0px;width:100%;height:100%;z-index:0;display:none;');
	    cal_bg.setAttribute('onclick','cal_close(event);');
	    document.body.appendChild(cal_bg);
	}
	if(cal_div==null) {
	    var tbl=document.createElement('table'),tblbdy=document.createElement('tbody'),tbltr=document.createElement('tr');
	    var tbltdp=document.createElement('td');
	    var tblimgp=document.createElement('span');
	    var tbltdy=document.createElement('td');
	    var tbltdm=document.createElement('td');
	    var tbltdn=document.createElement('td');
	    var tblimgn=document.createElement('span');
	    var tb=document.createElement('table');
	    var f=cal_format.replace(/[^mMyY]+/g,"").toLowerCase();
	    cal_table=document.createElement('tbody');
	    tb.setAttribute('class','cal_table');
	    tb.appendChild(cal_table);
	    cal_div=document.createElement('div');
	    cal_div.setAttribute('class','cal_div');
	    cal_div.setAttribute('style','position:absolute;top:0px;left:0px;width:'+w+'px;z-index:0;display:none;');
	    cal_year=fo[fi+':y'].cloneNode(true);
	    cal_year.setAttribute('style','margin-right:4px;');
	    cal_year.setAttribute('onchange','cal_redraw();');
	    cal_month=fo[fi+':m'].cloneNode(true);
	    cal_month.setAttribute('onchange','cal_redraw();');
	    tbl.setAttribute('style','width:'+w+'px;');
	    tblimgp.innerHTML='◀';
	    tbltdp.setAttribute('onclick','cal_prev();');
	    tbltdp.setAttribute('style','cursor:pointer;text-align:center;font-weight:bold;font-size:'+Math.round(cal_cellwidth*0.6)+'px;');
	    tbltdp.setAttribute('width','50%');
	    tbltdp.appendChild(tblimgp);
	    tbltdy.appendChild(f=="my"?cal_month:cal_year);
	    tbltdm.appendChild(f=="my"?cal_year:cal_month);
	    tblimgn.innerHTML='▶';
	    tbltdn.setAttribute('onclick','cal_next();');
	    tbltdn.setAttribute('style','cursor:pointer;text-align:center;font-weight:bold;font-size:'+Math.round(cal_cellwidth*0.6)+'px;');
	    tbltdn.setAttribute('width','50%');
	    tbltdn.appendChild(tblimgn);
	    tbltr.appendChild(tbltdp);
	    tbltr.appendChild(tbltdy);
	    tbltr.appendChild(tbltdm);
	    tbltr.appendChild(tbltdn);
	    tblbdy.appendChild(tbltr);
	    tbl.appendChild(tblbdy);
	    cal_div.appendChild(tbl);
	    cal_div.appendChild(tb);
	    document.body.appendChild(cal_div);
	}
	cal_year.value=fo[fi+':y'].value;
	cal_month.value=fo[fi+':m'].value;
        cal_bg.style.zIndex=Math.round(obj.style.zIndex+1);
        cal_div.style.zIndex=Math.round(obj.style.zIndex+2);
        t=obj;
        while (t.offsetParent) {
            x += t.offsetLeft;
            y += t.offsetTop;
            t = t.offsetParent;
        }
        cal_bg.style.display='block';
        cal_div.style.display='block';
        cal_div.style.top=Math.round(y-7)+'px';
        cal_div.style.left=Math.round(x-w+36)+'px';
	cal_fo=fo;
	cal_fi=fi;
	cal_sel=new Date(fo[fi+':y'].value,Math.floor(fo[fi+':m'].value)-1,fo[fi+':d'].value,0,0,0);
	cal_sel=Math.ceil(cal_sel/86400000);
	cal_redraw();
}

/*PRIVATE METHODS*/
function cal_iso8601(d){var m=d.getUTCMonth()+1,n=d.getUTCDate();return d.getUTCFullYear()+'-'+(m<10?'0':'')+m+'-'+(n<10?'0':'')+n;}
function cal_close(evt){if(cal_bg!=null) cal_bg.style.display='none';if(cal_div!=null) cal_div.style.display='none';}
function cal_prev(){var d=new Date(Math.floor(cal_year.value),Math.floor(cal_month.value)-2);cal_year.value=d.getFullYear();cal_month.value=Math.floor(d.getMonth()+1);cal_redraw();}
function cal_next(){var d=new Date(Math.floor(cal_year.value),Math.floor(cal_month.value));cal_year.value=d.getFullYear();cal_month.value=Math.floor(d.getMonth()+1);cal_redraw();}
function cal_setdate(day){cal_fo[cal_fi+':y'].value=cal_year.value;cal_fo[cal_fi+':m'].value=cal_month.value;cal_fo[cal_fi+':d'].value=day;cal_close();}
function cal_settoday(){var t=new Date();cal_year.value=t.getFullYear();cal_month.value=Math.floor(t.getMonth()+1);cal_setdate(t.getDate());}
function cal_setyesterday(){var t=new Date();t.setDate(t.getDate() - 1);cal_year.value=t.getFullYear();cal_month.value=Math.floor(t.getMonth()+1);cal_setdate(t.getDate());}
function cal_settomorrow(){var t=new Date();t.setDate(t.getDate() + 1);cal_year.value=t.getFullYear();cal_month.value=Math.floor(t.getMonth()+1);cal_setdate(t.getDate());}
function cal_redraw()
{
    var tr=document.createElement('tr'),i,md,td,inl=0,w,today=new Date();
    if(cal_table==null) return;
    while(cal_table.firstChild!=null) cal_table.removeChild(cal_table.lastChild);
    tr.setAttribute('class','cal_headerbg');
    td=document.createElement('td');
    td.setAttribute('style','width:20px;');
    tr.appendChild(td);
    for(i=0;i<7;i++){
	var td=document.createElement('td');
	td.setAttribute('class','cal_header');
	td.setAttribute('style','text-align:center;width:20px;');
	td.innerHTML=cal_day[Math.floor((i+cal_firstday)%7)]+"";
	tr.appendChild(td);
    }
    cal_table.appendChild(tr); tr=document.createElement('tr');
    md=new Date(Math.floor(cal_year.value),Math.floor(cal_month.value)-1,1);
    w=new Date(Math.floor(cal_year.value),0,1);
    w=Math.ceil((((md-w)/86400000)+md.getDay()+1)/7);
    td=document.createElement('td');
    td.setAttribute('class','cal_week');
    td.setAttribute('style','text-align:right;');
    td.innerHTML=w+".";
    tr.appendChild(td);
    md=md.getDay();
    for(i=cal_firstday;i<7+cal_firstday&&md!=(i%7);i++) {
	var td=document.createElement('td');
	td.setAttribute('class','cal_empty');
	td.innerHTML=' ';
	tr.appendChild(td);
	inl++;
    }
    md=new Date(Math.floor(cal_year.value),Math.floor(cal_month.value),0).getDate();
    today.setHours(0);today.setMinutes(0);today.setSeconds(0);today.setMilliseconds(0);
    today=Math.ceil(today/86400000);
    for(i=1;i<=md;i++){
        var d=new Date(Math.floor(cal_year.value),Math.floor(cal_month.value)-1,i);
	var cur=cal_iso8601(d);
	var wd=d.getDay(),isw=(wd==0||wd==6);
	var td=document.createElement('td'),j=Math.floor((wd+cal_firstday)%7);

	td.setAttribute('class',(today==Math.ceil(d/86400000)?'cal_today ':'')+'cal_'+(cal_sel==Math.ceil(d/86400000)?'selected':((!cal_flipdays[cur]&&isw)||(cal_flipdays[cur]&&!isw)?'weekend':'workday')));
	td.setAttribute('style','text-align:center;cursor:pointer;');
	td.setAttribute('onclick','cal_setdate("'+i+'");');
	td.innerHTML=i;
	tr.appendChild(td);
	inl++; if(inl>=7) {
	    inl=0; w++;
	    cal_table.appendChild(tr);tr=document.createElement('tr');
	    td=document.createElement('td');
	    td.setAttribute('class','cal_week');
	    td.setAttribute('style','text-align:right;');
	    td.innerHTML=w+".";
	    tr.appendChild(td);
	}
    }
    if(inl!=0) {
	for(i=inl;i<7;i++) {
	    var td=document.createElement('td');
	    td.setAttribute('class','cal_empty');
	    td.innerHTML=' ';
	    tr.appendChild(td);
	}
	cal_table.appendChild(tr);
    }
    tr=document.createElement('tr');
    tr.setAttribute('class','cal_footer');
    td=document.createElement('td');
    td.setAttribute('style','text-align:center;cursor:pointer;');
    td.setAttribute('colspan','3');
    td.setAttribute('onclick','cal_setyesterday();');
    td.innerHTML='<small>'+cal_yesterday+'</small>';
    tr.appendChild(td);
    td=document.createElement('td');
    td.setAttribute('style','text-align:center;cursor:pointer;');
    td.setAttribute('colspan','2');
    td.setAttribute('onclick','cal_settoday();cal_redraw();');
    td.innerHTML='<small>'+cal_today+'</small>';
    tr.appendChild(td);
    td=document.createElement('td');
    td.setAttribute('style','text-align:center;cursor:pointer;');
    td.setAttribute('colspan','3');
    td.setAttribute('onclick','cal_settomorrow();');
    td.innerHTML='<small>'+cal_tomorrow+'</small>';
    tr.appendChild(td);
    cal_table.appendChild(tr);
}
/*
 *   Boolean field support
 *     name: name of the field
 *     value: true or false, current value
 *     type: string "boolean" or "notboolean"
 *     args: array of arguments [default value, yes label, no label]
 */
function boolean_init(){
	setTimeout('boolean_initreal();',1);
}
function boolean_initreal()
{
	var i,allinp=document.getElementsByTagName("input"),p=new DOMParser();
	for(i=0;i<allinp.length;i++) {
		var t=allinp[i].getAttribute('data-type');
		if(t!="boolean" && t!="notboolean") continue;
		var html=boolean_open(allinp[i].name,allinp[i].checked?true:false,t,allinp[i].getAttribute('data-args').split(','));
		var span=document.createElement('SPAN');
		span.innerHTML=html;
		allinp[i].parentNode.replaceChild(span,allinp[i]);
	}
}
function boolean_open(name,value,type,args)
{
	var t="";
//	if(value!=true&&value!="true"&&value!=1&&value!="1"&&value!=false&&value!="false"&&value!=0&&value!="0")
//		value=args[0];
	value=value==true||value=="true"||value==1||value=="1"?"true":"false";
	t+="<input type='hidden' name='"+name+"' value='"+value+"' data-args='"+args.join(",")+"'>";
	t+="<span onclick='boolean_"+(type=="notboolean"?"not":"")+"on(this);' ";
	t+="style='margin:2px 0px 2px 2px;color:#"+(type=="boolean"&&value=="true"||type=="notboolean"&&value!="true"?"c0ffc0":"000")+";border-color:#DADADA;";
	t+="border-top-left-radius:5px;border-bottom-left-radius:5px;box-shadow:0 1px 3px #999;text-shadow:0 -1px 1px #808080;";
	t+="background-color:#"+(type=="boolean"&&value=="true"||type=="notboolean"&&value!="true"?"00c000; background-image:linear-gradient(to top, #00FF00, #008000)":"F0F0F0; background-image:linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%)")+";";
	t+="cursor:pointer;padding:1px;'>&nbsp;&nbsp;"+L(args[1]!=null&&args[1]!=""?args[1]:"Yes")+"&nbsp;&nbsp;</span>";
	t+="<span onclick='boolean_"+(type=="notboolean"?"not":"")+"off(this);' ";
	t+="style='margin:2px 2px 2px 0px;color:#"+(type=="boolean"&&value!="true"||type=="notboolean"&&value=="true"?"ffc0c0":"000")+";border-color:#DADADA;";
	t+="border-top-right-radius:5px;border-bottom-right-radius:5px;box-shadow:0 1px 3px #999;text-shadow:0 -1px 1px #808080;";
	t+="background-color:#"+(type=="boolean"&&value!="true"||type=="notboolean"&&value=="true"?"c00000; background-image:linear-gradient(to top, #FF0000, #800000)":"F0F0F0; background-image:linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%)")+";";
	t+="cursor:pointer;padding:1px;'>&nbsp;&nbsp;"+L(args[2]!=null&&args[2]!=""?args[2]:"No")+"&nbsp;&nbsp;</span>";
	return t;
}
function boolean_on(o) {
	o.previousSibling.value='true';
	o.style.color='#c0ffc0';
	o.style.backgroundColor='#00c000';
	o.style.backgroundImage='linear-gradient(to top, #00FF00, #008000)';
	o.nextSibling.style.color='#000';
	o.nextSibling.style.backgroundColor='#F0F0F0';
	o.nextSibling.style.backgroundImage='linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%)';
}
function boolean_off(o) {
	o.previousSibling.previousSibling.value='false';
	o.style.color='#ffc0c0';
	o.style.backgroundColor='#c00000';
	o.style.backgroundImage='linear-gradient(to top, #FF0000, #800000)';
	o.previousSibling.style.color='#000';
	o.previousSibling.style.backgroundColor='#F0F0F0';
	o.previousSibling.style.backgroundImage='linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%)';
}
function boolean_noton(o) {
	boolean_on(o);
	o.previousSibling.value='false';
}
function boolean_notoff(o) {
	boolean_off(o);
	o.previousSibling.previousSibling.value='true';
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
function dnd_init(){document.body.onmousemove=dnd_display;document.body.onmouseup=new Function("dnd_dragged=null;dnd_drop();");dnd_icon=document.createElement('img'); dnd_icon.setAttribute('src','');dnd_icon.setAttribute('style','position:absolute;z-index:999;left:0px;top:0px;');dnd_icon.style.opacity=0.8;dnd_icon.style.display='none';document.body.appendChild(dnd_icon);

/*PRIVATE METHODS*/
function dnd_display(e){if(dnd_dragged!=null){if(window.Event){if(e.pageX){ml=e.pageX;mt=e.pageY;}}else{ml=(event.clientX + document.body.scrollLeft);mt=(event.clientY+document.body.scrollTop);}dnd_icon.style.display='block';dnd_icon.style.left=Math.floor(ml+10)+'px';dnd_icon.style.top=Math.floor(mt+10)+'px';}}}

/*
 *   Zooming images and boxes
 *     images: <img class='zoom' title='commentary' src='data/1.gif' [rel='galeryN'] [data-zoom='data/1_large.jpg'] [data-zoom-max=80]>
 *     non-images: <span id='thumbX' title='commentary' onclick='zoom_open(event,"largeX");'>click me</span>
 *                 <div id='largeX' class='fullscrdiv' style='display:none;' [rel='galeryN']>blah blah blah</div>
*/
/*PUBLIC VARS*/
var zoom_step=10;		//number of phases in zoom animation
var zoom_maxfade=0.4;	//maximum opacity for background fade

/*PRIVATE VARS*/
var zoom_numthumb=0,zoom_tags=new Array(),zoom_large=null,zoom_preload=new Array();
var zoom_previmg,zoom_nextimg,zoom_closeimg,zoom_largediv,zoom_largeimg,zoom_fadediv,zoom_titlediv,zoom_cacheimg,zoom_tmr;
var zoom_galery="",zoom_idx=-1,zoom_onthumb=-1,zoom_dofade=0,zoom_df=0.0,zoom_dohide=0;
var zoom_return,zoom_steps=new Array(),zoom_dosteps=-1,zoom_nodecor=0,zoom_cnt=1,zoom_noper=false;

/*PUBLIC METHODS*/
function zoom_init(){setTimeout('zoom_initreal();',1);}
function zoom_initreal(){var i;var x=((window.innerWidth?window.innerWidth:document.body.offsetWidth)-16)/2;var y=((window.innerHeight?window.innerHeight:document.body.offsetHeight)-16)/2;var tags=document.getElementsByTagName("*");for(i=0;i<tags.length;i++) {var add=0;if(tags[i].getAttribute('data-zoom')!=null){add=1;tags[i].setAttribute('onclick','zoom_open(event,\"'+tags[i].getAttribute("data-zoom")+'\");');if(document.getElementById(tags[i].getAttribute("data-zoom"))==null) {zoom_preload[zoom_numthumb]=new Image();zoom_preload[zoom_numthumb].src=tags[i].getAttribute('data-zoom');} else {tags[i].setAttribute('data-id',tags[i].getAttribute("data-zoom"));}}if(tags[i].getAttribute('data-zoom-max')!=null){add=1;tags[i].setAttribute('data-zoom-min',tags[i].offsetWidth);tags[i].setAttribute('onmouseover','zoom_over(this);');tags[i].setAttribute('onmouseout','zoom_over();');}if(add) {if(tags[i].id==null||tags[i].id==undefined||tags[i].id=='')tags[i].id='zoom_thumb'+i;zoom_tags[zoom_numthumb++]=tags[i];}}loadimg=document.createElement('img');loadimg.setAttribute('src','images/loading.gif');loadimg.setAttribute('style','position:fixed;left:'+Math.floor(x-16)+'px;top:'+Math.floor(y-16)+'px;z-index:1003;opacity:0.25;display:none;');document.body.appendChild(loadimg);zoom_previmg=document.createElement('img'); zoom_previmg.setAttribute('src','<?=file_exists("public/images/zoom/prev.png")?"images/zoom/prev.png":"data:image/png;base64,R0lGODlhEgASAOMIAAIFASwuK0ZIRWZoZYmLiK2vq8/Rzvn7+P///////////////////////////////yH5BAEKAAgALAAAAAASABIAAARmEMl5qj0zy2oKIYVRadtBBECaDmNWCWoMBCJ1wLJcI2Y+4CmBxYCKEQ7EWKFSiAWOvRgrmhJRVUIq9JbLNlXQpJQpoyGLqaMImKJ9VbVXTodELLlzs2FyP8mmGBocHiBWJDYXLRMRADs="?>'); zoom_previmg.setAttribute('alt','◀'); zoom_previmg.setAttribute('style','position:fixed;left:0px;top:0px;z-index:1002;display:none;margin-left:-8px;'); zoom_previmg.setAttribute('onclick','zoom_prevthumb();'); document.body.appendChild(zoom_previmg);zoom_nextimg=document.createElement('img'); zoom_nextimg.setAttribute('src','<?=file_exists("public/images/zoom/next.png")?"images/zoom/next.png":"data:image/png;base64,R0lGODlhEgASAOMIAAIFASwuK0ZIRWZoZYmLiK2vq8/Rzvn7+P///////////////////////////////yH5BAEKAAgALAAAAAASABIAAARmEEl5qj0zz2oKIYVRadQxACgaEGN2GEEqA0KLvHNeY5UgC6cZ61aQBUSE2bESTLEOhFiq0Ms9DbKnb7aDOplKkXdaSaaWsBkVJwtJUzWDCJv7teZvbqWwARuffBocHiBiJH0XNhIRADs="?>'); zoom_nextimg.setAttribute('alt','▶'); zoom_nextimg.setAttribute('style','position:fixed;left:0px;top:0px;z-index:1002;display:none;margin-left:-8px;'); zoom_nextimg.setAttribute('onclick','zoom_nextthumb();'); document.body.appendChild(zoom_nextimg);zoom_closeimg=document.createElement('img'); zoom_closeimg.setAttribute('src','<?=file_exists("public/images/zoom/close.png")?"images/zoom/close.png":"data:image/gif;base64,R0lGODlhEgASAOMIAAIFASwuK0ZIRWZoZYmLiK2vq8/Rzvn7+P///////////////////////////////yH5BAEKAAgALAAAAAASABIAAARzEMl5qj0zy2oKIYVRadtBBECaDmNWCWoMBCJ1wLJcI2YaDKgVTmAxBFlGAOGQBBQqBRWNueytKoTYFBsjWlXVWxcaCzODANYhmvKeRAYwUwwQGLKzjqr28uVScAhPdDlTBhODJzJqGBocHiAijSQ8FxYaEQA7"?>');zoom_closeimg.setAttribute('alt','X'); zoom_closeimg.setAttribute('style','position:fixed;left:0px;top:0px;z-index:1002;display:none;margin-left:-8px;margin-top:-8px;'); zoom_closeimg.setAttribute('onclick','zoom_hide();'); document.body.appendChild(zoom_closeimg);zoom_titlediv=document.createElement('div'); zoom_titlediv.setAttribute('style','position:fixed;top:0px;left:0px;background:#f0f0f0;text-align:center;padding-top:3px;padding-bottom:3px;z-index:1002;box-shadow:3px 3px 8px #000000;display:none;'); zoom_titlediv.setAttribute('onclick','doshow=-1;'); document.body.appendChild(zoom_titlediv);zoom_largeimg=document.createElement('img'); zoom_largeimg.setAttribute('alt',''); zoom_largeimg.setAttribute('style','position:fixed;left:0px;top:0px;z-index:1001;box-shadow:3px 3px 8px #000000;display:none;'); zoom_largeimg.setAttribute('src',''); zoom_largeimg.setAttribute('onclick','zoom_hide();'); document.body.appendChild(zoom_largeimg);zoom_fadediv=document.createElement('div'); zoom_fadediv.setAttribute('style','position:fixed;top:0px;left:0px;width:100%;height:100%;z-index:1000;background:#000000;display:none;opacity:0.0;'); zoom_fadediv.setAttribute('onclick','zoom_hide();'); document.body.appendChild(zoom_fadediv);zoom_largediv=document.createElement('div'); zoom_largediv.setAttribute('style','position:fixed;overflow:hidden;top:0px;left:0px;width:0px;height:0px;z-index:1001;background:#FFF;box-shadow:3px 3px 8px #000000;display:none;'); document.body.appendChild(zoom_largediv);zoom_cacheimg=new Image();zoom_cacheprocessed=false;zoom_tmr=setInterval("zoom_animate();",50);}
function zoom_open(evt,large,norecalc,noper){
if(noper==true) zoom_noper=true;
var obj=evt.target!=null?evt.target:evt;var i,ww=(window.innerWidth?window.innerWidth:document.body.offsetWidth);var wh=(window.innerHeight?window.innerHeight:document.body.offsetHeight);var p,obj2=typeof large != 'string'?large:document.getElementById(large);
if(zoom_fadediv==null)zoom_initreal();
zoom_large=obj2;p=zoom_getpos(obj);
if(obj2==null && large!=null) {zoom_large=zoom_largeimg;zoom_large.src=obj.src!=null?obj.src:'images/loading.gif';if(obj.src!=null&&loadimg)loadimg.style.display='block';zoom_loadlarge(large);} else if(obj2==null) {
zoom_large=zoom_largediv;zoom_large.className=obj2.className;zoom_large.style=obj2.style;zoom_large.innerHTML=obj2.innerHTML;}if(zoom_large==null) return;
if(zoom_large.id==null||zoom_large.id==undefined||zoom_large.id=='')zoom_large.id='zoom_large'+zoom_cnt++;if(obj.getAttribute('data-id')==null)obj.setAttribute('data-id',zoom_large.id);
if(zoom_return==null) {zoom_return=p;zoom_galery=obj.getAttribute("rel")?obj.getAttribute("rel"):"";zoom_nodecor=zoom_large.getAttribute("data-zoom-nodecor")||obj.getAttribute("data-zoom-nodecor")?true:false;}if(zoom_idx==-1){zoom_idx=-1;for(i=0;i<zoom_tags.length;i++) {if(zoom_tags[i].id==obj.id||zoom_tags[i].getAttribute('data-id')==obj.id) { zoom_idx=i; break; }}}if(zoom_fadediv.style.display!='block'){zoom_fadediv.style.display='block';<?php if(empty(PHPPE::$core->noanim)) { ?>zoom_df=0.0;zoom_dofade=1;<?php } ?>}zoom_fadediv.style.opacity=zoom_maxfade;
var op=zoom_large.style.opacity;
zoom_large.setAttribute('class','modal');
zoom_large.style.opacity=op;
var nt=zoom_large.getAttribute('title')!=null?zoom_large.getAttribute('title'):obj.getAttribute('title');
zoom_titlediv.innerHTML=(nt!=null)?nt:(zoom_tags[zoom_idx]!=null&&zoom_tags[zoom_idx].getAttribute('title')!=null?zoom_tags[zoom_idx].getAttribute('title'):'');
if(norecalc==null || zoom_steps[zoom_step]==null) {zoom_steps[0]=p;
nw=zoom_large.getAttribute('data-zoom-w')!=null?zoom_large.getAttribute('data-zoom-w'):(obj.getAttribute('data-zoom-w')!=null?obj.getAttribute('data-zoom-w'):Math.floor(ww*0.6));
if(typeof nw=='string' && nw.indexOf('%')>-1) nw=Math.round(ww*Math.round(nw.replace('%',''))/100)-1;
nh=zoom_large.getAttribute('data-zoom-h')!=null?zoom_large.getAttribute('data-zoom-h'):(obj.getAttribute('data-zoom-h')!=null?obj.getAttribute('data-zoom-h'):Math.floor(wh*0.6));
if(typeof nh=='string' && nh.indexOf('%')>-1) nh=Math.round(wh*Math.round(nh.replace('%',''))/100)-1;
if(nt&&!zoom_nodecor){nh-=24;nw*=nh/(nh+24);}
nx=zoom_large.getAttribute('data-zoom-x')!=null?zoom_large.getAttribute('data-zoom-x'):(obj.getAttribute('data-zoom-x')!=null?obj.getAttribute('data-zoom-x'):Math.floor((ww-nw)/2));
ny=zoom_large.getAttribute('data-zoom-y')!=null?zoom_large.getAttribute('data-zoom-y'):(obj.getAttribute('data-zoom-y')!=null?obj.getAttribute('data-zoom-y'):Math.floor((wh-nh)/2));} else {
nw=zoom_steps[zoom_step].w;nh=zoom_steps[zoom_step].h;nx=zoom_steps[zoom_step].x;ny=zoom_steps[zoom_step].y;}
zoom_large.setAttribute('style','display:block;z-index:1001;border:0px;visibility:visible;position:fixed;left:'+zoom_steps[0].x+'px;top:'+zoom_steps[0].y+'px;width:'+zoom_steps[0].w+'px;height:'+zoom_steps[0].h+'px;');
zoom_setfullscreen(nx,ny,nw,nh,true);}

/*PRIVATE METHODS*/
function zoom_opendecor(){

var x=zoom_large.offsetLeft;var y=zoom_large.offsetTop;var w=zoom_large.offsetWidth;var h=zoom_large.offsetHeight;
var ww=(window.innerWidth?window.innerWidth:document.body.offsetWidth);var wh=(window.innerHeight?window.innerHeight:document.body.offsetHeight);
if(w>ww){ow=w;w=ww;h*=w/ow;}if(h>wh){ow=h;h=wh;w*=h/ow;}
zoom_large.style.width=(w*100/ww)+'%';
zoom_large.style.height=(h*100/wh)+'%';
if(!zoom_noper){
  zoom_large.style.left=((ww-w)/2*100/ww)+'%';
  zoom_large.style.top=((wh-h)/2*100/wh)+'%';
}
if(zoom_dohide){zoom_dohide=0;zoom_hidedecor();return;}
if(zoom_nodecor)return;
var dd=y+h;if(dd>wh-24)dd=wh-24;
zoom_titlediv.style.top=(dd*100/wh)+'%';zoom_titlediv.style.left=((ww-w)/2*100/ww)+'%';zoom_titlediv.style.width=(w*100/ww)+'%';zoom_closeimg.style.display='block';
zoom_closeimg.style.top=(y*100/wh)+'%';zoom_closeimg.style.left=((x+Math.floor(w))*100/ww)+'%';
if(zoom_titlediv.innerHTML!='')zoom_titlediv.style.display='block';
if(zoom_galery!=''){zoom_previmg.style.display='block';
zoom_previmg.style.top=((y+h/2)*100/wh)+'%';
zoom_previmg.style.left=((ww-w)/2*100/ww)+'%';
zoom_nextimg.style.display='block';
zoom_nextimg.style.top=((y+h/2)*100/wh)+'%';
zoom_nextimg.style.left=((ww+w)/2*100/ww)+'%';
}}
function zoom_hidedecor(){if(loadimg)loadimg.style.display='none';zoom_titlediv.style.display='none';zoom_previmg.style.display='none';zoom_nextimg.style.display='none';zoom_closeimg.style.display='none';}
function zoom_calczoom_steps(x,y,w,h){var i=0,dx=0.0,dy=0.0,dw=0.0,dh=0.0,zoom_cx=0.0,zoom_cy=0.0,cw=0,ch=0,dzoom_cx=0.0,dzoom_cy=0.0,dcw=0.0,dch=0.0,hs=Math.floor(zoom_step/2);dx=(x-zoom_steps[0].x);zoom_cx=dzoom_cx=dx/2;dy=(y-zoom_steps[0].y); zoom_cy=dzoom_cy=dy/2;dw=(w-zoom_steps[0].w); cw=dcw=dw/2;dh=(h-zoom_steps[0].h); ch=dch=dh/2;zoom_steps[hs]={ 'x':Math.round(zoom_steps[0].x+zoom_cx), 'y':Math.round(zoom_steps[0].y+zoom_cy), 'w':Math.round(zoom_steps[0].w+cw), 'h':Math.round(zoom_steps[0].h+ch) };for(i=1;i<hs;i++){dzoom_cx/=2;	zoom_cx+=dzoom_cx;dzoom_cy/=2;	zoom_cy+=dzoom_cy;dcw/=2;	cw+=dcw;dch/=2;	ch+=dch;zoom_steps[hs-i]={ 'x':Math.round(zoom_steps[hs].x+dx/2-zoom_cx), 'y':Math.round(zoom_steps[hs].y+dy/2-zoom_cy), 'w':Math.round(zoom_steps[hs].w+dw/2-cw), 'h':Math.round(zoom_steps[hs].h+dh/2-ch) };zoom_steps[hs+i]={ 'x':Math.round(zoom_steps[0].x+zoom_cx), 'y':Math.round(zoom_steps[0].y+zoom_cy), 'w':Math.round(zoom_steps[0].w+cw), 'h':Math.round(zoom_steps[0].h+ch) };}zoom_steps[zoom_step]={ 'x':x, 'y':y, 'w':w, 'h':h };if(((dx>0?dx:-dx)>3) || ((dy>0?dy:-dy)>3)) { if(zoom_titlediv)zoom_hidedecor(); zoom_dosteps=1; }else zoom_dosteps=zoom_step-1;}
function zoom_getpos(obj){var o=obj,container=obj;var zoom_cx=0;var zoom_cy=0;while(container!=null){if(container.scrollTop||container.scrollLeft){zoom_cx-=Math.round(container.scrollLeft);zoom_cy-=Math.round(container.scrollTop);}container=container.parentNode;}while(obj.offsetParent!=null){if(obj==document.body)break;zoom_cx+=Math.floor(obj.offsetLeft);zoom_cy+=Math.floor(obj.offsetTop);obj=obj.offsetParent;}return {x:zoom_cx,y:zoom_cy,w:o.offsetWidth,h:o.offsetHeight,obj:o};}
function zoom_hideall(){zoom_hidedecor();zoom_large.setAttribute('style','display:none;');zoom_fadediv.style.display='none';zoom_fadediv.style.opacity=0;zoom_return=null;zoom_idx=-1;}
function zoom_hide(){zoom_hidedecor();<?php if(empty(PHPPE::$core->noanim)) { ?>zoom_dofade=-1; zoom_df=zoom_maxfade;if(typeof jQuery=='undefined'){zoom_steps[0]=zoom_steps[zoom_step];zoom_calczoom_steps(zoom_return.x,zoom_return.y,zoom_return.w,zoom_return.h);zoom_dosteps=1;} else {$('#'+zoom_large.id).animate({left: zoom_return.x+'px',top: zoom_return.y+'px',width: zoom_return.w+'px',height: zoom_return.h+'px'},50*zoom_step);}<?php } else { ?>zoom_hideall();<?php } ?>}
function zoom_nextthumb() {if(zoom_idx<0||zoom_galery=='') return zoom_hide();do{ zoom_idx++; if(zoom_idx>=zoom_numthumb) zoom_idx=0; } while(zoom_tags[zoom_idx].getAttribute('rel')!=zoom_galery);var old=zoom_large;zoom_steps[0]=zoom_steps[zoom_step];zoom_open(zoom_large,zoom_tags[zoom_idx].getAttribute('data-zoom'),true);if(old!=zoom_large) old.style.display='none';}
function zoom_prevthumb() {if(zoom_idx<0||zoom_galery=='') return zoom_hide();do{ zoom_idx--; if(zoom_idx<0) zoom_idx=zoom_numthumb-1; } while(zoom_tags[zoom_idx].getAttribute('rel')!=zoom_galery);var old=zoom_large;zoom_steps[0]=zoom_steps[zoom_step];zoom_open(zoom_large,zoom_tags[zoom_idx].getAttribute('data-zoom'),true);if(old!=zoom_large) old.style.display='none';}
function zoom_setfullscreen(x,y,w,h,k){
var r=0,ww=(window.innerWidth?window.innerWidth:document.body.offsetWidth);var wh=(window.innerHeight?window.innerHeight:document.body.offsetHeight);//-(zoom_titlediv.innerHTML!=''&&!zoom_nodecor?24:0);
if(w>ww){ow=w;w=ww;if(!k)h*=w/ow;r=1;}if(h>wh){ow=h;h=wh;if(!k)w*=h/ow;r=1;}if(!k&&(r||x<0||y<0)){x=(ww-w)/2;y=(wh-h)/2;}
zoom_steps[zoom_step]={x:x,y:y,w:w,h:h};zoom_hidedecor();<?php if(empty(PHPPE::$core->noanim)) { ?>if(typeof jQuery=='undefined'){zoom_calczoom_steps(x,y,w,h);if(w!=zoom_large.offsetWidth==w&&zoom_large.offsetHeight==h)zoom_dostep=zoom_step-1; else zoom_dostep=0;zoom_animate();} else
{$('#'+zoom_large.id).animate({left: x+'px',top: y+'px',width: w+'px',height: h+'px'},(zoom_large.offsetWidth==w&&zoom_large.offsetHeight==h?1:50*zoom_step),zoom_opendecor);}<?php } else { ?>zoom_large.setAttribute('style','border:0px;left:'+x+'px;top:'+y+'px;width:'+w+'px;height:'+h+'px;');zoom_opendecor();<?php } ?>}
function zoom_loadlarge(fn){zoom_cacheimg.complete=false; zoom_cacheimg.width=0;zoom_largeimg.width=0;zoom_cacheimg.src=fn;zoom_cacheprocessed=false;if(loadimg)loadimg.style.display='block';}

function zoom_cacheready(){if(loadimg)loadimg.style.display='none';zoom_largeimg.style.display='block';zoom_cacheprocessed=true;
var w=zoom_cacheimg.naturalWidth>0?zoom_cacheimg.naturalWidth:zoom_cacheimg.width;var h=zoom_cacheimg.naturalHeight>0?zoom_cacheimg.naturalHeight:zoom_cacheimg.height;
var ow=zoom_large.width;zoom_large.src=zoom_cacheimg.src;zoom_largeimg.width=ow;zoom_dohide=1;zoom_setfullscreen(-1,-1,w,h);}function zoom_over(obj) {zoom_onthumb=obj;}

function zoom_animate(){<?php if(empty(PHPPE::$core->noanim)) { ?>var i;for(i=0;i<zoom_numthumb;i++){var minw=zoom_tags[i].getAttribute('data-zoom-min');
var maxw=zoom_tags[i].getAttribute('data-zoom-max');var w;if(maxw==null) continue;if(zoom_tags[i]==zoom_onthumb)w=Math.round(zoom_tags[i].offsetWidth+5>maxw?maxw:zoom_tags[i].offsetWidth+5);else w=Math.round(zoom_tags[i].offsetWidth-5<minw?minw:zoom_tags[i].offsetWidth-5);if(zoom_tags[i].offsetWidth!=w)zoom_tags[i].style.width=w+'px';}
if(zoom_dosteps!=-1){zoom_large.setAttribute('style','display:block;border:0px;z-index:1001;visibility:visible;position:fixed;left:'+zoom_steps[zoom_dosteps].x+'px;top:'+
zoom_steps[zoom_dosteps].y+'px;width:'+zoom_steps[zoom_dosteps].w+'px;height:'+zoom_steps[zoom_dosteps].h+'px;');zoom_dosteps++;if(zoom_dosteps>zoom_step) zoom_dosteps=-1;}if(zoom_dofade==1){zoom_df+=(zoom_maxfade/zoom_step);if(zoom_df>zoom_maxfade) { zoom_df=zoom_maxfade; zoom_dofade=0; }zoom_fadediv.style.opacity=zoom_df;}if(zoom_dofade==-1){zoom_df-=(zoom_maxfade/zoom_step);if(zoom_df<=(zoom_maxfade/zoom_step)) {zoom_df=0.0; zoom_dofade=0;zoom_hideall();}zoom_fadediv.style.opacity=zoom_df;}<?php }else{ ?>zoom_opendecor();<?php } ?>
if(!zoom_cacheprocessed&&zoom_cacheimg.complete&&(zoom_cacheimg.naturalWidth>0||zoom_cacheimg.width>0)) zoom_cacheready();}
