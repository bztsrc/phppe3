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
 * @file vendor/phppe/core/libs/pass.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief this file should define a password validator
 */
namespace PHPPE;

//validation defaults to lower and uppercase letters
//plus numbers, at least 6 characters

//this function can also be overridden in app/libs/pass.php

/**
 * value validator, returns boolean and a failure reason string in an array
 *
 * @param name of value to validate, for error reporting
 * @param reference to value
 * @param arguments
 * @param attributes
 * @return array(boolean,error message) if the first value is true, it's valid
 */
// function pass( $name, &$value,$args,$attrs )
// {
//	//! do checks here
//	if( strlen( $value ) < 10 ) return [ false, "password too short" ];
//	return [ true, "OK" ];
// }
