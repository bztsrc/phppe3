/*
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016 bzt
 */

pe.cms = {
    return:null, url:null, reload:false,
    border:null, item:null,
    scrx:null, srcy:null,
    divchooselast:null,

    //! open cms editor box
    edit:function(icon, paramidx, adjust, minw, minh, forcew, forceh, forcefull)
    {
        //! save current scroll position
        this.scrx=window.pageXOffset?window.pageXOffset:document.body.scrollLeft;
        this.scry=window.pageYOffset?window.pageYOffset:document.body.scrollTop;
        //! get background and editor box
        var cmsbg = document.getElementById('cmsbg');
        var cmsbox = document.getElementById('cmsbox');
        if(cmsbg == null) {
            //! create background div
            cmsbg = document.createElement('DIV');
            cmsbg.setAttribute('id', 'cmsbg');
            cmsbg.setAttribute('style', 'position:fixed;display:table-cell;top:0px;left:0px;width:100%;height:100%;z-index:2001;background:#000;opacity:0.4;visibility:hidden;backdrop-filter:blur(1px);');
            cmsbg.setAttribute('onclick', 'pe.cms.close();');
            document.body.appendChild(cmsbg);
            //! create editor box iframe
            cmsbox = document.createElement('IFRAME');
            cmsbox.setAttribute('id', 'cmsbox');
            cmsbox.setAttribute('style', 'position:fixed;display:table-cell;top:0px;left:0px;width:1px;height:1px;z-index:2002;background:rgba(64,64,64,0.9) !important;visibility:hidden;overflow:hidden;border:0px;opacity:0.9;box-shadow: 2px 2px 10px #404040;');
            cmsbox.setAttribute('scrolling', 'no');
            document.body.appendChild(cmsbox);
        }
        //! get coordinates for editor box
        var rt,x,y,w,h,adj=0;
        var ww=(window.innerWidth?window.innerWidth:document.body.offsetWidth);
        var wh=(window.innerHeight?window.innerHeight:document.body.offsetHeight);
        //! if size forced, place box at icon's coordinates 
        if(icon.parentNode!=null && forcew<1 && forceh<1) {
            //! editor area should occupy the parent node's space
            var ps=window.getComputedStyle(icon.parentNode, null);
            var t=parseInt(ps.getPropertyValue("padding-top"),10); if(t==null||t==NaN) t=0;
            var l=parseInt(ps.getPropertyValue("padding-left"),10); if(l==null||l==NaN) l=0;
            var r=parseInt(ps.getPropertyValue("padding-right"),10); if(r==null||r==NaN) r=0;
            var b=parseInt(ps.getPropertyValue("padding-bottom"),10); if(b==null||b==NaN) b=0;
            rt=icon.parentNode.getBoundingClientRect();
            i=icon.getBoundingClientRect();
            x=Math.floor(i.left);
            y=Math.floor(i.top);
            w=icon.parentNode.offsetWidth-l-r; h=icon.parentNode.offsetHeight-t-b;
            this.item=icon.parentNode;
        } else {
            //! get coordinates for the icon
            rt=icon.getBoundingClientRect();
            x=Math.floor(rt.left);
            y=Math.floor(rt.top);
            w=1; h=1;
        }
        //! get forced position and dimensions
        if(forcew>0) w=forcew;
        if(forceh>0) h=forceh;
        //! adjust position and check minimum width, height
        if(w<100) w=100; if(h<24) h=24;
        if(minw>0 && w<minw) w=minw;
        if(minh>0 && h<minh) h=minh;
        //! if adjust is a number, move modal upwards
        if(typeof adjust == 'number') adj = adjust;
        if(typeof adjust == 'object') {
            //! if it's an array, then key is the minimum width
            for(var n in adjust)
                if(parseInt(n)==0 || parseInt(n)>w)
                    adj=parseInt(adjust[n]);
        }
        if(adj>0) {
            y-=adj;
            h+=adj;
        } else
            adj = 0;
        //! add space for Save button
        h+=28;
        //! make sure the box is on screen
        if(x<0) x=0; if(y<0) y=0;
        if(w>ww) w=ww;
        if(h>wh) h=wh;
        if(x+w>ww) x=ww-w; if(y+h>wh) y=wh-h;
        //! big modal?
        if(forcefull>0) {
            x=Math.round((100-forcefull)/2)+'%';
            y=Math.round((100-forcefull)/2)+'%';
            w=forcefull+'%';
            h=(Math.round(wh*forcefull/100)-1);
        } else {
            x+='px'; y+='px'; w+='px';
        }
        cmsbox.src='about:blank';
        //! make box and background visible
        cmsbg.style.visibility = 'visible';
        cmsbox.style.visibility = 'visible';
        //! set editor box position and size
        if(<?=empty(\PHPPE\Core::$core->noanim)?'false':'true'?> || typeof jQuery=='undefined'){
            cmsbox.style.left=x;
            cmsbox.style.top=y;
            cmsbox.style.width=w;
            cmsbox.style.height=h+'px';
            this.return=null;
        } else {
            rt=icon.getBoundingClientRect();
            cmsbox.style.left=Math.floor(rt.left)+'px';
            cmsbox.style.top=Math.floor(rt.top)+'px';
            cmsbox.style.width=icon.offsetWidth+'px';
            cmsbox.style.height=icon.offsetHeight+'px';
            this.return={left:cmsbox.style.left,top:cmsbox.style.top,width:icon.offsetWidth+'px',height:icon.offsetHeight+'px'};
            cmsbg.style.opacity=0.0;
            $(cmsbg).animate({opacity:0.4}, 200);
            $(cmsbox).animate({left:x,top:y,width:w,height:h+'px'},300);
        }
        //! load form into editbox during animation
        var d=new Date();
        cmsbox.src='<?=url("cms", "param")?>'+paramidx+'?height='+(h-28-adj)+'&adjust='+adj+'&scrx='+this.scrx+'&scry='+this.scry+'&serial='+d.getTime();
    },
    
    close:function(reload)
    {
        //! see if reditection requested
        this.reload=(reload!=null && reload!=false);
        //! scroll back page where it was
        window.scrollTo(0,0);
        window.scrollBy(this.scrx,this.scry);
        if(this.return==null || typeof jQuery=='undefined'){
            this.hideedit();
        } else {
            $('#cmsbg').animate({opacity:0.0}, 200);
            $('#cmsbox').animate(this.return, 300, this.hideedit);
        }
    },
    
    hideedit:function()
    {
        //! close editor box and hide background
        document.getElementById('cmsbg').style.visibility='hidden';
        document.getElementById('cmsbox').style.visibility='hidden';
        document.getElementById('cmsbox').src='about:blank';
        //! release lock. We do it synchronously on purpose
        var r = new XMLHttpRequest();
        r.open('GET', '<?=url("cms", "unlock")?>', true); r.send(null);
        pe.cms.item=null;
        //! reload page if requested
        if(pe.cms.reload)
            top.location.href=top.location.href;
    },
    
    init:function(scrx,scry)
    {
        //! if scroll position was saved in session before reloading page
        if(scry!=null) {
            window.scrollTo(0,0);
            window.scrollBy(scrx,scry);
        }
    },
    
    tablesearch:function(obj,id)
    {
        var r=new RegExp("("+obj.value+")+","i");
        var i,j,tbl=document.getElementById(id),pl=document.getElementById('pagelang');
        if(pl!=null&&pl.value!=null) pl=pl.value; else pl='';
        for(j=1;j<tbl.rows.length;j++){
            var ok=(tbl.rows[j].cells.length==1&&obj.value=='')||obj.value==''?1:0;
            var l=tbl.rows[j].getAttribute('data-lang');
            if(pl!=''&&l!=null&&pl!=l) ok=0;
            for(i=0;i<tbl.rows[j].cells.length;i++){
                if(tbl.rows[j].cells[i].getAttribute('data-skipsearch')) continue;
                tbl.rows[j].cells[i].innerHTML=tbl.rows[j].cells[i].innerHTML.replace("<ins>","").replace("</ins>","");
                if(tbl.rows[j].cells[i].innerHTML.match(r)) {
					var t=tbl.rows[j].cells[i].firstChild;
					while(t!=null) {
						if(t.innerHTML!=null&&t.innerHTML!=''){
							if(obj.value!='' && t.innerHTML.match(r)) {
								t.innerHTML=t.innerHTML.replace(r,"<ins>$1</ins>");
								ok=1;
							}
						}
						if(t.textContent!=null&&t.textContent!=''&&t.tagName==null) {
							if(obj.value!='' && t.textContent.match(r)) {
								t.textContent=t.textContent.replace(r,"<ins>$1</ins>");
								ok=1;
							}
						}
						t=t.nextSibling;
					}
                }
                tbl.rows[j].cells[i].innerHTML=tbl.rows[j].cells[i].innerHTML.replace("&lt;ins&gt;","<ins>").replace("&lt;/ins&gt;","</ins>");
            }
            if(pl!=''&&l!=null&&pl!=l) ok=0;
            tbl.rows[j].style.display=ok?'table-row':'none';
        }
    },
    
    pagedel:function(url)
    {
        if(confirm('<?=L("Are you sure you want to delete this page?")?>'))
            document.location.href='<?=url('cms','pages')?>?pagedel='+urlencode(url);
    },
    
	fieldset:function(evt)
	{
		evt.preventDefault();
		var d=document.getElementById('layout_fieldset');
		pe_p();
        if(<?=empty(\PHPPE\Core::$core->noanim)?'false':'true'?> || typeof jQuery=='undefined'){
			d.style.visibility=d.style.display=='none'?'block':'none';
		} else {
			$(d).fadeToggle();
		}
	},

	layoutdel:function(evt)
	{
		var np = document.getElementById('layout_numPages').value;
		if(np==0 || confirm(L("There are %d page references to this layout. Are you sure?").replace("%d",np))) {
			document.getElementById('layout_delete').value=1;
			return true;
		} else {
			evt.preventDefault();
			return false;
		}
	},

	image:function(evt,id) {
    	if(evt.target.className==null||evt.target.alt==null||evt.target.className!="wyswyg_icon")
			return;
    	pe.cms.tag=evt.target;
		pe.wyswyg.popup(event,id,'cms/tag?item='+urlencode(evt.target.alt));
	},

	settag:function(frm) {
		var t="",s=" ",i=0,inps=document.getElementById(frm).querySelectorAll("input, select, textarea");
		if(inps[0].value=="eval") inps[0].value="=";
		t=inps[i++].value+(inps[0].value!="="?" ":"");
		if(inps[0].value=="var"||inps[0].value=="field"||inps[0].value=="widget"||inps[0].value=="cms") {
			if(inps[3].value.trim()!='')
				t+="@"+inps[3].value.trim()+" ";
			t+=(inps[1].checked?inps[1].value:"")+inps[2].value;
			i=4;
			if(inps[i]!=null&&inps[i].value=="(") {
				t+="(";
				i++;
				s=",";
			} else
				t+=" ";
		}
		for(;i<inps.length;i++) {
			if(inps[i].value==")") {
				s=" ";
				t=t.replace(/[,]+$/,'');
			}
			t+=(inps[i].value==''&&s==' '?'-':(inps[i].value.indexOf(' ')>-1?"\""+inps[i].value.replace("\"","\\\"")+"\"":inps[i].value))+(inps[i+1]!=null&&inps[i+1].value!=")"?s:"");
		}
		t="<!"+t.trim().replace("( ","(").replace(" )",")").replace("()","").replace(/([\ ][\-])+$/,"")+">";
		pe.cms.tag.src='js/wyswyg.js.php?item='+urlencode(t);
		pe.cms.tag.alt=t;
	},

	checkall:function(obj) {
		var i;
		for(i=0;i<obj.form.elements.length;i++) {
			if(obj.form.elements[i].type=='checkbox')
				obj.form.elements[i].checked=obj.checked;
		}
	},

	divchoosemove:function(evt)
	{
    	var el=document.elementFromPoint(evt.clientX,evt.clientY);
		evt.preventDefault();
    	if(pe.cms.divchooselast!=el) {
        	if(pe.cms.divchooselast!=null)
            	pe.cms.divchooselast.setAttribute('style',pe.cms.divchooselast.getAttribute('data-style')?pe.cms.divchooselast.getAttribute('data-style'):'');
        	if(el!=null) {
            	el.setAttribute('data-style',el.getAttribute('style')?el.getAttribute('style'):'');
            	el.setAttribute('style','background:#108010;color:#fff;cursor:crosshair;');
        	}
        	pe.cms.divchooselast=el;
    	}
	},

	divchooseclick:function(evt)
	{
    	var el=document.elementFromPoint(evt.clientX,evt.clientY);
		evt.preventDefault();
    	document.location.href=document.location.href.replace(/\?chooseid=[0-9]+/g,"")+'?chooseid='+el.getAttribute('data-chooseid');
	},

    //! called by wyswyg
    getitem:function()
    {
        return pe.cms.item;
    }
};
