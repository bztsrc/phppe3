<h1><!L Lists></h1>
<!if core.item>
<a href='<!=url(core.app,core.action)>'><!L Main menu></a>
<!if !empty(parentparent)>
&nbsp;&raquo;&nbsp;<a href='<!=url(core.app,core.action)><!=parentparent>'><!=L(parentparent)></a>
<!/if>
<!if !empty(parentmenu)>
&nbsp;&raquo;&nbsp;<a href='<!=url(core.app,core.action)><!=parentmenu>'><!=L(parentmenu)></a>
<!/if>
<br>
<!/if>
<!dump lists>
