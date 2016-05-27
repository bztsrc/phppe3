<style>
BODY { background:transparent !important;}
.infobox TD { color: #fff; }
.infobox B { color:#d0d0d0 !important; text-shadow: #000 2px 2px 3px; margin-top:8px; }
.header { color:#d0d0d0 !important; text-shadow: #000 2px 2px 3px; font-weight:bold; line-height:22px; font-size:20px; padding:2px;}
</style>
<span class='header'><!=L("Page_history")></span>
<div class='infobox' style='overflow:auto;padding:3px;'>
<!foreach param>
<img src='images/cms/pagedelete.png' alt='[Delete]' title='<!=L("Delete_page")>' style='vertical-align:middle;float:left;padding:2px;z-index:10;' onclick='if(confirm(L("Are you sure?"))) return document.location.href="<!=url("cms","pagepurge")+""+urlencode(id)>";'>
<img src='images/cms/pagediff.png' alt='[Diff]' title='<!=L("Diff_page")>' style='vertical-align:middle;float:left;padding:2px;z-index:10;' onclick='if(this.nextSibling!=null&&this.nextSibling.nextSibling!=null)this.nextSibling.nextSibling.style.background="#909090";window.open("<!=url("cms","pagediff")+""+urlencode(id)>","","width="+(screen.availWidth+0)+"px");'>
<div style='cursor:pointer;z-index:9;' onmouseover='this.style.background="#808080";' onmouseout='this.style.background="";' onclick='document.location.href="<!=url("cms","pagehistory")+""+urlencode(id)>";'>
<span style='min-width:320px;display:inline-block;'><img src='images/cms/pagerevert.png' alt='[Revert]' title='<!=L("Revert_page")>' style='vertical-align:middle;padding:2px;'>
<!=name> (<!difftime ago>)</span><!if modifyid==-1>admin<!else><!if moduser><!=moduser><!else>?<!/if><!/if></div>
<!/foreach>
</div>


