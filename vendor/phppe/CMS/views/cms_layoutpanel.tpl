<!if !empty(core.item)>
<span style='padding-right:10px;'><img style="padding:3px;position:absolute;" onclick="cms_layoutdelete(this,&quot;layoutdelete&quot;,1);" src="images/cms/layoutdelete.png" alt="[LAYOUTDELETE <!=htmlspecialchars(L('Delete layout'))>]" title="<!=htmlspecialchars(L('Delete layout'))>"><span></span></span>
<span style='padding-right:10px;'><img style="padding:3px;position:absolute;" onclick="cms_layoutadd(this,&quot;layoutadd&quot;,2);" src="images/cms/layoutadd.png" alt="[LAYOUTADD <!=htmlspecialchars(L('Clone layout'))>]" title="<!=htmlspecialchars(L('Clone layout'))>"><span></span></span>
<!else>
<span style='padding-right:10px;'><img style="padding:3px;position:absolute;" onclick="cms_layoutadd(this,&quot;layoutadd&quot;,2);" src="images/cms/layoutadd.png" alt="[LAYOUTADD <!=htmlspecialchars(L('Add new layout'))>]" title="<!=htmlspecialchars(L('Add new layout'))>"><span></span></span>
<!/if>
