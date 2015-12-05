/*
 *   PHP Portal Engine v3.0.0
 *   https://github.com/bztsrc/phppe3/
 *
 *   Copyright 2015 bzt, LGPLv3
 *
 *   PHPPE Cookie Alert
 *
 */
var cookiealert_name='';

function cookiealert_init(cfg)
{
	cookiealert_name=cfg.cookiename;
	if(document.cookie.indexOf(cfg.cookiename + "=")<0){
		var div=document.createElement('div');
		div.setAttribute('id','cookiealert');
		div.innerHTML='<span>'+L(cfg.message)+' <a href="'+cfg.moreurl+'" target="_new">'+L(cfg.morelabel)+'</a></span><button onclick="cookiealert_set();">'+L(cfg.acceptlabel)+'</button>';
		document.body.appendChild(div);
	}
}
function cookiealert_set()
{
		var today=new Date();
		var expire=new Date();
		expire.setTime(today.getTime()+3600000*24*365);
		document.cookie=cookiealert_name+"="+today.getTime()+";path=/;expires="+expire.toGMTString();
		document.getElementById('cookiealert').style.display='none';
}
