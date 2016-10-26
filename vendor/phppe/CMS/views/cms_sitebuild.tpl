<h1><!L Import sitebuild zip></h1>
<!include errorbox>
<!if !empty(htmls)>
	<h2><!L Choose a html></h2>
	<ul>
	<!foreach htmls>
		<li><a href='<!=url("cms/sitebuild")><!=IDX>'><!=basename(VALUE)></a></li>
	<!/foreach>
	</ul>
<!else>
	<!if !empty(content)>
		<h2><!L Choose application area></h2>
		<div onmousemove='pe.cms.divchoosemove(event);' onclick='pe.cms.divchooseclick(event);'><!=content></div>
	<!else>
		<a href='<!=url("cms/layouts")>'><!L Back></a>
	<!/if>
<!/if>
