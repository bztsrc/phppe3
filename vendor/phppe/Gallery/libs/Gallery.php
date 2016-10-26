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
 * @file vendor/phppe/Gallery/libs/Gallery.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief Image gallery functions
 */
namespace PHPPE;

/**
 * Main class
 *
 */
class Gallery
{
    private static $self;
    public static $sizes=[];
    public static $maxSize=512;
    public static $minQuality=5;
    public static $watermark="";
    public $wyswyg_toolbar = [ "image"=>"gallery/image" ];

/**
 * Initialize
 *
 * @param cfg
 */
    function init($cfg) {
        //! get image sizes
        if (!empty($cfg['sizes'])) {
            foreach (!is_array($cfg['sizes'])?str_getcsv($cfg['sizes']):$cfg['sizes'] as $s) {
                self::$sizes[] = explode("x",$s);
            }
        } else {
                self::$sizes = [ [57,57], [480,320], [1136,640], [1280,1024] ];
        }
        if (!empty($cfg['maxsize'])) {
            self::$maxSize=intval($cfg['maxsize']);
            if (self::$maxSize<128)
                self::$maxSize=128;
        }
        if (!empty($cfg['watermark']) && file_exists($cfg["Watermark"])) {
            self::$watermark=intval($cfg['watermark']);
        }
        if (!empty($cfg['minquality']) && $cfg['minquality']>1 && $cfg['minquality']<=10) {
            self::$minQuality=intval($cfg['minQuality']);
        }
        //! hangle gallery upload
        if (!empty($_FILES['imglist_upload'])) {
            Gallery::uploadImage($_FILES['imglist_upload']);
        }
    }

/**
 * Return images
 *
 * @return array of images
 */
    static function getImages()
    {
        if (!is_dir("data/gallery/0"))
            return [];
        $files = array_diff(scandir("data/gallery/0"), [".", ".."]);
        usort($files,function($a,$b){
            return filemtime("data/gallery/0/".$b)-filemtime("data/gallery/0/".$a);
        });
        $imgs = [];
        foreach($files as $f)
            $imgs[] = [ "id"=>$f, "name"=>$f ];
        return $imgs;
    }

/**
 * Function to save image lists
 *
 * @param name
 * @param array of image urls
 */
    static function saveImageList($name, $imgs)
    {
        //! check input
        if (empty($name))
            throw new \Exception(L('No imglist name'));
        if (is_string($imgs))
            $imgs = str_getcsv($imgs, ",");
        DS::exec("DELETE FROM img_list WHERE list_id=?",[$name]);
        foreach($imgs as $k=>$v)
            if(!empty($v)&&trim($v)!="null")
                DS::exec("INSERT INTO img_list (list_id,id,ordering) values (?,?,?)",[$name,$v,intval($k)]);
        return true;
    }

/**
 * Handle image upload
 *
 * @param file array
 */
    static function uploadImage($file)
    {
        if ($file['error']==4)
            return;
        if ($file['error']!=0 || $file['size']<1)
            Core::error(ucfirst(L('failed to upload file.')));
        elseif (substr($file['type'],0,5)!='image')
            Core::error(L('Only images allowed.'));
        else {
            if (!is_dir("data/gallery"))
                mkdir("data/gallery", 0750);
            //! generate different image sizes
            $l = count(self::$sizes)-1;
            foreach (self::$sizes as $k=>$s) {
                if (!is_dir("data/gallery/".$k))
                    mkdir("data/gallery/".$k, 0750);
                View::picture(
                    $file['tmp_name'],
                    "data/gallery/".$k."/".preg_replace("/[^a-zA-Z0-9_\.]/","",basename($file['name'])),
                    $s[0], $s[1], $k!=$l, $s[0]<256, self::$watermark, self::$maxSize, self::$minQuality);
            }
        }
    }
/**
 * Display upload button
 */
    static function uploadBtn()
    {
        View::js("img_loading()", "var d=document.createElement('div'); d.setAttribute('style','position:fixed;display:table-cell;top:0px;left:0px;width:100%;height:100%;z-index:2001;background:#000;opacity:0.4;text-align:center;padding-top:50%;');d.innerHTML='<img src=\"images/upload.gif\" style=\"position:fixed;top:50%;left:50%;transform:translateX(-50%) translateY(-50%);\">';document.body.appendChild(d);");
        return "<input type='file' name='imglist_upload' onchange='img_loading();this.form.submit();' style='display:none;'>".
        "<input type='button' value='".L("Upload")."' class='setsel_button' onclick=\"this.form['pe_f'].value='imglist';this.form['imglist_upload'].click();\">";
    }
/**
 * image chooser, loaded via AJAX
 */
    function image($item)
    {
        $list = self::getImages();
        echo(View::_t("<!form imglist>")."<input type='file' name='imglist_upload' onchange='this.form.submit();' style='display:none;'>".
        "<input type='button' value='".L("Upload")."' onclick=\"this.previousSibling.click();\"></form>".
        "<input type='text' style='width:130px;' placeholder='".L("Search")."' onkeyup='pe.wyswyg.search(this,this.nextSibling);'>");
        echo("<div class='wyswyg_gallery wyswyg_scroll'>\n");
        foreach($list as $img) {
            echo("<img src='gallery/1/".$img['id']."'>\n");
        }
        die("</div>");
    }

/**
 * default action
 */
    function action($item)
    {
        $url = str_replace("..", "", Core::$core->url);
        if (file_exists("data/".$url)) {
            Http::mime("image/jpeg");
            die(file_get_contents("data/".$url));
        }
        Core::$core->template="404";
    }
}
