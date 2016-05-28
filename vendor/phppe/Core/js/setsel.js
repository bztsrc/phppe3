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
 * @file vendor/phppe/Core/js/setsel.js
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief Set list selection
 */

var setsel_id=null;

function setsel_drag(evt,id) {
	setsel_id=id;
	if(evt.target.nextSibling!=null && evt.target.tagName==evt.target.nextSibling.tagName && typeof jQuery == 'function') {
		evt.target.nextSibling.style.marginTop=evt.target.offsetHeight+'px';
		evt.target.nextSibling.setAttribute('id','setsel_animate');
		$('#setsel_animate').css({'marginTop':evt.target.offsetHeight+'px'});
		$('#setsel_animate').animate({'marginTop':'0px'},300);
		evt.target.nextSibling.removeAttribute('id');
	}
	var i,t=evt.target.parentNode.getElementsByTagName("*");
	for(i=0;i<t.length;i++){
	    if(t[i].getAttribute("data-id")==evt.target.getAttribute("data-id")){
		t[i].setAttribute('style','display:none;');
	    }
	}
    return dnd_drag(evt,id,evt.target,16,"setsel_dragend");
}

function setsel_droparea(evt) {
	if(setsel_id==null||dnd_dragged==null||dnd_icon==null||dnd_icon.tagName!="DIV"||dnd_icon.className.indexOf("setsel_item")==-1) return;
	var i,n=null,l=document.getElementById(setsel_id+':inlist'),o=l.getElementsByTagName('*'),p=l.getBoundingClientRect();
	for(i=0;i<o.length;i++) if(o[i].className.indexOf('setsel_item')>-1){
        var r=o[i].getBoundingClientRect();
		if((evt.target==o[i]||evt.clientY<p.top+o[i].offsetTop-o[i].offsetHeight) && n==null) { n=o[i]; }
		else o[i].style.marginTop='0px';
	}
	if(n!=null&&evt.target.className.indexOf("setsel_item")>-1){
		n.style.marginTop='12px';
	}
}

function setsel_add(evt) {
	var id=setsel_id;
	if(dnd_dragged==null||dnd_icon==null||dnd_icon.tagName!="DIV"||dnd_icon.className.indexOf("setsel_item")==-1) return;
	dnd_icon.setAttribute('style','');
	var i,n=null,o=document.getElementById(id+':inlist').getElementsByTagName('*');
	for(i=0;i<o.length;i++) {
		var mt=Math.round(o[i].style.marginTop.replace('px',''));
		o[i].style.marginTop='0px';
		if(o[i].getAttribute('data-id')!=null && o[i].getAttribute('data-id')==dnd_icon.getAttribute('data-id'))
			o[i].parentNode.removeChild(o[i]);
		else {
			if(mt>0 && n==null) n=o[i];
		}
	}
	if(n!=null)
		document.getElementById(id+':inlist').insertBefore(dnd_icon,n);
	else
		document.getElementById(id+':inlist').appendChild(dnd_icon);
	o=document.getElementById(id+':all').getElementsByTagName('*');
	for(i=0;i<o.length;i++) {
		if(o[i].getAttribute('data-id')!=null && o[i].getAttribute('data-id')==dnd_icon.getAttribute('data-id')) {
			o[i].setAttribute('data-inlist',1);
			break;
		}
	}
	setsel_setvalue(id);
}

function setsel_remove(evt) {
	var id=setsel_id;
	setsel_id=null;
	if(dnd_dragged==null||dnd_icon==null||dnd_icon.tagName!="DIV"||dnd_icon.className.indexOf("setsel_item")==-1) return;
	var i,o=document.getElementById(id+':inlist').getElementsByTagName('*');
	for(i=0;i<o.length;i++) {
		if(o[i].style.display=='none'||(o[i].getAttribute('data-id')!=null && o[i].getAttribute('data-id')==dnd_icon.getAttribute('data-id')))
			o[i].parentNode.removeChild(o[i]);
	}
	o=document.getElementById(id+':all').getElementsByTagName('*');
	for(i=0;i<o.length;i++) {
		if(o[i].getAttribute('data-id')!=null && o[i].getAttribute('data-id')==dnd_icon.getAttribute('data-id')) {
			o[i].removeAttribute('data-inlist');
			o[i].style.display='block';
			break;
		}
	}
	setsel_setvalue(id);
}

function setsel_dragend(evt) {
	var id=setsel_id;
	setsel_id=null;
	if(id==null||id=='') return;
	var inl=document.getElementById(id+':inlist');
    if(inl!=null){
		var i,w=0,o=inl.getElementsByTagName('*');
		for(i=0;i<o.length;i++) {
			o[i].style.marginTop='0px';
			if(o[i].style.display=='none') {
				o[i].style.display='block';
				w=1;
			}
		}
		o=document.getElementById(id+':all').getElementsByTagName('*');
		for(i=0;i<o.length;i++) {
			o[i].style.marginTop='0px';
			if(o[i].style.display=='none'&&o[i].getAttribute('data-inlist')!=1) {
				o[i].style.display='block';
			}
		}
    }
}

function setsel_setvalue(id) {
		var v="",i,w=0,o=document.getElementById(id+':inlist').getElementsByTagName('*');
		for(i=0;i<o.length;i++)
			if(o[i].getAttribute('data-id')!=null)
				v+=(v?',':'')+o[i].getAttribute('data-id');
		if(v==null) v='';
		document.getElementById(id).value=v;
}

function setsel_search(id) {
	var flt=document.getElementById(id+':filters').getElementsByTagName("SELECT");
	var srch=document.getElementById(id+':filters').getElementsByTagName("INPUT");
	setsel_searchreal(id,document.getElementById(id+':inlist').getElementsByTagName("*"),srch[srch.length-1],flt);
	setsel_searchreal(id,document.getElementById(id+':all').getElementsByTagName("*"),srch[srch.length-1],flt);
}
function setsel_searchreal(id,par,srch,flt) {
	var i,j;
	for(i=0;i<par.length;i++) {
		if(par[i].className==null||par[i].className.indexOf('setsel_item')==-1) continue;
		var hide=false;
		for(j=0;j<flt.length;j++) {
			if(flt[j].value=='' || (flt[j].name=='lang'&&par[i].getAttribute('data-lang')=='') ) continue;
			var p=par[i].getAttribute('data-'+flt[j].name),r=new RegExp(flt[j].value);
			if(p==null|| !p.match(r)) hide=true;
		}
		srch.className=srch.className.replace('errinput','');
		if(srch.value) {
			try{
			var r=new RegExp(srch.value,'i');
			} catch(e) {
				srch.className+=' errinput';
			}
			if((par[i].innerText!=null && !par[i].innerText.match(r))&&
			   (par[i].textContent!=null && !par[i].textContent.match(r))&&
			   (par[i].getAttribute('data-id')!=null && !par[i].getAttribute('data-id').match(r))
				) hide=true;
		}
		if(hide)
			par[i].style.display='none';
		else
			par[i].style.display=par[i].getAttribute('data-inlist')?'none':'block';
	}
}
