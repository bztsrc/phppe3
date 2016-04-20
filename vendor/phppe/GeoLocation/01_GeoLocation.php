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
 * @file vendor/phppe/GeoLocation/01_GeoLocation.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief Provides PHPPE::$client->geo array.
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

/**
 * Main class
 *
 */
class GeoLocation
{
/**
 * Register GeoLocation
 *
 * @param cfg not used
 */
	function init($cfg) {
		//register ourself
		PHPPE::lib("Geo","GeoLocation");
		//! check whether we are enabled
		if(empty($cfg['type']))
			return false;

		if(PHPPE::$client->ip!="CLI") {
			//! AJAX helper to save javascript variables into php session
			if(PHPPE::$core->app=="geo" && !empty($_REQUEST['pe_s']) && $_REQUEST['pe_s']==$_SESSION['pe_s']['geo.action']) {
				foreach($_GET as $k=>$v)
					if($k!="pe_s")
						$_SESSION['pe_geo'][$k]=$v;
				//! mark that we have a high accuracy latitude and longitude values from client
				$_SESSION['pe_geoclient']=1;
				die("OK");
			}

			//! query location
			//! 1-server side
			if(function_exists("geoip_record_by_name")) {
				if(empty($_SESSION['pe_geo']))
					$_SESSION['pe_geo']=geoip_record_by_name(PHPPE::$client->ip);
			}
			//! 2-client side
			if($cfg['type']==2 && empty($_SESSION['pe_geoclient']))
				PHPPE::js("init()","if(navigator.geolocation)navigator.geolocation.getCurrentPosition(pe_geo,pe_geo);",true);
			//! 3-client tracking
			elseif($cfg['type']==3)
				PHPPE::js("init()","if(navigator.geolocation)navigator.geolocation.watchPosition(pe_geo,pe_geo);",true);
			//! 4-remote ipinfo.io
			elseif($cfg['type']==4 && empty($_SESSION['pe_geoclient'])){
				$ret=@json_decode(PHPPE::get("http://ipinfo.io/".PHPPE::$client->ip),true);
				if(is_array($ret) && !empty($ret['ip'])) {
					$r=explode(",",$ret['loc']);
					$_SESSION['pe_geo']['postal_code']=$ret['postal'];
					$_SESSION['pe_geo']['country_code']=$ret['country'];
					$_SESSION['pe_geo']['region']=$ret['region'];
					$_SESSION['pe_geo']['city']=$ret['city'];
					$_SESSION['pe_geo']['latitude']=floatval($r[0]);
					$_SESSION['pe_geo']['longitude']=floatval($r[1]);
					$_SESSION['pe_geoclient']=1;
				}
			}
			//! both 2 and 3 needs this helper function to make an AJAX call
			if($cfg['type']==2 && empty($_SESSION['pe_geoclient']) || $cfg['type']==3)
				PHPPE::js("pe_geo(p)","var s='',k,h=new XMLHttpRequest();console.log(p);if(p.code){if(document.body.getAttribute('data-geowarn')==null){document.body.setAttribute('data-geowarn',1);alert('E-GEO: '+L(p.message));}return;}else{document.body.removeAttribute('data-geowarn');for(k in p.coords)if(p.coords.hasOwnProperty(k)){s+='&'+encodeURIComponent(k)+'='+encodeURIComponent(p.coords[k]);}h.open('GET','".PHPPE::url("geo")."?pe_s=".urlencode($_SESSION['pe_s']['geo.action'])."&timestamp='+(p.timestamp?p.timestamp:0)+s,true);h.send(null);}");

			//! validation
			if(!is_array($_SESSION['pe_geo']))
				$_SESSION['pe_geo']=[];
			$_SESSION['pe_geo']['postal_code']=trim($_SESSION['pe_geo']['postal_code']);
			$_SESSION['pe_geo']['country_code']=trim(strtoupper($_SESSION['pe_geo']['country_code']));
			$_SESSION['pe_geo']['region']=trim($_SESSION['pe_geo']['region']);
			$_SESSION['pe_geo']['city']=trim($_SESSION['pe_geo']['city']);
			$_SESSION['pe_geo']['latitude']=floatval($_SESSION['pe_geo']['latitude']);
			$_SESSION['pe_geo']['longitude']=floatval($_SESSION['pe_geo']['longitude']);
			$_SESSION['pe_geo']['altitude']=floatval($_SESSION['pe_geo']['altitude']);
			//! set geo information on client object
			PHPPE::$client->geo=$_SESSION['pe_geo'];
		}
        return true;
	}
}
