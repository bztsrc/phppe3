<!if !empty(core.item)>
<span><img onclick="zoom_open(event,&quot;layout_fieldset&quot;);" src="images/cms/layoutmeta.png" title="<!=htmlspecialchars(L('Layout meta'))>"></span><span><img onclick="cms_layoutdelete(this,&quot;layoutdelete&quot;,1);" src="images/cms/layoutdelete.png" title="<!=htmlspecialchars(L('Delete layout'))>"></span><span><img onclick="cms_layoutadd(this,&quot;layoutadd&quot;,2);" src="images/cms/clone.png" title="<!=htmlspecialchars(L('Clone layout'))>"></span>
<!else>
<span><img onclick="cms_layoutadd(this,&quot;layoutadd&quot;,2);" src="images/cms/layoutadd.png" title="<!=htmlspecialchars(L('Add new layout'))>"></span>
<!/if>
