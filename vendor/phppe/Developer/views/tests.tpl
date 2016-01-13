<div class='mosaicbox'><h1><!=L("Tests")></h1>
<div id='testsdiv' style='padding:0px 0px 5px 5px;'><input type='button' value='<!=L("Run all")>' onclick='runtest("tests/run");'><br><br>
<table style='margin-left:5px;'>
<tr>
    <th></th>
    <th><!=L("Test boundle")></th>
    <th><!=L("Avg.time")></th>
    <th><!=L("#Tests")></th>
    <th><!=L("Last run")></th>
    <th><!=L("Result")></th>
</tr>
<!foreach testCases>
<tr>
    <td class='row<!=ODD>'><input type='button' value='<!=L("Run")>' onclick='runtest("tests/run/<!=KEY>");' title='php public/index.php tests run <!=KEY>'></td>
    <td class='row<!=ODD>'><!=L(name)></td>
    <td class='row<!=ODD>' align='right'><!=avg><small>&nbsp;<!L sec></small></td>
    <td class='row<!=ODD>' align='right'><!=asserts></td>
    <td class='row<!=ODD>'><!=date("Y-m-d H:i:s",time)></td>
    <td class='row<!=ODD>' style='color:<!=color>;'><!=ret></td>
</tr>
<!/foreach>
</table>
</div>
<div id='loadingdiv' style='display:none;padding-left:30px;'><!=L("Please wait...")></div>
</div>