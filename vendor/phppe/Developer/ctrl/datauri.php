<?php
/**
 * Controller for creating inlined data URIs
 * Similar to artisan make
 */
namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\Http;

class DatauriController {
    static $cli="datauri <file>";

    function __construct()
    {
        //! check if executed from CLI
        if(Core::$client->ip!="CLI")
            Http::redirect("403");

        if(empty($_SERVER['argv'][2]))
            die(chr(27)."[96m".L("Usage").":".chr(27)."[0m\n  php public/index.php ".Core::$core->app.chr(27)."[92m <file>".chr(27)."[0m\n");

        //! create dataURI
        die("data:".mime_content_type($_SERVER['argv'][2]).";base64,".base64_encode(file_get_contents($_SERVER['argv'][2]))."\n");
    }

}
