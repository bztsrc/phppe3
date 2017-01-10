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
 * @file vendor/phppe/Core/js/resptable.js
 * @author bzt
 * @date 1 Jan 2016
 * @brief responsive tables
 */
//L("Responsive tables")
pe.resptable = {
  instances:[],
  touchx:-1,
  touchy:-1,

/* PUBLIC METHODS */
  init: function() {
    pe.resptable.detect();
    if ( window.addEventListener )
      window.addEventListener( "resize", pe.resptable.recalc, false );
    else if ( window.attachEvent )
      window.attachEvent( "onresize", pe.resptable.recalc );
    else
      window["onresize"]=pe.resptable.recalc;
    if ( window.addEventListener )
      window.addEventListener( "scroll", pe.resptable.recalc, false );
    else if ( window.attachEvent )
      window.attachEvent( "onscroll", pe.resptable.recalc );
    else
      window["onscroll"]=pe.resptable.recalc;
    setTimeout('pe.resptable.recalc();',100);
  },

/* PRIVATE METHODS */
  scrollto: function(name,advanceTop)
  {
    var t,y=0,obj=document.getElementById(name);
    window.scrollTo(0,obj.offsetTop+obj.offsetHeight+advanceTop-(pe_ot>0?pe_ot:0)-30);
  },

  detect: function()
  {
    var i,ts=document.getElementsByTagName('TABLE'),ox=0,oy=0;
    for(i=0;i<ts.length;i++) if(ts[i].className.indexOf('resptable')>-1) {
      var l=pe.resptable.instances.length;
      pe.resptable.instances[pe.resptable.instances.length]=ts[i];
    var j,f=0,c=0,th=ts[i].getElementsByTagName('TH');
    for(j=0;j<th.length;j++) {
      if(th[j].getAttribute('data-fixed')!=null) f+=th[j].offsetWidth;
      else c++;
    }
    t=ts[i];
    while (t.offsetParent) {
      ox += t.offsetLeft;
      oy += t.offsetTop;
      t = t.offsetParent;
    }
    var pagerdiv=document.createElement('DIV');
    var prevspan=document.createElement('SPAN'),nextspan=document.createElement('SPAN');
    pagerdiv.setAttribute('id','resptable'+l+'_pager');
    pagerdiv.setAttribute('dir','ltr');
    pagerdiv.className="resptable_pager";
    pagerdiv.setAttribute('style','position:relative;width:'+ts[i].style.width+';');
    prevspan.setAttribute('id','resptable'+l+'_prev');
    prevspan.className="resptable_button_inactive";
    prevspan.innerHTML=ts[i].getAttribute('data-prev')?ts[i].getAttribute('data-prev'):'◀';
    prevspan.setAttribute('onclick','pe.resptable.prev('+l+');');
    nextspan.setAttribute('id','resptable'+l+'_next');
    nextspan.className="resptable_button_inactive";
    nextspan.innerHTML=ts[i].getAttribute('data-next')?ts[i].getAttribute('data-next'):'▶';
    nextspan.setAttribute('onclick','pe.resptable.next('+l+');');
    pagerdiv.appendChild(prevspan);
    pagerdiv.appendChild(nextspan);
    ts[i].parentNode.insertBefore(pagerdiv, ts[i]);
    ts[i].setAttribute('data-r','resptable'+l);
    ts[i].setAttribute('data-f',f);
    ts[i].setAttribute('data-c',c);
    ts[i].setAttribute('data-o',0);
    ts[i].setAttribute('data-x',ox);
    ts[i].setAttribute('data-y',oy);
    ts[i].setAttribute('ontouchstart','pe.resptable.touchstart(event);');
//    ts[i].setAttribute('onmousedown','pe.resptable.touchstart(event);');
    ts[i].setAttribute('ontouchend','pe.resptable.touchend(event,'+i+');');
//    ts[i].setAttribute('onmouseup','pe.resptable.touchend(event,'+i+');');
    ts[i].setAttribute('ontouchcancel','pe.resptable.touchcancel(event);');
    }
    for(i=0;i<pe.resptable.instances.length;i++) {
      var ts=pe.resptable.instances,j,tblhdr=document.createElement('TABLE');
      var pagerdiv=document.getElementById('resptable'+i+'_pager');
      tblhdr.setAttribute('id','resptable'+i+'_hdr');
      tblhdr.className=pe.resptable.instances[i].className;
      tblhdr.setAttribute('style','position:relative;');
      tblhdr.appendChild(ts[i].rows[0].cloneNode(true));
      ts[i].rows[1].cells[0].className=ts[i].rows[0].cells[0].className;
      ts[i].rows[0].style.display='none';
//      ts[i].style='margin-top:-'+ts[i].rows[0].offsetHeight+'px;';
      pagerdiv.appendChild(tblhdr);
      if(pe.resptable.instances[i].getAttribute('data-nomenu')==null){
        var was=0,k=tblhdr.getElementsByTagName('th')[0];
        var popup=document.createElement('div');
        popup.setAttribute('id','resptable'+i+'_popup');
        popup.setAttribute('style','position:absolute;display:block;background:#fff;visibility:hidden;box-shadow:2px 2px 2px #000;padding:3px;text-align:left;')
        for(j=0;j<pe.resptable.instances[i].rows.length;j++) {
          if(pe.resptable.instances[i].rows[j]!=null && pe.resptable.instances[i].rows[j].cells[0].getAttribute('colspan')!=null) {
            var d=document.createElement('div');
            pe.resptable.instances[i].rows[j].cells[0].setAttribute('id','resptable'+i+'_menu'+j);
            d.innerHTML=pe.resptable.instances[i].rows[j].cells[0].innerHTML;
            d.setAttribute('style',pe.resptable.instances[i].rows[j].cells[0].getAttribute('style')+';cursor:pointer;');
            d.setAttribute('onclick','pe.resptable.scrollto("resptable'+i+'_menu'+j+'",'+Math.floor(pe.resptable.instances[i].getAttribute('data-y'))+');document.getElementById("resptable'+i+'_popup").style.visibility="hidden";');
            popup.appendChild(d);
            was=1;
          }
        }
        pagerdiv.appendChild(popup);
        if(was){
          k.setAttribute('style','cursor:pointer;');
          k.setAttribute('onclick','document.getElementById("resptable'+i+'_popup").style.visibility=document.getElementById("resptable'+i+'_popup").style.visibility=="visible"?"hidden":"visible";')
        }
      }
      pe.resptable.setwidth(pe.resptable.instances[i]);
    }
  },

  prev: function(i)
  {
    var tbl=pe.resptable.instances[i];
    if(tbl.getAttribute('data-o')>0) tbl.setAttribute('data-o',Math.floor(tbl.getAttribute('data-o'))-1);
    pe.resptable.setwidth(tbl);
  },

  next: function(i)
  {
    var tbl=pe.resptable.instances[i];
    if(tbl.getAttribute('data-o')<tbl.getAttribute('data-h')) tbl.setAttribute('data-o',Math.floor(tbl.getAttribute('data-o'))+1);
    pe.resptable.setwidth(tbl);
  },

  setwidth: function(tbl)
  {
    var i,j,w=0,h=0,th=tbl.getElementsByTagName('TH'),ow=tbl.offsetWidth,o=Math.floor(tbl.getAttribute('data-o'));
    var tblhdr=document.getElementById(tbl.getAttribute('data-r')+'_hdr');
    var thhdr=tblhdr.getElementsByTagName('TH');
    var m=tbl.getAttribute('data-min'),t,ox=0,oy=0,lw,ow=tbl.offsetWidth;
    var scrx=window.pageXOffset?window.pageXOffset:document.body.scrollLeft,scry=window.pageYOffset?window.pageYOffset:document.body.scrollTop;
    if(m<10) m=140;
    while(w<m && h<tbl.getAttribute('data-c')){
      lw=w;
      w=Math.floor((tbl.offsetWidth-tbl.getAttribute('data-f'))/(tbl.getAttribute('data-c')-h));
      if(w==NaN) w=lw;
      if(w<m) h++;
    }
    t=tbl;
    while (t.offsetParent) {
      ox += t.offsetLeft;
      oy += t.offsetTop;
      t = t.offsetParent;
    }
    tbl.setAttribute('data-x',ox);
    tbl.setAttribute('data-y',oy);
    for(i=0;i<tbl.rows.length;i++)
      for(j=0;j<th.length;j++) {
        if(tbl.rows[i].cells[j]==null) continue;
        if(th[j].getAttribute('data-fixed')==null) {
          var st=(j>=o+(tbl.getAttribute('data-f')>0?1:0)&&j<th.length-h+o?'table-cell':'none');
          if(i==0) {
            tblhdr.rows[0].cells[j].setAttribute('style','display:'+st+';width:'+w+'px !important;');
          }
          tbl.rows[i].cells[j].setAttribute('style','display:'+st+';width:'+w+'px;');
  
//          tbl.rows[i].cells[j].setAttribute('style','display:'+st+';width:'+(thhdr[j].innerWidth+0)+'px !important;');
        } else {
          var old;
          try {
              old=tbl.rows[i].cells[j].getAttribute('style').replace(/width:[^;]+;/,'');
          } catch(e) {
              old='';
          }
          tbl.rows[i].cells[j].setAttribute('style',old+'width:'+Math.floor(tbl.getAttribute('data-f'))+'px;');
        }
      }
    if(tbl.getAttribute('data-h')==null || tbl.getAttribute('data-h')!=h) {o=0;tbl.setAttribute('data-o',0);}
    tbl.setAttribute('data-w',w);
    tbl.setAttribute('data-h',h);
    var r=1;
    for(j=1;j<3;j++)
      if(tbl.rows[j].cells[0].getAttribute("colspan")==null) {r=j; break;}
    for(j=0;j<th.length;j++) {
//      if(th[j].getAttribute('data-fixed')==null) {
        w=tbl.rows[r].cells[j].offsetWidth;
        th[j].style.width=(w+0)+'px';
        thhdr[j].style.width=(w+0)+'px';
/*      } else {
        th[j].setAttribute('style','width:'+Math.floor(tbl.getAttribute('data-f'))+'px !important;');
        thhdr[j].setAttribute('style','width:'+Math.floor(tbl.getAttribute('data-f'))+'px !important;');
      }*/
    }
    document.getElementById(tbl.getAttribute('data-r')+'_prev').className=(tbl.getAttribute('data-o')>0?'resptable_button':'resptable_button_inactive');
    document.getElementById(tbl.getAttribute('data-r')+'_next').className=(h>0&&tbl.getAttribute('data-o')<h?'resptable_button':'resptable_button_inactive');
    tblhdr.style.width=tbl.offsetWidth+'px';
    var y=Math.floor(tbl.getAttribute('data-y')),p=document.getElementById(tbl.getAttribute('data-r')+'_pager');
    if(scry>y-p.offsetHeight-(pe_ot>0?pe_ot:0) && scry<y-p.offsetHeight+tbl.offsetHeight-(pe_ot>0?pe_ot:0)) {
      p.setAttribute('style','position:fixed;top:'+(pe_ot>0?pe_ot:0)+'px;left:'+(ox-scrx)+'px;width:'+ow+'px;');
      tbl.setAttribute('style','margin-top:'+(p.offsetHeight)+'px;width:'+ow+'px;');
    } else {
      p.setAttribute('style','position:relative;display:block;width:'+ow+'px;');
      tbl.setAttribute('style','margin-top:0px;width:'+ow+'px;');
    }
  },

  recalc: function(evt)
  {
    var i;
    for(i=0;i<pe.resptable.instances.length;i++)
    pe.resptable.setwidth(pe.resptable.instances[i]);
  },

  touchstart: function(evt)
  {
    pe.resptable.touchx=evt.changedTouches[0].pageX?evt.changedTouches[0].pageX:evt.pageX;
    pe.resptable.touchy=evt.changedTouches[0].pageY?evt.changedTouches[0].pageY:evt.pageY;
    return true;
  },

  touchend: function(evt,i)
  {
    var x=(evt.changedTouches[0].pageX?evt.changedTouches[0].pageX:evt.pageX),y=(evt.changedTouches[0].pageY?evt.changedTouches[0].pageY:evt.pageY);
    var a=pe.resptable.touchx-x;
    var b=pe.resptable.touchy-y;
    if(pe.resptable.touchx!=-1 && (a>0?a:-a) > (b>0?b:-b)) {
      if(pe.resptable.touchx-x<-50)
        pe.resptable.prev(i);
      else if(pe.resptable.touchx-x>50)
        pe.resptable.next(i);
    }
    pe.resptable.touchcancel(evt);
    return true;
  },

  touchcancel: function(evt)
  {
    pe.resptable.touchx=-1;
    pe.resptable.touchy=-1;
    return true;
  }
};
