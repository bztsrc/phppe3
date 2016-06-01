<h1><!L Benchmarks></h1>
<!if empty(data)>
<div class='alert alert-danger'>
    <!L No samples found!>
</div>
<!else>
<!=sprintf(L("Using %s samples."),data['footer']['cnt'])>
<!/if>

<style scoped>
    TR:nth-child(odd) { background:#F0F0F0; }
    TABLE { border-collapse: separate; border-spacing: 3px; }
    TD { padding:2px; }
</style>

<h2><!L Rundown></h2>
<table>
<tr>
    <th><!L Name></th>
    <th><!L Start></th>
    <th><!L Time consumed></th>
</tr>
<!foreach data>
<tr>
    <td><!=KEY></td>
    <td align='right'><small><!=str></small></td>
    <td><meter value='1' style='margin-left:<!=str*10000>px;width:<!=avg*10000>px;'></meter><small><!=avg></small></td>
</tr>
<!/foreach>
</table>

<h2><!L Fluctuation></h2>
<table>
<tr>
    <th><!L Name></th>
    <th><!L Min></th>
    <th><!L Avarage></th>
    <th><!L Max></th>
    <th colspan='2'><!L Delta></th>
</tr>
<!foreach data>
<tr>
    <td><!=KEY></td>
    <td align='right'><small><!=min></small></td>
    <td align='right'><small><!=avg></small></td>
    <td align='right'><small><!=max></small></td>
    <td><meter value='<!=sprintf("%.8f",max-min)>' style='width:300px;' max='<!=app.delta>'></meter><small><!=sprintf("%.8f",max-min)></small></td>
</tr>
<!/foreach>
</table>
<br><br>
