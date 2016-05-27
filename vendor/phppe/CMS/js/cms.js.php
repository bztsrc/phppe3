/*
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016 bzt
 */

var cms_return=null, cms_url=null, cms_reload=false;
var cms_border=null;
var cms_scrx=null, cms_srcy=null;

//! open cms editor box
function cms_edit(icon, paramidx, adjust, minw, minh, forcew, forceh, forcefull)
{
    //! save current scroll position
    cms_scrx=window.pageXOffset?window.pageXOffset:document.body.scrollLeft;
    cms_scry=window.pageYOffset?window.pageYOffset:document.body.scrollTop;
    //! get background and editor box
    var cmsbg = document.getElementById('cmsbg');
    var cmsbox = document.getElementById('cmsbox');
    if(cmsbg == null) {
        //! create background div
        cmsbg = document.createElement('DIV');
        cmsbg.setAttribute('id', 'cmsbg');
        cmsbg.setAttribute('style', 'position:fixed;display:table-cell;top:0px;left:0px;width:100%;height:100%;z-index:998;background:#000;opacity:0.4;visibility:hidden;');
        cmsbg.setAttribute('onclick', 'cms_close();');
        document.body.appendChild(cmsbg);
        //! create editor box iframe
        cmsbox = document.createElement('IFRAME');
        cmsbox.setAttribute('id', 'cmsbox');
        cmsbox.setAttribute('style', 'position:fixed;display:table-cell;top:0px;left:0px;width:1px;height:1px;z-index:999;background:rgba(64,64,64,0.9) !important;visibility:hidden;overflow:hidden;border:0px;opacity:0.9;');
        cmsbox.setAttribute('scrolling', 'no');
        document.body.appendChild(cmsbox);
    }
    //! get coordinates for editor box
    var rt,x,y,w,h;
    var ww=(window.innerWidth?window.innerWidth:document.body.offsetWidth);
    var wh=(window.innerHeight?window.innerHeight:document.body.offsetHeight);
    if(icon.parentNode!=null) {
        var ps=window.getComputedStyle(icon.parentNode);
        var t=parseInt(ps.getPropertyValue("padding-top"),10); if(t==null||t==NaN) t=0;
        var l=parseInt(ps.getPropertyValue("padding-left"),10); if(l==null||l==NaN) l=0;
        var r=parseInt(ps.getPropertyValue("padding-right"),10); if(r==null||r==NaN) r=0;
        var b=parseInt(ps.getPropertyValue("padding-bottom"),10); if(b==null||b==NaN) b=0;
        rt=icon.parentNode.getBoundingClientRect();
        x=Math.floor(rt.left)+l;
        y=Math.floor(rt.top)+t;
        w=icon.parentNode.offsetWidth-l-r; h=icon.parentNode.offsetHeight-t-b;
    } else {
        rt=icon.getBoundingClientRect();
        x=Math.floor(rt.left);
        y=Math.floor(rt.top);
        w=1; h=1;
    }
    //! get forced position and dimensions
    if(forcew>0) w=forcew;
    if(forceh>0) h=forceh;
    //! adjust position and check minimum width, height
    if(w<48) w=48; if(h<24) h=24;
    if(minw>0 && w<minw) w=minw;
    if(minh>0 && h<minh) h=minh;
    if(adjust>0) {
        y-=adjust;
        h+=adjust;
    }
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
    //! make box and background visible
    cmsbg.style.visibility = 'visible';
    cmsbox.style.visibility = 'visible';
    //! set editor box position and size
    if(<?=empty(\PHPPE\Core::$core->noanim)?'false':'true'?> || typeof jQuery=='undefined'){
        cmsbox.style.left=x;
        cmsbox.style.top=y;
        cmsbox.style.width=w;
        cmsbox.style.height=h+'px';
        cms_return=null;
    } else {
        rt=icon.getBoundingClientRect();
        cmsbox.style.left=Math.floor(rt.left)+'px';
        cmsbox.style.top=Math.floor(rt.top)+'px';
        cmsbox.style.width=icon.offsetWidth+'px';
        cmsbox.style.height=icon.offsetHeight+'px';
        cms_return={left:cmsbox.style.left,top:cmsbox.style.top,width:icon.offsetWidth+'px',height:icon.offsetHeight+'px'};
        $('#cmsbox').animate({left:x,top:y,width:w,height:h+'px'},500);
    }
    //! load form into editbox during animation
    cmsbox.src='<?=url("cms", "param")?>'+paramidx+'?height='+(h-28)+'&scrx='+cms_scrx+'&scry='+cms_scry;
}

function cms_close(reload)
{
    //! see if reditection requested
    cms_reload=(reload!=null && reload!=false);
    //! scroll back page where it was
    window.scrollTo(0,0);
    window.scrollBy(cms_scrx,cms_scry);
    if(cms_return==null || typeof jQuery=='undefined'){
        cms_hideedit();
    } else {
        $('#cmsbox').animate(cms_return, 500, cms_hideedit);
    }
}

function cms_hideedit()
{
    //! close editor box and hide background
    document.getElementById('cmsbg').style.visibility='hidden';
    document.getElementById('cmsbox').style.visibility='hidden';
    //! release lock. We do it synchronously on purpose
    var r = new XMLHttpRequest();
    r.open('GET', '<?=url("cms", "unlock")?>', false); r.send(null);
    //! reload page if requested
    if(cms_reload)
        document.location.href=document.location.href;
}

function cms_init(scrx,scry)
{
    //! if scroll position was saved in session before reloading page
    if(scry!=null) {
        window.scrollTo(0,0);
        window.scrollBy(scrx,scry);
    }

}

function cms_pagedel()
{
    confirm('<?=L("Are you sure you want to delete this page?")?>');
}
