<style scoped>
BODY { background:transparent !important;color:#B0B0B0;}
INPUT.input, TEXTAREA.input, SELECT.input { background: rgba(32,32,32,0.95); color:#fff !important; }
INPUT.reqinput, TEXTAREA.reqinput, SELECT.reqinput { background: rgba(48,32,32,0.95); color:#fff !important; }
</style>
<div class='infobox' style='padding:5px;overflow:auto;'>
<div style='display:block;width:100%;'><b><!=L('ID')></b><!if quickhelp><small><!L help_layoutid></small><!/if><!field *text page.layoutid></div>
<div style='display:block;width:100%;'><b><!=L('Name')></b><!if quickhelp><small><!L help_layoutname></small><!/if><!field text page.layoutname></div>
<!if quickhelp><small><br style='clear:both;'/><!L help_layoutadd></small><!/if>
</div>
