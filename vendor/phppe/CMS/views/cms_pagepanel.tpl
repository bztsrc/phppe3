<!if !empty(core.item)>
<div id='pe_pageresp' class='sub' onmousemove='return pe_w();' style='visibility:hidden;'></div>
<span id='pageresp'><!cms pageresp Resolutions></span><span><!cms pagemeta Meta></span><span><!cms pagepublish Publish></span><span><!cms pagefilters Filters></span><span><!cms pagedds Dynamic_Data_Sets></span><!if core.lib("cms").revert><span><!cms pagerevert Revert_page></span><!/if><span><!cms pagedelete Delete_page></span><span><!cms pageadd Clone_page></span><span><img src='images/cms/accept.png' title="<!L Save>" style='position:absolute;z-index:998;' onclick='document.location.href="<!=core.item>";'></span>
<!else>
<span><img src='images/cms/pagenew.png' title="<!L Add new page>" style='position:absolute;z-index:998;' onclick="cms_pageadd(this,'pageadd');"></span>
<!/if>
