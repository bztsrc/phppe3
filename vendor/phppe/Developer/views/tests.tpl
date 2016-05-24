<div class='mosaicbox'><h1><!=L("Tests")></h1>
<div id='testsdiv' style='padding:0px 0px 5px 5px;'>
<pre style='display:inline-block;font-size:9px;'>
php public.php/index.php tests run
phpunit --bootstrap public/index.php vendor/phppe/Developer/tests
</pre>
<br>
<input type='button' value='<!=L("Run all")>' onclick='runtest("");'>
<br>
<table class='tests' style='margin-left:5px;border-spacing:2px;border-collapse:separate;'>
<tr>
    <th></th>
    <th><!=L("Test boundle")></th>
    <th><!=L("Avg.time")></th>
    <th colspan='2'><!=L("#Tests")></th>
    <th><!=L("Last run")></th>
    <th><!=L("Result")></th>
</tr>
<!foreach testCases>
<tr>
    <td class='row<!=ODD>'><input type='button' value='<!=L("Run")>' onclick='runtest("<!=KEY>");' title='php public/index.php tests run <!=KEY>'></td>
    <td class='row<!=ODD>'><!=L(name)></td>
    <td class='row<!=ODD>' align='right'><!=avg><small>&nbsp;<!L sec></small></td>
    <td class='row<!=ODD>' align='right'><!=executed> /</td>
    <td class='row<!=ODD>' align='right'><!=asserts></td>
    <td class='row<!=ODD>'><!=date("Y-m-d H:i:s",time)></td>
    <td class='row<!=ODD>' style='color:<!=color>;'><!=ret></td>
</tr>
<!/foreach>
</table>
<br><br><br>
</div>
<div id='loadingdiv' style='display:none;padding-left:30px;'><!=L("Please wait...")></div>
</div>