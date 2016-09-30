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
use PHPPE\Core as Core;

//! fallback to english if a specific language translation not found
$d=array_unique(array_merge(
  glob("vendor/*/lang/".$_SESSION['pe_l'].".php"),
  glob("vendor/*/*/lang/".$_SESSION['pe_l'].".php"),
  glob("vendor/*/lang/en.php"),
  glob("vendor/*/*/lang/en.php")
  ));
//make sure it's not empty otherwise json_encode() will report JS syntax error
$lang = ["lang"=>$_SESSION['pe_l']];
foreach ($d as $f) {$la=array(); $la=include($f);if( is_array( $la ) )$lang += $la; }
//force cache
Core::$core->nocache = false;
header( "Pragma: cache" );
header( "Cache-Control: cache,public,max-age=86400" );

//turn off animations
if (!empty(Core::$core->noanim))
    echo("if (typeof jQuery!='undefined') jQuery.fx.off = true;");
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

function function_exists(f)
{
  try {
    return eval("typeof "+f)=='function';
  }catch(e){
    return false;
  }
}

function getpos(obj)
{
  var o=obj.getBoundingClientRect();
  return {x:o.left,y:o.top,w:obj.offsetWidth,h:obj.offsetHeight,obj:obj};
}


/*
 *   Multilanguage support
 */
var LANG=<?=json_encode($lang)?>;
function L(t)
{
  return LANG[t]!=null&&LANG[t]!=undefined?LANG[t]:(t!=null?t.replace(/_/g,' '):'');
}

/*
 * PHPPE Core
 */
var 
	pe_t=null, //timer
	pe_c=null, //current item
	pe_to=2,   //time out
	pe_ot=0,   //offset top
	pe_a=<?=!empty(Core::$core->noanim)?'false':'true'?>;
/*
 *   PE Panel
 */
