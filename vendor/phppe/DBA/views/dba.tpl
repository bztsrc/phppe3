<div style='position:fixed;background:rgba(255,255,255,0.8);width:100%;'><table style='width:100%;'><tr>
<td style='padding:5px;'><!field select dba.table tables - switchtable(this.value)></td>
<td style='padding:5px;'><!field text dba.search searchtable(this.value) - - Search></td>
</tr></table></div>
<div style='padding:64px 5px 0px 5px;'>
<table style='width:100%;' class='resptable result' cellspacing='2'>
<tbody>
<tr>
<!foreach columns>
<th title='<!=id> <!=type>'<!if key> class='dba_key'<!/if>><!=name></th>
<!/foreach>
</tr>
<!template>
<%foreach rows>
<tr>
    <!foreach columns>
    <td><%=htmlspecialchars(crop(<!=id>))></td>
    <!/foreach>
</tr>
<%/foreach>
<!/template>
</tbody>
</table>
</div>
