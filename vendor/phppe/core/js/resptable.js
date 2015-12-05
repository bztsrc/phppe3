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
 * @file vendor/phppe/core/js/resptable.js
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief responsive tables
 */
var resptable_instances=[];
var resptable_touchx=-1,resptable_touchy=-1;

function resptable_init() {
    resptable_detect();
    if ( window.addEventListener )
        window.addEventListener( "resize", resptable_recalc, false );
    else if ( window.attachEvent )
        window.attachEvent( "onresize", resptable_recalc );
    else
        window["onresize"]=resptable_recalc;
    if ( window.addEventListener )
        window.addEventListener( "scroll", resptable_recalc, false );
    else if ( window.attachEvent )
        window.attachEvent( "onscroll", resptable_recalc );
    else
        window["onscroll"]=resptable_recalc;
    setTimeout('resptable_recalc();',100);
}
function resptable_scrollto(name,advanceTop) {
	var t,y=0,obj=document.getElementById(name);
//	t=obj;
//	while (t.offsetParent) {
//		y += t.offsetTop;
//		t = t.offsetParent;
//   	}
	window.scrollTo(0,obj.offsetTop+obj.offsetHeight+advanceTop-(pe_ot>0?pe_ot:0));
}
function resptable_detect() {
    var i,ts=document.getElementsByTagName('TABLE'),ox=0,oy=0;
    for(i=0;i<ts.length;i++) if(ts[i].className.indexOf('resptable')>-1) {
    	var l=resptable_instances.length;
		resptable_instances[resptable_instances.length]=ts[i];
	var j,f=0,c=0,th=ts[i].getElementsByTagName('TH');
	for(j=0;j<th.length;j++) {
	    if(th[j].getAttribute('data-fixed')!=null) f+=th[j].offsetWidth;
	    else c++;
	}
//	f=40;
	t=ts[i];
	while (t.offsetParent) {
		ox += t.offsetLeft;
		oy += t.offsetTop;
		t = t.offsetParent;
   	}
	var pagerdiv=document.createElement('DIV');
	var prevspan=document.createElement('SPAN'),nextspan=document.createElement('SPAN');
	pagerdiv.setAttribute('id','resptable'+l+'_pager');
	pagerdiv.className="resptable_pager";
	pagerdiv.setAttribute('style','position:relative;width:'+ts[i].style.width+';');
	prevspan.setAttribute('id','resptable'+l+'_prev');
	prevspan.className="resptable_button_inactive";
	prevspan.innerHTML=ts[i].getAttribute('data-prev')?ts[i].getAttribute('data-prev'):'◀';
	prevspan.setAttribute('onclick','resptable_prev('+l+');');
	nextspan.setAttribute('id','resptable'+l+'_next');
	nextspan.className="resptable_button_inactive";
	nextspan.innerHTML=ts[i].getAttribute('data-next')?ts[i].getAttribute('data-next'):'▶';
	nextspan.setAttribute('onclick','resptable_next('+l+');');
	pagerdiv.appendChild(prevspan);
	pagerdiv.appendChild(nextspan);
	ts[i].parentNode.insertBefore(pagerdiv, ts[i]);
//	ts[i].setAttribute('style','margin-top:'+(pagerdiv.offsetHeight+0)+'px;');
	ts[i].setAttribute('data-r','resptable'+l);
	ts[i].setAttribute('data-f',f);
	ts[i].setAttribute('data-c',c);
	ts[i].setAttribute('data-o',0);
	ts[i].setAttribute('data-x',ox);
	ts[i].setAttribute('data-y',oy);
	ts[i].setAttribute('ontouchstart','resptable_touchstart(event);');
//	ts[i].setAttribute('onmousedown','resptable_touchstart(event);');
	ts[i].setAttribute('ontouchend','resptable_touchend(event,'+i+');');
//	ts[i].setAttribute('onmouseup','resptable_touchend(event,'+i+');');
	ts[i].setAttribute('ontouchcancel','resptable_touchcancel(event);');
    }
    for(i=0;i<resptable_instances.length;i++) {
		var ts=resptable_instances,j,tblhdr=document.createElement('TABLE');
		var pagerdiv=document.getElementById('resptable'+i+'_pager');
		tblhdr.setAttribute('id','resptable'+i+'_hdr');
		tblhdr.className=resptable_instances[i].className;
		tblhdr.setAttribute('style','position:relative;');
		tblhdr.appendChild(ts[i].rows[0].cloneNode(true));
		ts[i].rows[1].cells[0].className=ts[i].rows[0].cells[0].className;
		ts[i].rows[0].style.display='none';
//		ts[i].style='margin-top:-'+ts[i].rows[0].offsetHeight+'px;';
		pagerdiv.appendChild(tblhdr);
		if(resptable_instances[i].getAttribute('data-nomenu')==null){
			var was=0,k=tblhdr.getElementsByTagName('th')[0];
//			k.innerHTML="&#xf0c9;";
//			k.setAttribute('class','etlapmenu');
			var popup=document.createElement('div');
			popup.setAttribute('id','resptable'+i+'_popup');
			popup.setAttribute('style','position:absolute;display:block;background:#fff;visibility:hidden;box-shadow:2px 2px 2px #000;padding:3px;text-align:left;')
//			var p=document.createElement('div');
//			p.setAttribute('id','resptable'+i+'_popupbg');
//			p.setAttribute('style','position:fixed;top:0px;left:0px;width:100%;height:100%;visibility:hidden;background:#808080')
//			p.setAttribute('onclick','document.getElementById("resptable'+i+'_popup").style.visibility="hidden";document.getElementById("resptable'+i+'_popupbg").style.visibility="hidden";')
//			pagerdiv.appendChild(p);
			for(j=0;j<resptable_instances[i].rows.length;j++) {
				if(resptable_instances[i].rows[j]!=null && resptable_instances[i].rows[j].cells[0].getAttribute('colspan')!=null) {
					var d=document.createElement('div');
					resptable_instances[i].rows[j].cells[0].setAttribute('id','resptable'+i+'_menu'+j);
					d.innerHTML=resptable_instances[i].rows[j].cells[0].innerHTML;
					d.setAttribute('style',resptable_instances[i].rows[j].cells[0].getAttribute('style')+';cursor:pointer;');
					d.setAttribute('onclick','resptable_scrollto("resptable'+i+'_menu'+j+'",'+(Math.floor(resptable_instances[i].getAttribute('data-y'))-10)+');document.getElementById("resptable'+i+'_popup").style.visibility="hidden";');
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
		resptable_setwidth(resptable_instances[i]);
	}
}
function resptable_prev(i)
{
	var tbl=resptable_instances[i];
	if(tbl.getAttribute('data-o')>0) tbl.setAttribute('data-o',Math.floor(tbl.getAttribute('data-o'))-1);
	resptable_setwidth(tbl);
}
function resptable_next(i)
{
	var tbl=resptable_instances[i];
	if(tbl.getAttribute('data-o')<tbl.getAttribute('data-h')) tbl.setAttribute('data-o',Math.floor(tbl.getAttribute('data-o'))+1);
	resptable_setwidth(tbl);
}
function resptable_setwidth(tbl) {
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

//				tbl.rows[i].cells[j].setAttribute('style','display:'+st+';width:'+(thhdr[j].innerWidth+0)+'px !important;');
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
//	    if(th[j].getAttribute('data-fixed')==null) {
w=tbl.rows[r].cells[j].offsetWidth;
			th[j].style.width=(w+0)+'px';
			thhdr[j].style.width=(w+0)+'px';
/*	    } else {
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
}
function resptable_recalc(evt) {
    var i;
    for(i=0;i<resptable_instances.length;i++)
	resptable_setwidth(resptable_instances[i]);
}
function resptable_touchstart(evt) {
	resptable_touchx=evt.changedTouches[0].pageX?evt.changedTouches[0].pageX:evt.pageX;
	resptable_touchy=evt.changedTouches[0].pageY?evt.changedTouches[0].pageY:evt.pageY;
	return true;
}
function resptable_touchend(evt,i) {
	var x=(evt.changedTouches[0].pageX?evt.changedTouches[0].pageX:evt.pageX),y=(evt.changedTouches[0].pageY?evt.changedTouches[0].pageY:evt.pageY);
	var a=resptable_touchx-x;
	var b=resptable_touchy-y;
	if(resptable_touchx!=-1 && (a>0?a:-a) > (b>0?b:-b)) {
		if(resptable_touchx-x<-50)
			resptable_prev(i);
		else if(resptable_touchx-x>50)
			resptable_next(i);
	}
	resptable_touchx=-1;
	resptable_touchy=-1;
	return true;
}
function resptable_touchcancel(evt) {
	resptable_touchx=-1;
	resptable_touchy=-1;
	return true;
}

