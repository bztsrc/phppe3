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
 * @file vendor/phppe/Developer/init.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief Initialize Developer extension
 */

/*!SKIPAUTOLOAD!*/
namespace PHPPE;

//! register menu
View::menu( L("Developer"), [
    L("Tests")=>"tests",
    L("Benchmarks")=>"benchmark"
]);

//! this is needed for test cases
Http::route("tests/httptest", "Developer", "action_httpget", "get");
Http::route("tests/httptest", "Developer", "action_httppost", "post");

//! register url routes.
Http::route("tests", "Developer");
Http::route("benchmark", "BenchmarkController");
//! these are CLI only
Http::route("mkrepo", "MkRepoController");
Http::route("minify", "MinifyController","action");
Http::route("create", "CreateController");
Http::route("lang",   "LangController");
Http::route("pretty", "PrettyController","action");
Http::route("datauri","DatauriController","action");

//for event testing
return new Testing;
?>
