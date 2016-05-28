<style scoped>
BODY { background:transparent !important;}
INPUT.input, TEXTAREA.input, SELECT.input { background: rgba(32,32,32,0.95); color:#fff !important; }
INPUT.reqinput, TEXTAREA.reqinput, SELECT.reqinput { background: rgba(48,32,32,0.95); color:#fff !important; }
</style>
<div class='infobox'>
<b><!=L('URL')></b><br><nobr><span dir='ltr'><span style='float:left;'><i><!=url("/")+""+(substr(url("/"),-1)!="/"?"/":"")></i></span><div><!field *text page.id></div></nobr><br/>
<div><b><!=L('Name')></b><!if quickhelp><small><!L help_pagename></small><!/if><!field *text page.name></div>
<div><b><!=L('Filters')></b><!if quickhelp><small><!L help_filters></small><!/if><!field text page.filter></div>
<br style='clear:both;'/>
<div><b><!=L('Layout')></b><br><!field *select page.template layouts></div>
<div><b><!=L('Language')></b><br><!field select page.lang langs></div>
<div><b><!=L('Not Before')></b><br><!field time(1) page.pubd></div>
<div><b><!=L('Not After')></b><br><!field time(1) page.expd></div>
<!if quickhelp><small><br style='clear:both;'/><!L help_publish></small><!/if>
</div>
