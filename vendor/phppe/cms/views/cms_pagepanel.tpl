<!if !empty(core.item)>
<span style='padding-right:10px;'><a href='<!=url("cms","pages")>'><img src='images/cms/home.png' style="position:absolute;"></a></span>
<div id='pe_pageresp' class='sub' onmousemove='return pe_w();' style='visibility:hidden;'></div>
<span style='padding-right:10px;' id='pageresp'><!cms pageresp Resolutions></span>
<span style='padding-right:10px;'><!cms pagemeta Meta></span>
<span style='padding-right:10px;'><!cms pagepublish Publish></span>
<span style='padding-right:10px;'><!cms pagefilters Filters></span>
<span style='padding-right:10px;'><!cms pagedds Dynamic_Data_Sets></span>
<!if core.lib("cms").revert>
<span style='padding-right:10px;'><!cms pagerevert Revert_page></span>
<!/if>
<span style='padding-right:10px;'><!cms pagedelete Delete_page></span>
<span style='padding-right:10px;'><!cms pageadd Clone_page></span>
<span style='padding-right:10px;'><a href='<!=core.item>'><img src='images/cms/accept.png' style="position:absolute;"></a></span>
<!else>
<span style='padding-right:10px;'><img src='images/cms/pageadd.png' style="position:absolute;" title="<!L Add new page>" onclick="cms_pageadd(this,'pageadd');"></span>
<!/if>
