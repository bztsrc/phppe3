/*
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016 bzt
 */

pe.smenu = {
    bg:null,dragged:null,moved:false,lx:0,ly:0,url:null,cb:null,popupbg:null,popupdiv:null,

/**
 * Configuration object
 * background: background image name, or url starting with 'images/'
 * link: JavaScript function call or url, may contain @ID and @URL
 * callback: ajax call back url to refresh state, may contain @URL, should return a tabtext "id\tcssclass\tinfo\n" lines
 * refresh: refresh rate, min 333
 */
    init:function(conf)
    {
        this.bg = document.getElementById('content');
        if(this.bg==null) this.bg = document.body;
        if(conf.background!=null && conf.background!=''){
            if(conf.background.substr(0,6)!='images') conf.background='gallery/3/'+conf.background;
            this.bg.setAttribute('style','background-image:url(/'+conf.background+');background-repeat:no-repeat;background-size:100%;display:block;width:100%;height:100%;position:absolute;');
        }
        this.url=conf.url;
        var i,id,link,items = pe.smenu.bg.querySelectorAll('[data-smenu]');
        if(conf.link==null) conf.link="";
        if(conf.link.indexOf('@ID')==-1) {
            if(conf.link.substr(-1)!='/') conf.link+='/';
            conf.link+='@ID';
        }
        if(conf.link.substr(0,4)=='http'||conf.link.substr(0,1)=='/')
            conf.link='document.location.href="'+conf.link+'";';
        for(i=0;i < items.length;i++) {
            if(conf.edit==null) {
                items[i].setAttribute('onclick',conf.link.replace('@ID',urlencode(items[i].getAttribute('data-id'))).replace('@URL',urlencode(this.url)));
            } else {
                items[i].setAttribute('onclick','pe.smenu.edit(event,this);');
                items[i].setAttribute('onselectstart','return false;');
                items[i].setAttribute('onmousedown','pe.smenu.drag(event,"'+items[i].getAttribute('data-id')+'");');
                items[i].setAttribute('onmousemove','pe.smenu.move(event,"'+items[i].getAttribute('data-id')+'");');
                items[i].setAttribute('onmouseup','pe.smenu.drop(event,"'+items[i].getAttribute('data-id')+'");');
            }
        }
        this.recalc();
        document.body.onresize=this.recalc;
        if(conf.callback!=null && conf.callback.substr(0,4)=='http') {
            this.cb=conf.callback.replace('@URL',urlencode(this.url));
            this.refresh();
            if(Math.round(conf.refresh)>0)
                setInterval('pe.smenu.refresh();',Math.round(conf.refresh)<333?333:Math.round(conf.refresh));
        }
    },
    
    refresh:function()
    {
            var r = new XMLHttpRequest();
            r.open('GET', pe.smenu.cb, true);
            r.onload = function() {
                if(r.status==200 && r.responseText!="") {
                    var i,l=r.responseText.trim().split('\n');
                    for(i=0;i<l.length;i++) {
                        var f=l[i].split('\t'),t;
                        if(f[0]!=null && f[0]!="") {
                            var o=document.querySelector('[data-id='+f[0]+']');
                            if(o!=null && o.getAttribute('data-smenu')!=null) {
                                t=o.getAttribute('data-title')!=''?'<b>'+o.getAttribute('data-title')+'</b><br>':'';
                                o.className='smenu_item '+f[1];
                                o.innerHTML=t+f[2];
                            }
                        }
                    }
                }
            };
            r.send(null);
    },

    recalc:function()
    {
        var i,items = pe.smenu.bg.querySelectorAll('[data-smenu]');
        for(i=0;i < items.length;i++) {
            var pos=items[i].getAttribute('data-smenu').split(',');
            var x=Math.round(pe.smenu.bg.offsetWidth*Math.round(pos[0])/1000);
            var y=Math.round(pe.smenu.bg.offsetWidth*Math.round(pos[1])/1000);
            items[i].setAttribute('style','top:'+y+'px;left:'+x+'px;');
        }
    },
    
    edit:function(evt,obj)
    {
        if(pe.smenu.moved==true) {
            pe.smenu.moved=false;
            return;
        }
        if(pe.smenu.popupbg==null){
            pe.smenu.popupbg = document.createElement('DIV');
            pe.smenu.popupbg.setAttribute('style', 'position:fixed;display:table-cell;top:0px;left:0px;width:100%;height:100%;z-index:2001;background:#000;opacity:0.4;visibility:visible;');
            pe.smenu.popupbg.setAttribute('onclick', 'pe.smenu.close();');
            document.body.appendChild(pe.smenu.popupbg);
            pe.smenu.popupdiv = document.createElement('DIV');
            pe.smenu.popupdiv.setAttribute('class', 'smenu_popupdiv');
            pe.smenu.popupdiv.setAttribute('style', 'position:fixed;display:table-cell;top:30%;left:30%;width:40%;height:200px;z-index:2002;background:rgba(64,64,64,0.9) !important;visibility:visible;overflow:hidden;border:0px;opacity:0.9;');
            pe.smenu.popupdiv.setAttribute('scrolling', 'no');
            document.body.appendChild(pe.smenu.popupdiv);
            var l=document.createElement('link');
            l.rel='stylesheet'; l.type='text/css'; l.media='all'; l.href=url('css','smenu_edit.css');
            document.getElementsByTagName('head')[0].appendChild(l);
        }
        pe.smenu.popupdiv.innerHTML="<h3>"+L("Modify menu")+"</h3>ID: "+evt.target.getAttribute('data-id')+"<input type='hidden' class='input' name='id' value='"+evt.target.getAttribute('data-id')+"'><br>"+
        "<label>"+L("Name")+"<br><input type='text' class='input' name='title' value='"+evt.target.getAttribute('data-title')+"'></label>"+
        "<label>"+L("Type")+"<br><input type='text' class='input' name='type' value='"+evt.target.className.replace('smenu_item ','')+"'></label>"+
        "<br><br><label><input type='button' class='button' value='"+L("Delete")+"' onclick='pe.smenu.del(\""+evt.target.getAttribute('data-id')+"\");'></label>"+
        "<label><input type='button' class='button' value='"+L("Save")+"' onclick='pe.smenu.save(this);'></label>";
        pe.smenu.popupbg.style.visibility='visible';
        pe.smenu.popupdiv.style.visibility='visible';
    },
    del:function(id)
    {
        if(id!=null&&id!="") {
            var r = new XMLHttpRequest();
            r.open('POST', url('smenu','del'), true);
            r.setRequestHeader('Content-type','application/x-www-form-urlencoded');
            r.onload = function() {
                if(r.status==200) {
                    if(r.responseText=="OK")
                        document.location.href='/'+pe.smenu.url;
                    else
                        alert(r.responseText);
                }
            };
            r.send('url='+pe.smenu.url+'&id='+id);
        }
    },
    save:function(btn)
    {
        var form=btn.parentNode.parentNode.querySelectorAll("[class=input]");
        if(form[0].value!=null&&form[0].value!="") {
            var r = new XMLHttpRequest();
            r.open('POST', url('smenu','edit'), true);
            r.setRequestHeader('Content-type','application/x-www-form-urlencoded');
            r.onload = function() {
                if(r.status==200) {
                    if(r.responseText=="OK")
                        document.location.href='/'+pe.smenu.url;
                    else
                        alert(r.responseText);
                }
            };
            r.send('url='+pe.smenu.url+'&id='+form[0].value+'&title='+urlencode(form[1].value)+
                '&type='+urlencode(form[2].value));
        }
    },
    close:function()
    {
        pe.smenu.popupbg.style.visibility='hidden';
        pe.smenu.popupdiv.style.visibility='hidden';
    },
    drag:function(evt,id)
    {
        pe.smenu.dragged=id;
        pe.smenu.lx=evt.clientX;pe.smenu.ly=evt.clientY;
        evt.target.setAttribute('data-pos',evt.target.style.left.replace('px','')+','+evt.target.style.top.replace('px',''));
    },
    move:function(evt,id)
    {
        if(pe.smenu.dragged==null)
            return;
        pe.smenu.moved=true;
        var pos=evt.target.getAttribute('data-pos').split(',');
        var x=Math.round(pos[0])+evt.clientX-pe.smenu.lx;
        var y=Math.round(pos[1])+evt.clientY-pe.smenu.ly;
        if(x<0) x=0; if(y<0) y=0;
        if(x>=pe.smenu.bg.offsetWidth-evt.target.offsetWidth) x=pe.smenu.bg.offsetWidth-evt.target.offsetWidth;
        if(y>=pe.smenu.bg.offsetHeight-evt.target.offsetHeight) y=pe.smenu.bg.offsetHeight-evt.target.offsetHeight;
        evt.target.setAttribute('data-pos', x+','+y);
        if(evt.shiftKey){
            x=Math.round(x/10)*10;
            y=Math.round(y/10)*10;
        }
        evt.target.setAttribute('style','top:'+y+'px;left:'+x+'px;cursor:move;');
        pe.smenu.lx=evt.clientX;pe.smenu.ly=evt.clientY;
    },
    drop:function(evt,id)
    {
        pe.smenu.dragged=null;
        if(pe.smenu.moved==false)
            return;
        var x=Math.round(Math.round(evt.target.style.left.replace('px',''))*1000/pe.smenu.bg.offsetWidth);
        var y=Math.round(Math.round(evt.target.style.top.replace('px',''))*1000/pe.smenu.bg.offsetWidth);
        evt.target.setAttribute('data-smenu', x+','+y);
        var r = new XMLHttpRequest();
        r.open('POST', url('smenu','move'), true);
        r.setRequestHeader('Content-type','application/x-www-form-urlencoded');
        r.send('url='+pe.smenu.url+'&id='+evt.target.getAttribute('data-id')+'&x='+x+'&y='+y);
        evt.target.style.cursor='pointer';
    },
    
    add:function()
    {
        var id=prompt(L("Name"));
        if(id!=null&&id!="") {
            var r = new XMLHttpRequest();
            r.open('POST', url('smenu','add'), true);
            r.setRequestHeader('Content-type','application/x-www-form-urlencoded');
            r.onload = function() {
                if(r.status==200) {
                    if(r.responseText=="OK")
                        document.location.href='/'+pe.smenu.url;
                    else
                        alert(r.responseText);
                }
            };
            r.send('url='+pe.smenu.url+'&id='+id);
        }
    }
};
