<?php
use PHPPE\Core as Core;

//check user access
if( !Core::$user->has("install") )
	die( "403" );

Core::$core->nocache = true;

header("Content-type:text/javascript;charset=utf8");
//error_reporting(0);
?>/*
 *   PHP Portal Engine v3.0.0
 *   https://github.com/bztsrc/phppe3/
 *
 *   Copyright LGPL 2016 bzt
 *
 *   PHPPE Extension Manager
 *
 */
pe.extensions = {
/*PRIVATE VARS*/
    installed:[ <?=Core::lib("Extensions")->getInstalled()?> ],
    pkgs:[ <?=Core::lib("Extensions")->getPkgs()?> ],
    licenses:[],
    tmr:null, me:false, lastcmd:"", param:"",return:"",url:"",

/*PUBLIC METHODS*/
    search:function(str,installed)
    {
        var t="",up=false,down=false;
        if(str==""||str==null||str==undefined)str=document.getElementById("search").value;
        if(str=="<?=L("installed")?>"<?=(L("installed")!="installed"?"||str==\"installed\"":"")?>){str="";installed=true;}
        if(str=="<?=L("upgrade")?>"<?=(L("upgrade")!="upgrade"?"||str==\"upgrade\"":"")?>){str="";up=true;}
        if(str=="<?=L("downgrade")?>"<?=(L("downgrade")!="downgrade"?"||str==\"downgrade\"":"")?>){str="";down=true;}
        if(this.tmr) { clearTimeout(this.tmr); this.tmr=null; }
        var cat="",r=(str!=null && str!=undefined && str!="" ? new RegExp(str.replace(".","\\.").replace("*","\\*").replace("+","\\+").replace("|","\\|"),"i") : null);
        for(i=0;i<this.pkgs.length;i++) {
            if((installed==true && !this.pkgs[i].installed) ||
               (r!=null &&
               !(this.pkgs[i].name.match(r)||this.pkgs[i].version.match(r)||this.pkgs[i].desc.match(r)||this.pkgs[i].maintainer.match(r)||this.pkgs[i].license.match(r)||this.pkgs[i].url.match(r))
               )
            ) continue; else
            if(up==true && (!this.pkgs[i].installed ||
               this.pkgs[i].installed>=this.pkgs[i].version)
            ) continue; else
            if(down==true && (!this.pkgs[i].installed ||
               this.pkgs[i].installed<=this.pkgs[i].version)
            ) continue;
            try{
            if(up==false && down==false && !document.getElementById('license_filter'+this.licenses.indexOf(this.pkgs[i].license)).checked) continue;
            }catch(e){}
            if(this.pkgs[i].category!=null && this.pkgs[i].category!=cat) {
                cat=this.pkgs[i].category;
                t+="<br style='clear:both;'><b>"+L(cat)+"</b></a><br style='clear:both;'>"
            }
            if(Math.floor(this.pkgs[i].price)!=0)
                this.pkgs[i].homepage=(this.pkgs[i].homepage? this.pkgs[i].homepage : "https://phppe.org/"+this.pkgs[i].id.split("/")[1] );
            t+=
            "<div class='col-lg-4'>"+
                "<div class='extension well well-sm' dir='ltr'>"+(this.pkgs[i].homepage?"<a href='"+this.pkgs[i].homepage+"' target='_new'>":"")+
                "<img class='preview' title='"+this.pkgs[i].url+"' src='data:image/png;base64,"+(this.pkgs[i].preview?this.pkgs[i].preview:'iVBORw0KGgoAAAANSUhEUgAAAIAAAAB5CAMAAADGfdkoAAAAUVBMVEUAAADDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8PDw8OOFo/iAAAAGnRSTlMA+Mgje5u258wWvleUTwtvrUQspPCGYTne1lzKNFwAAAPaSURBVGje7ZrJlqsgEEALBAdUBFHU+v8PfadHTToRRAlvkbvJok8nF6kJEoiC1MzkVKim6yEBVuDKYCZ4LR3BO5ZXKkwFPkBJeBEdPmZ4USyM+JSXbEOHzyEZRGfCPShEp8RdGESmxn2G2Kkg0EEH8ZASMnSRQxx6JophWCi6KKLsQU/RmxiZ2OAB+FTBxeR4kLK71EFhALyKXnpflY8VwUCEjNv73AyXJMSC4ZAeTtPjGYoqbvNxI4IXbhnrWhmUA+fbczUWPwvILJ4kO/7xOa6QAU9iDn8+QwdxHkE9svani8hM112TL+vq40eBLklBzWh1v0kcmU0ta/KyiJ8I/WbXZ2HGrt2KQDXVncBQ+rByR2aqmq1IcGxYj8DbK6ezUOO0zqNRgkC73+REX27AifXayBbD4Oda7rqMOp6A8komFm8LSq+KyuMFYeH1LgbDqE9k4cr0WY/atUJfORZN6EZvK7TuGkWL60qxRQ+KJW+6m1ZRacu4cHqwgCzcL4zbAv27MTPBJ1TgICy8Z8GZ1dnGo9dNYBUAgcEUHx1LZ3KdYEOurWY8y7Dk3eOCwiCgF4YPfyZoGpnwGqa/Nb2Qh88eM+eUBA8ey97hUEqPLGRfiWV5edyiBTncrv/m8+0wiLF9FJNs+P2PaZvenZoPzt/93vFcfT9ixfS9hextk8+fNaPqb+NTM7X4ZsLtyEIsbLn948I7Lf8eB1vQAxb5WN8aZvWoiqMTwyhd5Wag3P7pU/QnFPOxre70hHvwUd+Lp1Z63vXM+SYs7vN4UUxvLdrCdSfEqDKcaXmw3syq09WTkkAW3v1aVMtuEpwr+SW3k3wynBHa1F8Gw6kDkHXGsRifSs6t4wBBJLiQeIp6f3wtwU1+/qBfdWYJv4ho8RTqp3k93KcRPCB4Crn3KC14YE5Gwd5KJvBAX3Xpx4/NnivDyT14Hk0zuFnNT991ZI7jR/w9KBzTd6w96Ne2eQe78uunxfDHGP0bBPyO/rrbd1JDTGaP6Toqo8+NjmVumg/GzQtv2kv2oAVool4LL26B0vvLGIrfveHrrUvwgLkLvkA3FP4KGAoeZOcF1tIr8PvM8aVDr/gNRu05uFBjlCGojFJm+HgxuWc57EIF3DTgQ+UUmJcsCMzBC+ESKCgE4StgnQJlXAGZWgBEaoE6tYBMLQAqtUCbWkCSxAJgUgvo1AIwpBJwj4ZtVIEVmloAeGoBqGq+JBNYJRpKEgmsEu0oSEKBVSKZwHo1LwZEm0BgRepxSiSw8hZ4C7wF3gJvgbfAW+At8Bb4HwRoYoEZSRCXCdSqpAGURsMT/gG5cUM2oGYBXgAAAABJRU5ErkJggg==')+
                "' style='"+(LANG['rtl']!=null&&LANG['rtl']!=''&&LANG['rtl']!=false?"padding-left:16px;":"")+"float:left;width:96px !important;' alt='"+this.pkgs[i].id+"'>"+(this.pkgs[i].homepage?"</a>":"")+
                "<div style='padding-left:100px;'"+(LANG['rtl']!=null&&LANG['rtl']!=''&&LANG['rtl']!=false?" dir='rtl'":"")+">"+
                (this.pkgs[i].homepage?"<a href='"+this.pkgs[i].homepage+"' target='_new'>":"")+this.pkgs[i].name+" "+this.pkgs[i].version+(this.pkgs[i].homepage?"</a>":"")+
                (this.pkgs[i].installed?" <span class='installed' style='color:green;'>(<?=L("installed")?> "+this.pkgs[i].installed+")</span>":"")+"<br>";
            if( this.pkgs[i].url != undefined ) {
                var u="",s=this.pkgs[i].size;
                if(s>1024*1024) { u="M"; s=Math.round(s/1024/1024); }
                if(s>1024) { u="K"; s=Math.round(s/1024); }
                t+="<small dir='ltr' class='details' style='font-style:italic;'>"+this.pkgs[i].id+" ("+s+" "+u+"b, "+L(this.pkgs[i].license)+", "+this.pkgs[i].maintainer+")</small><br>";
                if(Math.floor(this.pkgs[i].price)!=0)
                t+="<button class='btn btn-warning glyphicon glyphicon-shopping-cart' title='<?=L("Buy")?>' onclick='window.open(\""+this.pkgs[i].homepage+"\");' ></button>&nbsp;&nbsp;&nbsp;";
                if(!this.pkgs[i].installed) {
                    t+="<button onclick='pe.extensions.cmd(this,\"install\","+i+");' class='btn btn-default glyphicon glyphicon-arrow-down' title='<?=L("Install")?>' style='"+(Math.floor(this.pkgs[i].price)!=0?"background:#B0B0F0 linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%);":"")+"'><span></span></button>";
                } else if(this.pkgs[i].installed<this.pkgs[i].version) t+="<button onclick='pe.extensions.cmd(this,\"install\","+i+");' class='btn btn-warning'><?=L("Upgrade to")?> "+this.pkgs[i].version+"</button>";
                else if(this.pkgs[i].installed>this.pkgs[i].version) t+="<button onclick='if(confirm(\"<?=L("sure")?>\"))pe.extensions.cmd(this,\"install\","+i+");' class='btn btn-info'><?=L("Downgrade to")?> "+this.pkgs[i].version+"</button>";
                else t+="<button onclick='pe.extensions.cmd(this,\"install\","+i+");' class='btn btn-default glyphicon glyphicon-repeat' title='<?=L("Reinstall")?>'></button>";
                if(this.pkgs[i].config!="" && window.XMLHttpRequest && (this.pkgs[i].installed||this.pkgs[i].id=="phppe/Core")) t+="&nbsp;<button onclick='pe.extensions.conf(this,"+i+");' title='<?=L("Configure")?>' class='btn btn-default glyphicon glyphicon-cog'></button>";
                if(this.pkgs[i].installed) {
                    t+="&nbsp;&nbsp;&nbsp;<button onclick='if(confirm(\"<?=L("sure")?>\"))pe.extensions.cmd(this,\"uninstall\","+i+");'  title='<?=L("Remove")?>' class='btn btn-danger glyphicon glyphicon-trash'></button>";
                }
            }
            t+="</div><br style='clear:both;'>";
            t+="<small class='desc'>"+(this.pkgs[i].desc?this.pkgs[i].desc.replace(/\b(http[^\'\"\ \t\r\n\;\,!\<\>]+)/,"<a href='$1' target='_new'>$1</a>"):'<?=L("No description")?>')+"<br>";
            try {
                        var j,k,l;
                        for(j=0;j<this.installed.length;j++) {
                            if(this.installed[j].id==null) continue;
                            var idx=this.pkgs[i].depends.indexOf(this.installed[j].id);
                            if(idx>-1) this.pkgs[i].depends.splice(idx,1);
                        }
                        if(this.pkgs[i].depends.length>0)
                            t+="<small class='desc' style='color:"+(this.pkgs[i].installed?"red":"#808080")+";'>"+L(this.pkgs[i].installed?"Failed dependency":"Also installs")+": "+this.pkgs[i].depends.join(", ")+"</small><br>";
            } catch(e) {}
            t+="<small style='color:#808080;'>"+this.pkgs[i].time+"   "+this.pkgs[i].sha1+"</small></small></div></div></div>";
        }
        if(t=="") t="<br style='clear:both;'><i><?=L("No match found.")?></i>";
        document.getElementById('pkgs').innerHTML=t;
        return true;
    },
    
    conf:function(obj,i)
    {
        var waserr="",t="",tabs="",p="",cfg=new Array(),was=new Array(),wasinp=false;
        if(i==-1 || this.pkgs[i].config=="") return;
        var url="<?=url("extensions")?>getconf?item="+encodeURIComponent(this.pkgs[i].id);
        if( window.XMLHttpRequest ) {
            var r = new XMLHttpRequest();
            r.open('GET', url, false); r.send(null);
            try {
            if(r.status==200) cfg=JSON.parse(r.responseText);
            else alert('HTTP-E: '+r.status+' '+url);
            } catch(e) {
                if(r.responseText!=null&&r.responseText!=undefined&&r.responseText!='')
                    waserr=r.responseText;
                cfg=new Array();
            }
        } else return;
    
        var first=null;for(p in this.pkgs[i].config){first=p;break;}
        var isstr=typeof this.pkgs[i].config[first] == 'string';
        var carr=isstr?{"Basic":this.pkgs[i].config}:this.pkgs[i].config;
        var tn=0;
        for (var tab in carr) {
            tabs+="<b class='conftab"+(tab==first?"_active":"")+"' onclick='pe.extensions.conftab("+i+","+tn+");'>"+(this.pkgs[i].conf.hasOwnProperty(tab)?this.pkgs[i].conf[tab]:tab)+"</b>";
            t+="<table class='conftable' id='conftable"+(tn++)+"'"+(tab==first||isstr?"":" style='display:none;'")+">";
            for(p in carr[tab]) {
                var m=carr[tab][p].match(/^(\*)?([^\(\*]+)\(?([^\)]*)/);
                var a=m[3]!=null?m[3].split(','):new Array();
                if(m[2]==null) continue;
                was[p]=1;
                wasinp=true;
                    t+="<tr><td>"+(this.pkgs[i].conf.hasOwnProperty(p)?this.pkgs[i].conf[p]:p)+":</td><td width='100%' title='"+(this.pkgs[i].help.hasOwnProperty(p)?this.pkgs[i].help[p]:'')+"'>";
                switch(m[2]) {
                    case "select":
                        t+="<select name='"+p+"'>";
                        for(var j=0;j < a.length;j++) {
                            var d = a[j].split("=");
                            var v = d[1]!=null?d[0]:a[j];
                            var n = d[1]!=null?d[1]:a[j];
                            t+="<option value='"+v+"'"+(cfg[p]!=null&&cfg[p]==v?" selected":"")+">"+(this.pkgs[i].conf.hasOwnProperty(n)?this.pkgs[i].conf[n]:n)+"</option>";
                        }
                        t+="</select>";
                        break;
                    case "integer":
                        t+="<input type='number' min='"+a[0]+"' max='"+a[1]+"' value='"+a[2]+"'>";
                        break;
                    case "boolean":
                    case "notboolean":
                        var bv=(cfg[p]!=null?(Math.floor(cfg[p])==1||cfg[p]=="true"?"true":"false"):(Math.floor(a[0])==1||a[0]=="true"?"true":"false"));
                        if(function_exists("pe.boolean.open")) {
                            t+="<span dir='ltr'>"+pe.boolean.open(p,bv,m[2],a)+"</span>";
                        } else {
                            var t1="<option value=''>"+(a[2]!=null?a[2]:L('No'))+"</option>";
                            var t2="<option value='"+(a[0]!=null?a[0]:'true')+"'>"+(a[1]!=null?a[1]:L('Yes'))+"</option>";
                            t+="<select name='"+p+"'>";
                            if(m[2]=='boolean')
                                t+=t1+t2;
                            else
                                t+=t2+t1;
                            t+="</select>";
                        }
                        break;
                    case "textarea":
                        t+="<textarea name='"+p+"' style='width:98%' rows='3' wrap='virtual'>"+(cfg[p]!=null?cfg[p]:(m[1]?m[3]:""))+"</textarea><br>";
                        break;
                    case "url":
                        t+="<span dir='ltr'><small>http://("+L("remote base")+")/</small><input type='text' name='"+p+"' style='width:33%;' value='"+(cfg[p]!=null?cfg[p]:(m[1]?m[3]:""))+"' placeholder='"+m[3]+"'></span>";
                        break;
                    case "string":
                    default:
                        if(function_exists("pe."+m[2]+".open")) {
                            t+="<span dir='ltr'>"+eval("pe."+m[2]+".open(p,(cfg[p]!=null?cfg[p]:(m[1]?m[3]:'')),m[3])")+"</span>";
                        } else {
                            t+="<input type='text' name='"+p+"' style='width:98%;' value='"+(cfg[p]!=null?cfg[p]:(m[1]?m[3]:""))+"' placeholder='"+m[3]+"'>";
                        }
                        break;
                };
                t+="</td></tr>\n";
            }
            t+="</table>";
        }
        if(t==""||!wasinp) return;
        t="<table id='confhdr"+i+"' width='95%'><tr><td colspan='2' align='center'><h2>"+this.pkgs[i].name+"</h2></td></tr>"+(waserr!=""?"<tr><td colspan='2' style='color:#FEA0A0;background:rgba(128,0,0,0.6);'>"+waserr+"</td></tr>":"")+"<tr><td>"+(isstr?'':tabs)+"</td><td><input type='button' class='button' style='float:"+(LANG['rtl']!=null&&LANG['rtl']!=''&&LANG['rtl']!=false?'left':'right')+";' value='<?=L("Save")?>'onclick='pe.extensions.saveconf("+i+");'></td></tr></table>"+t+"<table width='95%'><tr><td>";
        for(p in cfg)
            if(was[p]==null) t+="<input type='hidden' name='"+p+"' value='"+(cfg[p]!=null?cfg[p]:"")+"'>";
        t+="</td><td><input type='button' class='button' style='float:"+(LANG['rtl']!=null&&LANG['rtl']!=''&&LANG['rtl']!=false?'left':'right')+";' value='<?=L("Save")?>'onclick='pe.extensions.saveconf("+i+");'></td></tr></table>";
        document.getElementById('statusbg').style.visibility='visible';
        document.getElementById('status').innerHTML=t;
        document.getElementById('statusbg').style.visibility='visible';
        if(<?=empty(Core::$core->noanim)?'false':'true'?> || typeof jQuery=='undefined'){
            document.getElementById('status').style.visibility='visible';
        } else {
            var r=obj.getBoundingClientRect();
            var a=obj,x=r.left,y=r.top;
            document.getElementById('status').style.left=x+'px';
            document.getElementById('status').style.top=y+'px';
            document.getElementById('status').style.width=obj.offsetWidth+'px';
            document.getElementById('status').style.height=obj.offsetHeight+'px';
            document.getElementById('status').style.visibility='visible';
            this.return={left:x+'px',top:y+'px',width:obj.offsetWidth+'px',height:obj.offsetHeight+'px'};
            $('#status').animate({left:'20%',top:'20%',width:'60%',height:'60%'},500);
        }
        this.lastcmd='conf';
    },
    
    conftab:function(i,tn)
    {
        var hdr=document.getElementById('confhdr'+i);
        var b=hdr.getElementsByTagName("B");
        for(var j in b)
            b[j].className="conftab"+(j==tn?"_active":"");
        var j=0,tabs=hdr.nextSibling;
        while(tabs.className!=null && tabs.className.indexOf("conftable")>-1) {
            tabs.style.display=j++==tn?"block":"none";
            tabs = tabs.nextSibling;
        }
    },
    
    walk:function(n, f)
    {
        f(n);
        n = n.firstChild;
        while(n) {
            pe.extensions.walk(n,f);
            n = n.nextSibling;
        }
    },
    
    saveconf:function(i)
    {
        var t="",i;
        var url="<?=url("extensions")?>setconf?item="+encodeURIComponent(pe.extensions.pkgs[i].id);
        if( window.XMLHttpRequest ) {
            var r = new XMLHttpRequest();
            pe.extensions.param="";
            pe.extensions.walk(document.getElementById('status').firstChild.nextSibling, function(n) {
                    if(n.tagName=="INPUT" && n.type!="button") {
                        pe.extensions.param+=(pe.extensions.param?"&":"")+n.name+"="+encodeURIComponent(n.value);
                    }
                    if(n.tagName=="SELECT") {
                        pe.extensions.param+=(pe.extensions.param?"&":"")+n.name+"="+encodeURIComponent(n.value);
                    }
                    if(n.tagName=="TEXTAREA") {
                        pe.extensions.param+=(pe.extensions.param?"&":"")+n.name+"="+encodeURIComponent(n.value);
                    }
            });
            r.open('POST', url, false);
            r.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            r.send(pe.extensions.param);
            t=r.status==200?r.responseText:'HTTP-E: '+r.status+' '+url;
        }
        pe.extensions.exitcmd();
        if(t) alert(t);
    },
    
    cmd:function(obj,cmd,i)
    {
        var t="";
        pe.cookie.set('extensions_scroll',((window.pageYOffset||document.documentElement.scrollTop)-(document.documentElement.clientTop||0))+'');
        if( cmd=="uninstall" ) {
            var bn=pe.extensions.pkgs[i].url.split('/').pop().split('.')[0];
            pe.extensions.me=(bn=="PHPPE_extmgr");
            if(pe.extensions.me && !confirm("<?=L("You are going to remove the Extension Manager itself!")."\\n".L("sure")?>") ) return;
        } else
            pe.extensions.me=false;
    
        if( i!=-1 && Math.floor(pe.extensions.pkgs[i].price)!=0) t=prompt("<?=L("Product key?")?>" );
        if( i==-1 || Math.floor(pe.extensions.pkgs[i].price)==0 || (t!=null && t!=undefined && t!="") ) {
            pe.extensions.url="<?=url("extensions")?>"+cmd+(i!=-1?"?item="+encodeURIComponent(pe.extensions.pkgs[i].url+(t?"&key="+Sha256.hash(t):"")+"#"+pe.extensions.pkgs[i].id):"");
            pe.extensions.lastcmd=cmd;
            document.getElementById('statusbg').style.visibility='visible';
            if(<?=empty(Core::$core->noanim)?'false':'true'?> || typeof jQuery=='undefined'){
                document.getElementById('status').style.visibility='visible';
                pe.extensions.loadmodal();
            } else {
                var r=obj.getBoundingClientRect();
                var a=obj,x=r.left,y=r.top;
                var ww=(window.innerWidth?window.innerWidth:document.body.offsetWidth);
                var wh=(window.innerHeight?window.innerHeight:document.body.offsetHeight);
                document.getElementById('status').setAttribute('style','left:'+x+'px;top:'+y+'px;width:'+obj.offsetWidth+'px;height:'+obj.offsetHeight+'px;position:fixed;visibility:visible;');
                pe.extensions.return={left:x+'px',top:y+'px',width:obj.offsetWidth+'px',height:obj.offsetHeight+'px'};
                x=Math.floor(/*(window.pageXOffset?window.pageXOffset:document.body.scrollLeft)+*/ww*0.2);
                y=Math.floor(/*(window.pageYOffset?window.pageYOffset:document.body.scrollTop)+*/wh*0.2);
                $('#status').animate({left:x+'px',top:y+'px',width:'60%',height:'60%'},750,pe.extensions.loadmodal);
            }
        }
    },
    
    loadmodal:function()
    {
            var t;
            if( window.XMLHttpRequest ) {
                var r = new XMLHttpRequest();
                r.open('GET', pe.extensions.url, false); r.send(null);
                t=r.status==200?r.responseText:'HTTP-E: '+r.status+' '+pe.extensions.url;
                document.getElementById('status').style.color=(t.substr(0,7)!="PHPPE-I"&&t.substr(0,5)!="DIAG-"?'#FF6060':'#60FF60');
                document.getElementById('status').innerHTML=t;
            } else
                document.getElementById('status').src=pe.extensions.url;
    },
    
    hidestatus:function()
    {
        document.getElementById('status').style.visibility='hidden';
        document.getElementById('status').innerHTML='<?=L("Connecting to host")?>...';
        if(pe.extensions.lastcmd!='conf')
            document.location.href=(pe.extensions.me?"<?=url("/")?>":"<?=url("extensions")?>");
    },
    
    exitcmd:function()
    {
        document.getElementById('statusbg').style.visibility='hidden';
        if(<?=empty(Core::$core->noanim)?'false':'true'?> || typeof jQuery=='undefined'){
            pe.extensions.hidestatus();
        } else {
            $('#status').animate(pe.extensions.return,750,pe.extensions.hidestatus);
        }
    },
    
    searchbg:function()
    {
        if(pe.extensions.tmr) { clearTimeout(pe.extensions.tmr); pe.extensions.tmr=null; }
        pe.extensions.tmr = setTimeout("pe.extensions.search(document.getElementById('search').value);",500);
    },
    
    init:function()
    {
        var i,j,t,ctx=document.getElementById('content'),sc=pe.cookie.get('extensions_scroll'),cnt=0;
        for(i=0;i<this.pkgs.length;i++) {
            if(this.licenses.indexOf(this.pkgs[i].license)==-1)
                this.licenses.push(this.pkgs[i].license);
            for(j=1;j<this.installed.length;j++) {
                if(this.pkgs[i].id==this.installed[j].id) {
                    this.installed[j].idx=i;
                    this.pkgs[i].installed=this.installed[j].version;
                    if(this.pkgs[i].installed<this.pkgs[i].version) cnt++;
                }
            }
        }
        for(j=1;j<this.installed.length;j++)
            if(this.installed[j].idx==null||this.installed[j].idx==undefined)
                this.pkgs.push({"id":this.installed[j].id,"name":"NA "+this.installed[j].id,"version":"?.?.?","installed":(this.installed[j].version!=undefined&&this.installed[j].version!=""?this.installed[j].version:"?.?.?"),"desc":"*<?=L("installed, but not in repo")?>*"});
        t="<div id='extensions_panel'>"+
            "<div class='row'>"+
                "<div class='col-md-6'>"+
                    "<b>"+(this.installed[0]!=null?this.installed[0].name:"<?=L("No name")?>")+"</b><br><small>&nbsp;&nbsp;<?=(!empty(Core::$user->data['remote']['user'])?Core::$user->data['remote']['user']."@":"").Core::$user->data['remote']['host'].(!empty(Core::$user->data['remote']['path'])?":".Core::$user->data['remote']['path']:"")?></small>"+
                "</div>"+
                "<div class='col-md-6'>"+
                    "<input id='search' type='text' class='form-control' onkeyup='return pe.extensions.searchbg();'  placeholder='<?=L("Search")?>'>"+
                "</div>"+
            "</div>"+
            "<div class='row'>"+
                "<div class='col-sm-3'>"+
                    "<button style='margin:5px;line-height:20px;width:95%;' onclick='document.getElementById(\"search\").value=\"\";pe.extensions.search();'><?=L("All")?> <small dir='ltr'>("+this.pkgs.length+")</small></button>"+
                "</div>"+
                "<div class='col-sm-3'>"+
                    "<button style='margin:5px;line-height:20px;width:95%;' onclick='document.getElementById(\"search\").value=\"<?=L("installed")?>\";pe.extensions.search();'><?=L("Installed")?> <small dir='ltr'>("+(this.installed.length-1)+")</small></button>"+
                "</div>"+
                "<div class='col-sm-3'>"+
                    "<button id='upbtn' style='margin:5px;line-height:20px;width:95%;"+(cnt>0?"color:green;background:#B0F0B0 linear-gradient(to bottom,rgba(0,0,0,0.5) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%);":"")+"' onclick='document.getElementById(\"search\").value=\"<?=L("upgrade")?>\";pe.extensions.search();'><?=L("Upgrade")?> <small dir='ltr'>("+cnt+")</small></button>"+
                "</div>"+
                "<div class='col-sm-3'>"+
                    "<button style='margin:5px;line-height:20px;width:95%;' onclick='pe.extensions.cmd(this,\"bootstrap\",-1);'><?=L("Diagnostics")?></button>"+
                "</div>"+
            "</div>"+
            "<div class='row'>"+
                "<div class='text-center'>";
        for(i=0;i<this.licenses.length;i++){
            t+="<span><input class='input' type='checkbox' name='license_filter"+i+"' id='license_filter"+i+"' value='"+this.licenses[i]+"' checked onchange='pe.extensions.search(document.getElementById(\"search\").value);'><label for='license_filter"+i+"'><small style='padding-"+(LANG['rtl']!=null&&LANG['rtl']!=''&&LANG['rtl']!=false?"left":"right")+":8px;'>"+L(this.licenses[i])+"</small></label></span>";
        }
        t+="</div></div></div>";
        t+="<div id='pkgs'></div>";
        t+="<div id='statusbg' onclick='pe.extensions.exitcmd();'></div>";
        if (window.XMLHttpRequest)
            t+="<pre id='status'><?=L("Connecting to host")?>...</pre>";
        else
            t+="<iframe id='status' src='data:text/plain,<?=urlencode(L("Connecting to host"))?>...'></iframe>";
        t+="<br style='clear:both;'>";
        if(ctx==null||ctx==undefined) ctx=document.body;
        ctx.innerHTML=t;
        this.search();
        document.getElementById('search').focus();
        if(sc>0) {
            window.scrollTo(0,0);
            window.scrollBy(0,sc);
            pe.cookie.set('extensions_scroll','',-1);
        }
        if ( window.addEventListener )
            window.addEventListener( "scroll", this.header, false );
        else if ( window.attachEvent )
            window.attachEvent( "onscroll", this.header );
        else
            window["onscroll"]=this.header;
        document.getElementById('status').style.visibility='hidden';
        document.getElementById('statusbg').style.visibility='hidden';
    },
    
    header:function(evt)
    {
        var scrx=window.pageXOffset?window.pageXOffset:document.body.scrollLeft,scry=window.pageYOffset?window.pageYOffset:document.body.scrollTop;
        var ep,t,ow=0,oh=0,ox=0,oy=0;
        ep=document.getElementById('extensions_panel');
        if(ep==null) return;
        t=ep;
        ow=t.offsetWidth;
        oh=t.offsetHeight;
        if(ep.getAttribute("data-x")!=null) {
            ox=ep.getAttribute("data-x");
            oy=ep.getAttribute("data-y");
        } else {
            while (t.offsetParent) {
                ox += t.offsetLeft - t.scrollLeft ;//+ t.clientLeft;
                oy += t.offsetTop - t.scrollTop ;//+ t.clientTop;
                t = t.offsetParent;
            }
            ox-=Math.floor((window.pageXOffset?window.pageXOffset:document.body.scrollLeft));
            oy-=Math.floor((window.pageYOffset?window.pageYOffset:document.body.scrollTop));
            ep.setAttribute("data-x",ox);
            ep.setAttribute("data-y",oy);
        }
        if(scry>oy-(pe_ot>0?pe_ot:0) || scrx>ox) {
            ep.setAttribute('style','position:fixed;top:'+(pe_ot>0?pe_ot:0)+'px;left:'+(ox-scrx)+'px;width:'+ow+'px;');
            ep.nextSibling.setAttribute('style','margin-top:'+oh+'px;');
        } else {
            ep.setAttribute('style','position:relative;width:100%;');
            ep.nextSibling.setAttribute('style','margin-top:0px;');
        }
    }
};