function pe_p(i,trg,to,dx)
{
    var evt = window.event || (arguments.callee.caller!=null && arguments.callee.caller.arguments[0]);
    var o=i!=null&&i!=''?document.getElementById(i):null;
    if(trg==null && evt && evt.target) trg=evt.target;
    if(trg && o) {
        var rt = trg.getBoundingClientRect();
        o.style.left=(rt.left+(dx==null?0:dx))+'px';
        o.style.top=(rt.top+evt.target.offsetHeight)+'px';
    }
    if(pe_t!=null)clearTimeout(pe_t);
    if(!pe_a || typeof jQuery=='undefined'){
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
function pe_w()
{
  if(pe_t!=null)
    clearTimeout(pe_t);
  pe_t=setTimeout(function(){pe_p('');},pe_to*1000);
  return false;
}

/*
 * PE Plugins
 */
var pe={};

/*
 *   JS Cookie support
 */
pe.cookie = {
  get:function(name)
  {
    var i=document.cookie.indexOf(name + "=");
    if(i==-1)
      return null;
    i=document.cookie.indexOf("=",i)+1;
    var e=document.cookie.indexOf(";",i);
    if(e==-1)
      e=document.cookie.length;
    return unescape(document.cookie.substring(i,e));
  },

  set:function(name,value,nDays,path) {
    var t=new Date(), e=new Date();
    if(nDays==null||nDays<1)
      nDays=1;
    e.setTime(t.getTime()+3600000*24*nDays);
    document.cookie=name+"="+escape(value)+";path="+(path!=null?path:"/")+";expires="+e.toGMTString();
  }
};

/*
 *   JS local storage support
 */
pe.store = {
  get:function(name)
  {
    try{
      return (typeof(localStorage)!=="undefined"?localStorage.getItem(name):null);
    }catch(e){
      return '*none*';
    }
  },

  set:function(name,value)
  {
    try{
      return (typeof(localStorage)!=="undefined"?(value!=null?localStorage.setItem(name,value):localStorage.removeItem(name)):null);
    }catch(e){
      return '*none*';
    }
  }
};

/*
 *   Popup menu support
 *    popups: <div id='mypopup' class='popup'>menu</div>
 *    triggers: onmouseover='pe.popup.open(this,"mypopup",10,10);'
 */
pe.popup = {
/*PUBLIC*/
  timeout:1000,

/*PRIVATE*/
  tmr:null,
  currentobj:null,
  current:'',
  dontclose:false,

/*PUBLIC METHODS*/
  open: function(triggerobj,id,deltax,deltay,abs)
  {
    var obj,oldobj=triggerobj,i,t;
          var curleft = 0;
          var curtop = 0;
    if(pe.popup.tmr!=null) clearTimeout(pe.popup.tmr);
    if(pe.popup.current!='') obj=document.getElementById(pe.popup.current);
    if(obj!=null) obj.style.display='none';
    obj=document.getElementById(id);
    if(obj!=null) {
      obj.style.zIndex=Math.round(triggerobj.style.zIndex+10);
      pe.popup.currentobj=t=triggerobj;
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
      obj.onmouseover=pe.popup.over;
      obj.onmouseout=pe.popup.out;
      oldobj.onmouseout=pe.popup.out;
      pe.popup.current=id;
      pe.popup.dontclose=true;
      pe.popup.tmr=setTimeout('pe.popup.close();',pe.popup.timeout);
    }
  },

/*PRIVATE METHODS*/
  over: function()
  {
    pe.popup.dontclose=true;
  },

  out: function()
  {
    pe.popup.dontclose=false;
    if(pe.popup.tmr==null)
      pe.popup.tmr=setTimeout('pe.popup.close();',pe.popup.timeout);
  },

  close: function()
  {
    var obj,i;
    if(pe.popup.dontclose)
      pe.popup.tmr=setTimeout('pe.popup.close();',pe.popup.timeout);
    else {
      obj=document.getElementById(pe.popup.current);
      if(obj!=null)
        obj.style.display='none';
      pe.popup.current=0;
      pe.popup.currentobj=null;
      pe.popup.tmr=null;
    }
  },

  init: function()
  {
    var i,c,items=document.querySelectorAll("[data-popup]");
    for(i=0;i<items.length;i++) {
      c='return pe.popup.open(this,'+items[i].getAttribute('data-drag')+',10,10);';
      items[i].setAttribute('onmouseover',c);
      items[i].setAttribute('ontouchstart',c);
    }
  }
};

/*
 *   JS Drag'n'drop support (legacy HTML4)
 *     Dragable items:
 *       onmousedown='return pe.dnd.drag(event,<what>[,<icon>[,endcallback]]);'
 *       onmousedown='return pe.dnd.drag(event,this.id,this);'
 *     Drop areas:
 *       onmouseup='return pe.dnd.drop(event,myfunc[,context]);'
 *       and implement myfunc(what,context,event){} callback for each drop area
 */
pe.dnd = {
/*PRIVATE VARS*/
  dragged:null,
  icon:null,
  start:null,
  endhooks:[],

/*PUBLIC METHODS*/
  drag: function(evt,id,icon,size,endhook)
  {
    var ti=new Date();
    if(endhook!=null && typeof window[endhook]=='function' && pe.dnd.endhooks.indexOf(endhook)==-1) pe.dnd.endhooks.push(endhook);
    var w=0,h=16;
    if(pe.dnd.start==null) pe.dnd.init();
    if(size!=null&&size>16&&size < 256) h=size;
    pe.dnd.icon=(icon!=null?icon:evt.target).cloneNode(true);
    //w=Math.floor((pe.dnd.icon.width?pe.dnd.icon.width:pe.dnd.icon.offsetWidth)*h/((pe.dnd.icon.height?pe.dnd.icon.height:pe.dnd.icon.offsetHeight)+0.00001));
    //if(w < h) w=h;
    pe.dnd.icon.setAttribute('style','position:absolute;z-index:999;left:0px;top:0px;margin:0px;opacity:0.8;');
    //width:'+w+'px;height:'+h+'px;');
    document.body.appendChild(pe.dnd.icon);
    pe.dnd.dragged=id;
    pe.dnd.start=ti.getTime();
    pe.dnd.display(evt);
    return false;
  },

  drop: function(evt,callback,ctx)
  {
    if(!evt)evt=window.event;
    var ti=new Date();
    if(pe.dnd.dragged!=null&&callback!=null&&ti.getTime()-pe.dnd.start>200)
      if(typeof callback=='function')callback(pe.dnd.dragged,ctx,evt);
      else if(function_exists(callback))eval(callback+"(pe.dnd.dragged,ctx,evt)");
    if(pe.dnd.icon!=null) { try{document.body.removeChild(pe.dnd.icon);}catch(e){} pe.dnd.icon=null; }
    pe.dnd.dragged=null;return false;
  },

  init: function()
  {
    pe.dnd.start=1;
    if ( window.addEventListener )
      window.addEventListener( "mousemove", pe.dnd.display, false );
    else if ( window.attachEvent )
      window.attachEvent( "onmousemove", pe.dnd.display );
    if ( window.addEventListener )
      window.addEventListener( "mouseup", pe.dnd.cancel, false );
    else if ( window.attachEvent )
      window.attachEvent( "onmouseup", pe.dnd.cancel );
    var i,c,items=document.querySelectorAll("[data-drag]");
    for(i=0;i<items.length;i++) {
      c='return pe.dnd.drag(event,'+items[i].getAttribute('data-drag')+');';
      items[i].setAttribute('onmousedown',c);
      items[i].setAttribute('ontouchstart',c);
    }
    items=document.querySelectorAll("[data-drop]");
    for(i=0;i<items.length;i++) {
      c='return pe.dnd.drop(event,'+items[i].getAttribute('data-drop')+');';
      items[i].setAttribute('onmouseup',c);
      items[i].setAttribute('ontouchend',c);
    }
  },
  
/*PRIVATE METHODS*/
  display: function(e)
  {
    if(pe.dnd.dragged!=null){
      if(e!=null){
        if(e.pageX){
          ml=e.pageX;mt=e.pageY;
        }else{
          ml=(event.clientX + document.body.scrollLeft);mt=(event.clientY+document.body.scrollTop);
        }
      }
      pe.dnd.icon.style.left=Math.floor(ml+10)+'px';
      pe.dnd.icon.style.top=Math.floor(mt+10)+'px';
    }
  },

  cancel:function(e)
  {
    var h;
    pe.dnd.dragged=null;
    pe.dnd.drop();
    for(h in pe.dnd.endhooks) {
      if(function_exists(pe.dnd.endhooks[h]))
        eval(pe.dnd.endhooks[h]+"(e)");
    }
  }
};
