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
 * @file vendor/phppe/Core/js/boolean.js
 * @author bzt
 * @date 1 Jan 2016
 * @brief Yes or no selector
 */
pe.boolean={
/* PRIVATE VARS */

/* PUBLIC METHODS */
    init: function()
    {
        var i,allinp=document.getElementsByTagName("input"),p=new DOMParser();
        for(i=0;i<allinp.length;i++) {
            var t=allinp[i].getAttribute('data-type');
            if(t!="boolean" && t!="notboolean") continue;
            var html=this.open(allinp[i].name,allinp[i].checked?true:false,t,allinp[i].getAttribute('data-args').split(','));
            var span=document.createElement('SPAN');
            span.innerHTML=html;
            allinp[i].parentNode.replaceChild(span,allinp[i]);
        }
    },

    open: function(name,value,type,args)
    {
        var t="",t1="",t2="",rtl=LANG['rtl']!=null&&LANG['rtl']!=''&&LANG['rtl']!=false;
    //	if(value!=true&&value!="true"&&value!=1&&value!="1"&&value!=false&&value!="false"&&value!=0&&value!="0")
    //		value=args[0];
        value=value==true||value=="true"||value==1||value=="1"?"true":"false";
        t+="<input type='hidden' name='"+name+"' value='"+value+"' data-args='"+args.join(",")+"'>";
    
      t+="<span"+(rtl?" dir='rtl'":"")+" onclick='pe."+type+".on(this);' ";
      t+="style='margin:2px 0px 2px 0px;color:#"+(type=="boolean"&&value=="true"||type=="notboolean"&&value!="true"?"c0ffc0":"000")+";border-color:#DADADA;";
      t+="border-top-"+(rtl?"right":"left")+"-radius:5px;border-bottom-"+(rtl?"right":"left")+"-radius:5px;box-shadow:0 1px 3px #999;text-shadow:0 -1px 1px #808080;";
      t+="background-color:#"+(type=="boolean"&&value=="true"||type=="notboolean"&&value!="true"?"00c000; background-image:linear-gradient(to top, #00FF00, #008000)":"F0F0F0; background-image:linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%)")+";";
      t+="cursor:pointer;padding:1px;'>&nbsp;&nbsp;"+L(args[1]!=null&&args[1]!=""?args[1]:"Yes")+"&nbsp;&nbsp;</span>";
    
      t+="<span"+(rtl?" dir='rtl'":"")+" onclick='pe."+type+".off(this);' ";
      t+="style='margin:2px 0px 2px 0px;color:#"+(type=="boolean"&&value!="true"||type=="notboolean"&&value=="true"?"ffc0c0":"000")+";border-color:#DADADA;";
      t+="border-top-"+(rtl?"left":"right")+"-radius:5px;border-bottom-"+(rtl?"left":"right")+"-radius:5px;box-shadow:0 1px 3px #999;text-shadow:0 -1px 1px #808080;";
      t+="background-color:#"+(type=="boolean"&&value!="true"||type=="notboolean"&&value=="true"?"c00000; background-image:linear-gradient(to top, #FF0000, #800000)":"F0F0F0; background-image:linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%)")+";";
      t+="cursor:pointer;padding:1px;'>&nbsp;&nbsp;"+L(args[2]!=null&&args[2]!=""?args[2]:"No")+"&nbsp;&nbsp;</span>";
    
      return t;
    },
    on: function(o)
    {
        o.previousSibling.value='true';
        o.style.color='#c0ffc0';
        o.style.backgroundColor='#00c000';
        o.style.backgroundImage='linear-gradient(to top, #00FF00, #008000)';
        o.nextSibling.style.color='#000';
        o.nextSibling.style.backgroundColor='#F0F0F0';
        o.nextSibling.style.backgroundImage='linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%)';
    },
    off: function(o)
    {
        o.previousSibling.previousSibling.value='false';
        o.style.color='#ffc0c0';
        o.style.backgroundColor='#c00000';
        o.style.backgroundImage='linear-gradient(to top, #FF0000, #800000)';
        o.previousSibling.style.color='#000';
        o.previousSibling.style.backgroundColor='#F0F0F0';
        o.previousSibling.style.backgroundImage='linear-gradient(to bottom,rgba(0,0,0,0.2) 1%,rgba(255,255,255,0) 89%,rgba(255,255,255,0.2) 10%)';
    }

/* PRIVATE METHODS */
};

pe.notboolean={
    init: function()
    {
        return pe.boolean.init();
    },
    open: function(name,value,type,args)
    {
        return pe.boolean.open(name,value,type,args);
    },
    on: function(o)
    {
        pe.boolean.on(o);
        o.previousSibling.value='false';
    },
    off: function(o) {
        pe.boolean.off(o);
        o.previousSibling.previousSibling.value='true';
    }
}
