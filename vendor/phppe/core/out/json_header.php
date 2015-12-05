<?php
//get current application. We are about to output it's property
die(json_encode(\PHPPE\Core::getval("app")->results));
?>
