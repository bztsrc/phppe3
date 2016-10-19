<style scoped>
BODY { background:transparent !important;color:#B0B0B0;}
INPUT.input, TEXTAREA.input, SELECT.input { background: rgba(32,32,32,0.95); color:#fff !important; }
INPUT.reqinput, TEXTAREA.reqinput, SELECT.reqinput { background: rgba(48,32,32,0.95); color:#fff !important; }
</style>
<div class='infobox' style='padding:5px;overflow:auto;'>
<b><!=L('Global')></b><!if quickhelp><small><!L help_global></small><!/if>
<!field cmsdds page.gdds><br/>
<b><!=L('Local')></b><!if quickhelp><small><!L help_local></small><!/if>
<!field cmsdds page.dds><br/>
<!if quickhelp><small><!L help_dds></small><!/if>
</div>