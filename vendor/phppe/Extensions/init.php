<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016
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
 * @file vendor/phppe/Extensions/init.php
 * @author bzt
 * @date 23 May 2016
 * @brief
 */
namespace PHPPE;

\PHPPE\View::menu( L("Extensions") ."@install", "extensions" );
return new Extensions;

/* make lang utility happy
L("Also installs") L("Failed dependency") L("Proprietary") L("Framework") L("Connections") L("Themes") L("Content")
L("Security") L("Business") L("Sales") L("Office") L("Games") L("Banners") L("Hardware") L("User Input")
*/