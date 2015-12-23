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
 * @file vendor/phppe/core/js/setsel.js
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief Set list selection
 */

function setsel_drag(evt,id) {
	evt.dataTransfer.setData("text", id);
	document.getElementById(id+':inlist').removeAttribute('data-finished');
	//! force refresh browser screen
	evt.target.parentNode.style.display='none';
	obj=evt.target.cloneNode(true);
	obj.setAttribute('id','setsel_dnd');
	obj.style.background='rgba(255,255,255,0.5);';
	obj.style.display='inline';
	obj.style.position='absolute';
	obj.style.top='0px';
	obj.style.marginTop=(0-obj.offsetHeight-1000)+'px';
	document.body.appendChild(obj);
	if(evt.target.nextSibling!=null && evt.target.tagName==evt.target.nextSibling.tagName && typeof jQuery == 'function') {
		evt.target.nextSibling.style.marginTop=obj.offsetHeight+'px';
		evt.target.nextSibling.setAttribute('id','setsel_animate');
		$('#setsel_animate').css({'marginTop':obj.offsetHeight+'px'});
		$('#setsel_animate').animate({'marginTop':'0px'},250);
		evt.target.nextSibling.removeAttribute('id');
	}
	evt.target.setAttribute('data-inlist',1);
	evt.target.style.display="none";
	evt.dataTransfer.setDragImage(obj, 0, 0);
	evt.target.parentNode.style.display='block';
}

function setsel_droparea(evt) {
	var id=evt.dataTransfer.getData("text");
	evt.preventDefault();
	var i,n=null,o=document.getElementById(id+':inlist').getElementsByTagName('*');
	for(i=0;i<o.length;i++) if(o[i].className.indexOf('setsel_item')>-1){
		o[i].style.marginTop='0px';
		if(evt.layerY<o[i].offsetTop && n==null) n=o[i];
	}
	if(n!=null)
		n.style.marginTop='12px';
}

function setsel_add(evt) {
	var id=evt.dataTransfer.getData("text");
	evt.preventDefault();
	document.getElementById(id+':inlist').setAttribute('data-finished','true');
	obj=document.getElementById('setsel_dnd');
	document.body.removeChild(obj);
	obj.removeAttribute('id');
	obj.removeAttribute('style');
	var i,n=null,o=document.getElementById(id+':inlist').getElementsByTagName('*');
	for(i=0;i<o.length;i++) {
		o[i].style.marginTop='0px';
		if(o[i].getAttribute('data-id')==obj.getAttribute('data-id'))
			o[i].parentNode.removeChild(o[i]);
		else
			if(evt.layerY<o[i].offsetTop && n==null) n=o[i];
	}
	if(n!=null)
		document.getElementById(id+':inlist').insertBefore(obj,n);
	else
		document.getElementById(id+':inlist').appendChild(obj);
	setsel_setvalue(id);
}

function setsel_remove(evt) {
	var id=evt.dataTransfer.getData("text");
	evt.preventDefault();
	document.getElementById(id+':inlist').setAttribute('data-finished','true');
	var i,o=document.getElementById(id+':inlist').getElementsByTagName('*');
	for(i=0;i<o.length;i++) {
		o[i].style.marginTop='0px';
		if(o[i].getAttribute('data-id')==obj.getAttribute('data-id'))
			o[i].parentNode.removeChild(o[i])
	}
	o=document.getElementById(id+':all').getElementsByTagName('*');
	for(i=0;i<o.length;i++)
		if(o[i].getAttribute('data-id')==obj.getAttribute('data-id')) {
			o[i].removeAttribute('data-inlist');
			o[i].style.display='block';
		}
	setsel_setvalue(id);
}

function setsel_dragend(evt) {
	var id=evt.dataTransfer.getData("text");
	obj=document.getElementById('setsel_dnd');
	if(obj!=null)
		document.body.removeChild(obj);
	if(document.getElementById(id+':inlist').getAttribute('data-finished')!='true'){
		var i,w=0,o=document.getElementById(id+':inlist').getElementsByTagName('*');
		for(i=0;i<o.length;i++) {
			o[i].style.marginTop='0px';
			if(o[i].style.display=='none') {
				o[i].style.display='block';
				w=1;
			}
		}
		if(!w){
			o=document.getElementById(id+':all').getElementsByTagName('*');
			for(i=0;i<o.length;i++) {
				o[i].style.marginTop='0px';
				if(o[i].style.display=='none') {
					o[i].removeAttribute('data-inlist');
					o[i].style.display='block';
				}
			}
		}
	} else
		document.getElementById(id+':inlist').removeAttribute('data-finished');
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
	var srch=document.getElementById(id+':filters').getElementsByTagName("INPUT")[0];
	setsel_searchreal(id,document.getElementById(id+':inlist').getElementsByTagName("*"),srch,flt);
	setsel_searchreal(id,document.getElementById(id+':all').getElementsByTagName("*"),srch,flt);
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
			if((par[i].innerText!=null && !par[i].innerText.match(r))||
			   (par[i].textContent!=null && !par[i].textContent.match(r))
				) hide=true;
		}
		if(hide)
			par[i].style.display='none';
		else
			par[i].style.display=par[i].getAttribute('data-inlist')?'none':'block';
	}
}
