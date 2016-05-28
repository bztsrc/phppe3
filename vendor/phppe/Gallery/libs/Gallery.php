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
/**
 * Initialize
 *
 * @param cfg not used
 */
	function init($cfg) {
        if(!empty($_FILES['imglist_upload'])) {
            \PHPPE\Gallery::uploadImage($_FILES['imglist_upload']);
        }
	}

/**
 * Return images
 *
 * @return array of images
 */
    static function getImages()
    {
        if (!is_dir("data/gallery"))
            mkdir("data/gallery", 0750);
        $files = array_diff(scandir("data/gallery"), [".", ".."]);
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
            $imgs = \PHPPE\Core::x(",", $imgs);
        \PHPPE\DS::exec("DELETE FROM img_list WHERE list_id=?",[$name]);
        foreach($imgs as $k=>$v)
            if(!empty($v)&&trim($v)!="null")
                \PHPPE\DS::exec("INSERT INTO img_list (list_id,id,ordering) values (?,?,?)",[$name,$v,intval($k)]);
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
        else
            //! FIXME: use Core::picture() to generate thumbnails
            move_uploaded_file($file['tmp_name'], "data/gallery/".basename($file['name']));
    }
    
/**
 * default action
 */
	function action($item)
	{
        $url = str_replace("..", "", \PHPPE\Core::$core->url);
        if (file_exists("data/".$url)) {
            Http::mime("image/jpeg");
            die(file_get_contents("data/".$url));
        }
        Core::$core->template="404";
	}
}
