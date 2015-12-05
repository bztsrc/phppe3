<!if !empty(core.item)>
<span style='padding-right:10px;'><img title="<!L Pages>" alt="" src='images/cms/home.png' onclick='document.location.href="<!=url("cms","pages")>";' style='position:absolute;z-index:998;'><span></span></span>
<!if core.lib("cms").revert><span style='padding-right:5px;'><!cms pagerevert Revert_page></span><!/if>
<div id='pe_pageresp' class='sub' onmousemove='return pe_w();' style='visibility:hidden;'></div>
<span style='padding-right:5px;' id='pageresp'><!cms pageresp Resolutions></span>
<span style='padding-right:5px;'><!cms pagemeta Meta></span>
<span style='padding-right:5px;'><!cms pagepublish Publish></span>
<span style='padding-right:5px;'><!cms pagefilters Filters></span>
<span style='padding-right:5px;'><!cms pagedds Dynamic_Data_Sets></span>
<span style='padding-right:5px;'><!cms pagedelete Delete_page></span>
<span style='padding-right:5px;'><!cms pageadd Clone_page></span>
<span style='padding-right:10px;'><img src='images/cms/accept.png' title="<!L Save>" style='position:absolute;z-index:998;' onclick='document.location.href="<!=core.item>";'><span></span></span>
<!else>
<span style='padding-right:10px;'><img src='images/cms/pageadd.png' title="<!L Add new page>" style='position:absolute;z-index:998;' onclick="cms_pageadd(this,'pageadd');"><span></span></span>
<!/if>
