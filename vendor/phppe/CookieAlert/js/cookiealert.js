/*
 *   PHP Portal Engine v3.0.0
 *   https://github.com/bztsrc/phppe3/
 *
 *   Copyright 2016 LGPL bzt
 *
 *   PHPPE Cookie Alert
 *
 */
pe.cookiealert = {
  name:'',

/* PUBLIC METHODS */
  init:function(cfg)
  {
    pe.cookiealert.name=cfg.cookiename;
    if(document.cookie.indexOf(cfg.cookiename + "=")<0){
      var div=document.createElement('div');
      div.setAttribute('id','cookiealert');
      div.innerHTML='<span>'+L(cfg.message)+' <a href="'+cfg.moreurl+'" target="_new">'+L(cfg.morelabel)+'</a></span><button onclick="pe.cookiealert.set();">'+L(cfg.acceptlabel)+'</button>';
      document.body.appendChild(div);
      if(typeof jQuery=='function'){
        var h=div.offsetHeight>10?div.offsetHeight:10;
        $('#cookiealert').css({height:"0px"});
        $('#cookiealert').animate({height:h+"px"},250);
      }
    }
  },

/* PRIVATE METHODS */
  set:function()
  {
      var t=new Date();
      var e=new Date();
      e.setTime(t.getTime()+3600000*24*365);
      document.cookie=pe.cookiealert.name+"="+t.getTime()+";path=/;expires="+e.toGMTString();
      if(typeof jQuery=='function'){
        $('#cookiealert').animate({height:"0px"},250,function(){ document.getElementById('cookiealert').style.display='none'; });
      } else
        document.getElementById('cookiealert').style.display='none';
  }
};
