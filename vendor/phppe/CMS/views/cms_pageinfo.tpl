<style scoped>
.infobox DIV { display:inline-block; float:left; width:50%; padding:2px; }
.infobox SPAN { color:#d0d0d0 !important; }
.infobox B { color:#d0d0d0 !important; text-shadow: #000 2px 2px 3px; }
.header { color:#d0d0d0 !important; text-shadow: #000 2px 2px 3px; font-weight:bold; line-height:22px; font-size:20px; padding:2px;}
</style>
<span class='header'><!=L(page.header)></span>
<div class='infobox' style='overflow:auto;padding:3px;'>
<b><!=L('URL')></b><br><nobr><span dir='ltr'><span style='float:left;'><i><!=url("/")+""+(substr(url("/"),-1)!="/"?"/":"")></i></span><div><!field *text page.id></div></nobr><br/>
<div><b><!=L('Name')></b><!if quickhelp><small><!L help_pagename></small><!/if><!field *text page.name></div>
<div><b><!=L('Filters')></b><!if quickhelp><small><!L help_filters></small><!/if><!field text page.filter></div>
<br style='clear:both;'/>
<div><b><!=L('Language')></b><br><!field select page.lang langs></div>
<div><b><!=L('Layout')></b><br><!field *select page.template layouts></div>
<div><b><!=L('Not Before')></b><br><!field time(1) page.pubd></div>
<div><b><!=L('Not After')></b><br><!field time(1) page.expd></div>
<!if quickhelp><small><br style='clear:both;'/><!L help_publish></small><!/if>
<br style='clear:both;'>
<b><!=L('Meta data')></b><br><!field cmsmeta page.meta>
<br style='clear:both;'>
<b><!=L('Style Sheets')></b><br><!field cmscss page.css>
<br style='clear:both;'>
<b><!=L('Javascript Libraries')></b><br><!field cmsjs page.js>
</div>


