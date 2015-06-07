<!foreach app.results>
<item>
	<title><![CDATA[<!=title>]]></title>
<!if !empty(description)>
	<description><![CDATA[<!=description>]]></description>
<!/if>
<!if !empty(category)>
	<category><![CDATA[<!=category>]]></category>
<!/if>
<!if !empty(link)>
	<link><!=link></link>
<!/if>
	<guid><!if !empty(guid)><!=guid><!else><!if !empty(link)><!=link><!else><!=sha1(time()+'_'+IDX)><!/if><!/if></guid>
</item>
<!/foreach>