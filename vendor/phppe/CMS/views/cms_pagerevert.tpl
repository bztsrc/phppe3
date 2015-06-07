<div class='confpanel'>
<h2><!=L("Page history")></h2>
<!foreach param>
<img src='images/cms/pagedelete.png' alt='[Delete]' style='vertical-align:middle;float:left;padding:2px;z-index:10;' onclick='if(confirm(L("Are you sure?"))) return document.location.href="<!=url("cms","pagepurge")+""+urlencode(id)>";'>
<img src='images/cms/pagediff.png' alt='[Diff]' style='vertical-align:middle;float:left;padding:2px;z-index:10;' onclick='window.open("<!=url("cms","pagediff")+""+urlencode(id)>","","width=<!=client.screen[0]>px");'>
<div style='cursor:pointer;z-index:9;' onmouseover='this.style.background="#808080";' onmouseout='this.style.background="";' onclick='document.location.href="<!=url("cms","pagerevert")+""+urlencode(id)>";'>
<img src='images/cms/pagerevert.png' alt='[Revert]' style='vertical-align:middle;padding:2px;'>
<!=name> (<!difftime ago>)</div>
<!/foreach>
</div>


