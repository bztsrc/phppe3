<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016 bzt
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *   <http://www.gnu.org/licenses/>
 *
 * @file vendor/phppe/DataLibrary/libs/DataLibrary.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief Data upload/download functions
 */
namespace PHPPE;

/**
 * Main class
 *
 */
class DataLibrary
{
    private static $self;
    public $wyswyg_toolbar = [ "attachment"=>"datalibrary/attachment" ];

/**
 * Initialize
 *
 * @param cfg not used
 */
	function init($cfg) {
        if(!empty($_FILES['doclist_upload'])) {
            \PHPPE\DataLibrary::uploadDocument($_FILES['doclist_upload']);
        }
	}

/**
 * Return documents
 *
 * @return array of documents
 */
    static function getDocuments()
    {
        //! file types
        $types=[
            "image"=>["jpg","jpeg","png","gif","bmp"],
            "movie"=>["mov","avi","mpg","mkv","flv"],
            "music"=>["mp3","snd","ogg","flac"],
            "pdf"  =>["pdf","ps"],
            "pres" =>["ppt","pptx","pps","ppsx","odp"],
            "sheet"=>["xls","xlsx","ods"],
            "word" =>["doc","docx","odt","rtf"],
            "text" =>["txt","asc","nfo","me"],
        ];
        if (!is_dir("data/download"))
            mkdir("data/download", 0750);
        $files = array_diff(scandir("data/download"), [".", ".."]);
        usort($files,function($a,$b){
            return filemtime("data/download/".$b)-filemtime("data/download/".$a);
        });
        $docs = [];
        foreach($files as $f) {
            $ext = pathinfo($f, PATHINFO_EXTENSION);
            $type= "other";
            foreach($types as $k=>$v)
                if(in_array($ext,$v)) {$type=$k;break;}
            $docs[] = [
                "id"=>$f,
                "name"=>$f,
                "size"=>filesize("data/download/".$f),
                "ext"=>$ext,
                "type"=>$type,
            ];
        }
        return $docs;
    }
/**
 * Function to save document lists
 *
 * @param name
 * @param array of image urls
 */
    static function saveDocumentList($name, $docs)
    {
        //! check input
        if (empty($name))
            throw new \Exception(L('No doclist name'));
        if (is_string($docs))
            $docs = str_getcsv($docs, ',');
        \PHPPE\DS::exec("DELETE FROM doc_list WHERE list_id=?",[$name]);
        foreach($docs as $k=>$v)
            if(!empty($v)&&trim($v)!="null")
                \PHPPE\DS::exec("INSERT INTO doc_list (list_id,id,ordering) values (?,?,?)",[$name,$v,intval($k)]);
        return true;
    }

/**
 * Handle document upload
 *
 * @param file array
 */
    static function uploadDocument($file)
    {
        if ($file['error']==4)
            return;
        if ($file['error']!=0 || $file['size']<1)
            Core::error(ucfirst(L('failed to upload file.')));
        else
            move_uploaded_file($file['tmp_name'], "data/download/".preg_replace("/[^a-zA-Z0-9_\.]/","",basename($file['name'])));
    }
    
/**
 * document chooser, loaded via AJAX
 */
    function attachment($item)
    {
        $list = self::getDocuments();
        echo(\PHPPE\View::_t("<!form doclist>")."<input type='file' name='doclist_upload' onchange='this.form.submit();' style='display:none;'>".
        "<input type='button' value='".L("Upload")."' onclick=\"this.previousSibling.click();\"></form>".
        "<input type='text' style='width:130px;' placeholder='".L("Search")."' onkeyup='pe.wyswyg.search(this,this.nextSibling);'>");
        echo("<div class='wyswyg_docs wyswyg_scroll'>\n");
        foreach($list as $doc) {
            echo("<a href='download/".$doc['id']."'><img src='images/datalibrary/".$doc['type'].".png' alt='".$doc['type']."' height='16'>".$doc['id']."</a>\n");
        }
        die("</div>");
    }

/**
 * default action
 */
    function action($item)
    {
        $url = str_replace("..", "", \PHPPE\Core::$core->url);
        if (file_exists("data/".$url)) {
            Http::mime(mime_content_type("data/".$url));
            die(file_get_contents("data/".$url));
        }
        Core::$core->template="404";
    }
}
