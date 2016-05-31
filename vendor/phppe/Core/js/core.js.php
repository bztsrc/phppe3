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
 * @file vendor/phppe/Core/js/core.js.php
 * @author bzt
 * @date 1 Jan 2016
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
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016 bzt
 *
 *   *** CACHE ME! ***
 */

/*
 *   PHP compatibility
 */
function htmlspecialchars(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text==null||text==''?'':text.replace(/[&<>\"\']/g, function(m) { return map[m]; });
}
function urlencode(text) {
  return encodeURIComponent(text);
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

function getpos(obj){var o=obj.getBoundingClientRect();return {x:o.left,y:o.top,w:obj.offsetWidth,h:obj.offsetHeight,obj:obj};}


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
 *   PE Panel
 */
var pe_t=null,pe_c=null,pe_to=2;
function pe_p(i,trg,to) {
    var evt = window.event || (arguments.callee.caller!=null && arguments.callee.caller.arguments[0]);
    var o=i!=null&&i!=''?document.getElementById(i):null;
    if(trg==null && evt && evt.target) trg=evt.target;
    if(trg && o) {
        var rt = trg.getBoundingClientRect();
        o.style.left=rt.left+'px';
        o.style.top=(rt.top+evt.target.offsetHeight)+'px';
    }
    if(pe_t!=null)clearTimeout(pe_t);
    if(<?=empty(PHPPE::$core->noanim)?'false':'true'?> || typeof jQuery=='undefined'){
        if(pe_c&&pe_c!=i)document.getElementById(pe_c).style.visibility='hidden';
        pe_t=pe_c=null;
        if(o){
            if(o.style.visibility=='visible')o.style.visibility='hidden';
            else{o.style.visibility='visible';pe_c=i;}
        }
    }else{
        if(pe_c&&pe_c!=i)$('#'+pe_c).fadeOut('slow');
        pe_t=pe_c=null;
        if(o){
            if(o.getAttribute('data-x')==null){
                o.setAttribute('data-x', true);
                o.style.visibility='visible';
                o.style.display='none';
            }
            $('#'+i).fadeToggle();
            pe_c=i;
        }
    }
    if(i){
        if(to>1) pe_to=to;
        pe_t=setTimeout(function(){pe_p();},pe_to*1000);
    } else
        pe_to=2;
    return false;
}
function pe_w() {if(pe_t!=null)clearTimeout(pe_t);pe_t=setTimeout(function(){pe_p('');},pe_to*1000);return false;}

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
function popup_open(triggerobj,id,deltax,deltay,abs)
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
      if(abs==null) {
       var y=getpos(t);
       curleft=y.x; curtop=y.y;
      }
      var y=Math.round(curtop+deltay>0?curtop+deltay:0),x=Math.round(curleft+deltax>0?curleft+deltax:0);
      if(x+obj.offsetWidth>document.body.offsetWidth-20)
        x=Math.round(document.body.offsetWidth-20-obj.offsetWidth);
      if(y+obj.offsetHeight>document.body.offsetHeight)
        y=Math.round(document.body.offsetHeight-obj.offsetHeight);
			//chrome suxx
			obj.style.position='fixed';
			obj.style.top=y+'px';
			obj.style.left=x+'px';
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
 *     Dragable items:
 *       onmousedown='return dnd_drag(event,<what>[,<icon>[,endcallback]]);'
 *       onmousedown='return dnd_drag(event,this.id,this);'
 *     Drop areas:
 *       onmouseup='return dnd_drop(event,myfunc[,context]);'
 *       and implement myfunc(what,context,event){} callback for each drop area
 */
/*PRIVATE VARS*/
var dnd_dragged=null,dnd_icon=null,dnd_start=null,dnd_endhooks=Array();

/*PUBLIC METHODS*/
function dnd_drag(evt,id,icon,size,endhook){var ti=new Date();
  if(endhook!=null && typeof window[endhook]=='function' && dnd_endhooks.indexOf(endhook)==-1) dnd_endhooks.push(endhook);
  var w=0,h=16;
  if(dnd_start==null) dnd_init();
  if(size!=null&&size>16&&size < 256) h=size;
  dnd_icon=(icon!=null?icon:evt.target).cloneNode(true);
  //w=Math.floor((dnd_icon.width?dnd_icon.width:dnd_icon.offsetWidth)*h/((dnd_icon.height?dnd_icon.height:dnd_icon.offsetHeight)+0.00001));
  //if(w < h) w=h;
  dnd_icon.setAttribute('style','position:absolute;z-index:999;left:0px;top:0px;margin:0px;opacity:0.8;');
  //width:'+w+'px;height:'+h+'px;');
  document.body.appendChild(dnd_icon);
  dnd_dragged=id;
  dnd_start=ti.getTime();
  dnd_display(evt);
  return false;
}
function dnd_drop(evt,callback,ctx){
if(!evt)evt=window.event;
var ti=new Date();
if(dnd_dragged!=null&&callback!=null&&ti.getTime()-dnd_start>200)
  if(typeof callback=='function')callback(dnd_dragged,ctx,evt);
  else if(typeof window[callback]=='function')window[callback](dnd_dragged,ctx,evt);
if(dnd_icon!=null) { try{document.body.removeChild(dnd_icon);}catch(e){} dnd_icon=null; }
dnd_dragged=null;return false;}
function dnd_init(){
  dnd_start=1;
  if ( window.addEventListener )
    window.addEventListener( "mousemove", dnd_display, false );
  else if ( window.attachEvent )
    window.attachEvent( "onmousemove", dnd_display );
  if ( window.addEventListener )
    window.addEventListener( "mouseup", dnd_cancel, false );
  else if ( window.attachEvent )
    window.attachEvent( "onmouseup", dnd_cancel );
}

/*PRIVATE METHODS*/
function dnd_display(e){if(dnd_dragged!=null){
if(e!=null){
  if(e.pageX){
    ml=e.pageX;mt=e.pageY;
  }else{
    ml=(event.clientX + document.body.scrollLeft);mt=(event.clientY+document.body.scrollTop);
  }
}
dnd_icon.style.left=Math.floor(ml+10)+'px';dnd_icon.style.top=Math.floor(mt+10)+'px';}}
function dnd_cancel(e){dnd_dragged=null;dnd_drop();var h;for(h in dnd_endhooks) { if(typeof window[dnd_endhooks[h]]=='function') window[dnd_endhooks[h]](e);}}

