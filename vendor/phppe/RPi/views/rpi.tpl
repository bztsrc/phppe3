<h2>GPIO pinout</h2>
<b>P1</b>
<table id='pins' cellspacing='2'>
<!foreach pins>
<!if ODD>
	<tr><td align='right' <!if isset(values[KEY])>data-value='<!=values[KEY]>' onclick='rpi_swgpio(this,"<!=urlencode(VALUE)>");' style='cursor:pointer;background-color:#<!if values[KEY]>0F0<!else>006000<!/if>;'<!/if>><!=VALUE></td>
	<td><!if core.lib("GPIO").hdlr[VALUE]=="in">▶<!/if><!if core.lib("GPIO").hdlr[VALUE]=="out">◀<!/if></td>
<!/if>
<td style='text-align:right;background-color:#<!if VALUE=='3.3V'||VALUE=='5V'>FF0000;color:#fff<!else><!if VALUE=='GND'>000;color:#fff<!else><!if isset(values[KEY])>C0F0C0<!else>C0C0C0<!/if><!/if><!/if>;'><!=KEY></td>
<!if !ODD>
	<td><!if core.lib("GPIO").hdlr[VALUE]=="in">◀<!/if><!if core.lib("GPIO").hdlr[VALUE]=="out">▶<!/if></td>
	<td <!if isset(values[KEY])>data-value='<!=values[KEY]>' onclick='rpi_swgpio(this,"<!=urlencode(VALUE)>");' style='cursor:pointer;background-color:#<!if values[KEY]>0F0<!else>006000<!/if>;'<!/if>><!=VALUE></td></tr>
<!/if>
<!/foreach>
</table>