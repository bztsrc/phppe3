<style scoped>
    TR:nth-child(odd) { background:#F0F0F0; }
    TABLE { border-collapse: separate; border-spacing: 3px; }
    TD { padding:2px; }
    SELECT.form-control { width: 80% !important; display: inline;}
</style>
<h1><!L Benchmarks></h1>
<!if empty(data)>
<div class='alert alert-danger'>
    <!L No samples found!><br>
    <!L Append '?benchmark' to your url. Note that only source.php supports benchmarking.>
</div>
<!else>
<button class='btn btn-alert' onclick='document.location="/benchmark?clearbenchmark";'><!L Clear samples></button>&nbsp;
<!field select url urls - choosediv(this.value)>
<!/if>
<!foreach data>
<div id='url<!=IDX-1>' class='benchmark' style='display:<!if IDX==1>block<!else>none<!/if>;'>
<h2><!L Rundown></h2>
<table>
<tr>
    <th><!L Name></th>
    <th><!L Start></th>
    <th><!L Time consumed></th>
</tr>
<!foreach VALUE>
<!if KEY!='total' && KEY!='delta' && KEY!='count'>
<tr>
    <td><!=KEY></td>
    <td align='right'><small><!=str></small></td>
    <td><meter value='1' style='margin-left:<!=str*10000>px;width:<!=avg*10000>px;'></meter><small><small>&nbsp;<!=avg></small></small></td>
</tr>
<!/if>
<!/foreach>
<tr>
    <td align='right'><i><!L Total></i></td>
    <td align='right'><b><small><!=total></small></b></td>
    <td><b><small>secs</small></b></td>
</tr>
</table>

<h2><!L Fluctuation></h2>
<table>
<tr>
    <th><!L Name></th>
    <th><!L Min></th>
    <th><!L Avarage></th>
    <th><!L Max></th>
    <th><!L Min></th>
    <th><!L Max></th>
</tr>
<!foreach VALUE>
<!if KEY!='total' && KEY!='delta' && KEY!='count'>
<tr>
    <td><!=KEY></td>
    <td align='right'><small><!=min></small></td>
    <td align='right'><small><!=avg></small></td>
    <td align='right'><small><!=max></small></td>
    <td align='right'><small><small>-<!=sprintf("%.8f",avg-min)>&nbsp;</small></small><meter value='<!=sprintf("%.8f",avg-min)>' max='<!=parent.delta>'></meter></td>
    <td><meter value='<!=sprintf("%.8f",max-avg)>' max='<!=parent.delta>'></meter><small><small>&nbsp;+<!=sprintf("%.8f",max-avg)></small></small></td>
</tr>
<!/foreach>
<!/foreach>
</table>
</div>
<!/foreach>
<br><br>
