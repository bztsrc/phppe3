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
 * @file vendor/phppe/CMS/libs/CMS.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief Content Management Service
 */
namespace PHPPE;

class CMS
{
    public $expert=false;   //!< do not show help text for experienced users
    public $revert=false;   //!< do we have to keep history for reverting
    public $purge=8;        //!< we have to purge history if bigger than this
    public $metas=[];       //!< meta tags we want to make editable
    public $wyswyg_toolbar = [ "content"=>"cms/content" ];

/**
 * Initialization hook
 *
 * @return true on success
 */
    function init($cfg) {
        //! if user is not logged in or does not have the necessary
        //! priviledge then do not register this service
        if (!Core::$user->has("siteadm|webadm"))
            return false;

        //! load configuration into properties
        if (!empty($cfg['expert']))
            $this->expert=true;
        if (!empty($cfg['pagehistory']))
            $this->revert=true;
        if (isset($cfg['purge']))
            $this->purge=intval($cfg['purge']);
        if ($this->purge<3)
            $this->purge=8;
        if ($this->purge>128)
            $this->purge=128;
        if (!empty($cfg['metas']))
            $this->metas=str_getcsv($cfg['metas'],',');
        else
            $this->metas=["description", "keywords"];

        //! add menu
        if (Core::$user->has("siteadm")) {
            //! two level menu for site adminsitrators
            View::menu(L("Contents")."@siteadm|webadm", [
                L("Pages")."@siteadm|webadm"=>"cms/pages",
                L("Layouts")."@siteadm"=>"cms/layouts"
            ]);
        } else {
            //! simple link for web administrators
            View::menu(L("Contents")."@webadm", "cms/pages");
        }

        //! disable caching for all cms related applications
        //! also load the stylesheet for them
        if (Core::$core->app == "cms") {
            Core::$core->nocache = true;
            View::css("cms.css");
        }
    }

/**
 * Controller event, called before view generated
 *
 * @param context
 */
    function ctrl($ctx)
    {
        //! if we are on an editable page
        if(get_class(\PHPPE\View::getval("app"))=="PHPPE\Content") {
            //! clear the list of parameters
            $_SESSION['cms_param'] = [];
            //! save page url for cms/param action handler
            $_SESSION['cms_url'] = str_replace("/action", "", Core::$core->url);
            //! load javascript library
            View::jslib("cms.js", "pe.cms.init(".
                intval(@$_SESSION['cms_scroll'][0]).",".
                intval(@$_SESSION['cms_scroll'][1]).
                ");",9);
            $_SESSION['cms_scroll']=[];
        } else
            View::jslib("cms.js","",9);
    }


/**
 * Generate icon for various cms modals. Called by View::_t()
 *
 * @param tag
 * @param addon type
 * @param addon instance
 *
 * @return html code
 */
    static function icon($tag, $type, &$addon)
    {
        //! be paranoid. Extra check on user rights
        if (!Core::$user->has("siteadm|webadm")) {
            return "";
        }
        //! disable caching if at least one icon is shown
        Core::$core->nocache = true;

        //! get forced sizes from tag
        if(!preg_match("/^cms\(([0-9\%]+),?([0-9\%]*),?([0-9\%]*),?([0-9\%]*)\)/", $tag, $sizes))
            $sizes=["", 0, 0, 0, 0];

        $title = !empty($addon->name)?$addon->name:$type;
        //! save the page parameter
        $idx=sha1($type."_".$addon->fld);
        $_SESSION['cms_param'][$idx] = $addon;
        //! edit arguments
        
        //! return icon
        return "<img style='position:absolute;z-index:997;cursor:pointer;opacity:0.7;' ".
            "onclick='pe.cms.edit(this,\"".$idx."\",".
                (is_array(@$addon->adjust)?json_encode($addon->adjust):intval(@$addon->adjust)).",".
                intval(@$addon->minWidth).",".intval(@$addon->minHeight).",".
                intval(!empty($sizes[1])?$sizes[1]:@$addon->forceWidth).",".
                intval(!empty($sizes[2])?$sizes[2]:@$addon->forceHeight).",".
                intval(!empty($sizes[3])?$sizes[3]:@$addon->forceFull).");' ".
            "src='images/cms/".(file_exists(__DIR__."/../images/cms/".$type.".png")?urlencode($type):"edit").".png' ".
            "alt='[".htmlspecialchars(strtoupper($type)." ".$title)."]' ".
            "title='".htmlspecialchars(L($title))."'>";
    }

/**
 * Helper function to create stat icons
 */
    private static function statIcon($name)
    {
                $d="\\PHPPE\\AddOn\\$name";
                $addon = new $d([],$name,$name);
                $idx=sha1($name."_");
                $_SESSION['cms_param'][$idx] = $addon;
                return "<img style='cursor:pointer;' ".
                "onclick='pe.cms.edit(this,\"".$idx."\",".
                    intval(@$addon->adjust).",".intval(@$addon->minWidth).",".intval(@$addon->minHeight).",".
                    intval(@$addon->forceWidth).",".
                    intval(@$addon->forceHeight).",".
                    intval(@$addon->forceFull).");' ".
                "src='images/cms/".$name.".png' ".
                "alt='[".strtoupper($name)."]' ".
                "title='".htmlspecialchars(L($name))."'>";
    }
/**
 * Status block event handler
 */
    function stat()
    {
        $ret = "";
        if (Core::$user->has("siteadm|webadm")) {
            //! if we are on an editable page
            $app=\PHPPE\View::getval("app");
            if (get_class($app)=="PHPPE\Content") {
                //! unpiblished warning
                if(!$app->publishid)
                    $ret.="<a href='".url("cms/pages")."?publish'><span class='btn-warning' style='padding:0px 4px 0px 4px;text-shadow:none;'>".L("UNPUBLISHED")."</span></a>";
                //! page info
                $ret .= self::statIcon("pageinfo");
                //! page meta
                $ret .= self::statIcon("pagemeta");
                //! page dds icon
                if (Core::$user->has("siteadm")) {
                    $ret .= self::statIcon("pagedds");
                }
                //! page history
                if ($this->revert)
                    $ret .= self::statIcon("pagehist");
                //! page delete
                $ret .= "<img style='cursor:pointer;' ".
                "onclick='pe.cms.pagedel(\"".addslashes(Core::$core->url)."\");' ".
                "src='images/cms/pagedel.png' ".
                "alt='[PAGEDEL]' ".
                "title='".htmlspecialchars(L('pagedel'))."'>";
            }
            if (get_class($app)=="PHPPE\Ctrl\CMSArchive" && $this->revert) {
                $u = Core::$core->url."?created=".urlencode(trim($_REQUEST['created']));
                //! page version delete
                $ret .= "<img style='cursor:pointer;' ".
                "onclick='document.location.href=\"".$u.(isset($_REQUEST['diff'])?'':'&diff')."\";' ".
                "src='images/cms/pagediff.png' ".
                "alt='[PAGEDIFF]' ".
                "title='".htmlspecialchars(L('pagediff'))."'>";
                //! revert page to version
                $ret .= "<img style='cursor:pointer;' ".
                "onclick='document.location.href=\"".$u."&revert\";' ".
                "src='images/cms/pagerevert.png' ".
                "alt='[PAGEREVERT]' ".
                "title='".htmlspecialchars(L('pagerevert'))."'>";
                //! page history
                $ret .= self::statIcon("pagehist");
                //! page version delete
                $ret .= "<img style='cursor:pointer;' ".
                "onclick='document.location.href=\"".$u."&pagedel\";' ".
                "src='images/cms/pagedel.png' ".
                "alt='[PAGEDEL]' ".
                "title='".htmlspecialchars(L('pagedel'))."'>";

            }
            //! page add
            $ret .= self::statIcon("pageadd");
        }
        return $ret;
    }

/**
 * helper function to mark a specific tag in html
 *
 * @return html with marked tags
 */
	static function taghtml($data)
	{
			$t="";
			if(preg_match("|<body[^>]*>(.*)</body>|ims",$data,$m)) {
				$id=1;
				for($i=0;$i<strlen($m[1]);$i++){
					if($m[1][$i]=='<'&&$m[1][$i+1]!='/') {
						while($m[1][$i]!=''&&$m[1][$i]!=' '&&$m[1][$i]!='>'&&$m[1][$i]!="\t"&&$m[1][$i]!="\n"&&$m[1][$i]!="\r")
							$t.=$m[1][$i++];
						$t.=" data-chooseid='".$id++."'";
					}
					$t.=$m[1][$i];
				}
			}
			return $t;
	}

/**
 * split html according to marked tags
 *
 * @param html with marked tags
 * @param id of a tag
 * @param selector: 1=return html after tag, 0=return tag for id, -1=return html before tag
 * @return html code
 */
	static function splithtml($data,$id,$idx=1)
	{
		if(!preg_match("|data-chooseid|",$data))
			$data=self::taghtml($data);
		if($id==0)
			return $idx==1?preg_replace("| data-chooseid='[0-9]+'|ims","",trim($data)):"";
		if( preg_match_all( "/<([^\ \t\r\n]+)[\ \t\r\n]data-chooseid='".$id."'[^>]*>/ims", $data,$T,PREG_OFFSET_CAPTURE | PREG_SET_ORDER ) )
		{
			if($idx==0)
				return preg_replace("| data-chooseid='[0-9]+'|ims","",trim(substr($data,0,$T[0][0][1])));
			$c=0;
			$i=$T[0][0][1];$lc=$i;while($data[$lc]!=''&&$data[$lc]!='>') $lc++;$lc++;
			for(;$i<strlen($data);$i++) {
				if(strtolower(substr($data,$i,strlen($T[0][1][0])+1))=='<'.strtolower($T[0][1][0]) ) $c++;
				if(strtolower(substr($data,$i,strlen($T[0][1][0])+2))=='</'.strtolower($T[0][1][0]) ) $c--;
				if($c==0) {
					while($data[$i]!=''&&$data[$i]!='>') $i++;$i++;$lc=$i;
					return preg_replace("| data-chooseid='[0-9]+'|ims","",trim($idx==1? substr($data,$T[0][0][1],$i-$T[0][0][1]) : substr($data,$i)));
				}
			}
			if($c!=0) return preg_replace("| data-chooseid='[0-9]+'|ims","",trim($idx==1? substr($data,$T[0][0][1],$lc-$T[0][0][1]) : substr($data,$lc)));
		}
		return "";
	}
}
