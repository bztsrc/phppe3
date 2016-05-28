<style scoped>
BODY { background:transparent !important;}
INPUT.input, TEXTAREA.input, SELECT.input { background: rgba(32,32,32,0.95); color:#fff !important; }
INPUT.reqinput, TEXTAREA.reqinput, SELECT.reqinput { background: rgba(48,32,32,0.95); color:#fff !important; }
</style>
<div class='infobox'>
<!foreach param>
<img src='images/cms/pagedelete.png' alt='[Delete]' title='<!=L("Delete_page")>' style='vertical-align:middle;float:left;padding:2px;z-index:10;' onclick='if(confirm(L("Are you sure?"))) return document.location.href="<!=url("cms","pagepurge")+""+urlencode(id)>";'>
<img src='images/cms/pagediff.png' alt='[Diff]' title='<!=L("Diff_page")>' style='vertical-align:middle;float:left;padding:2px;z-index:10;' onclick='if(this.nextSibling!=null&&this.nextSibling.nextSibling!=null)this.nextSibling.nextSibling.style.background="#909090";window.open("<!=url("cms","pagediff")+""+urlencode(id)>","","width="+(screen.availWidth+0)+"px");'>
<div style='cursor:pointer;z-index:9;' onmouseover='this.style.background="#808080";' onmouseout='this.style.background="";' onclick='document.location.href="<!=url("cms","pagehistory")+""+urlencode(id)>";'>
<span style='min-width:320px;display:inline-block;'><img src='images/cms/pagerevert.png' alt='[Revert]' title='<!=L("Revert_page")>' style='vertical-align:middle;padding:2px;'>
<!=name> (<!difftime ago>)</span><!if modifyid==-1>admin<!else><!if moduser><!=moduser><!else>?<!/if><!/if></div>
<!/foreach>
</div>
