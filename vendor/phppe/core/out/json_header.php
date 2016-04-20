<?php
/**
 * Generate JSON output
 */

//get current application. We are about to output it's property
//we don't need templater for output, so exit here
die(json_encode(\PHPPE\Core::getval("app")->results));
?>
