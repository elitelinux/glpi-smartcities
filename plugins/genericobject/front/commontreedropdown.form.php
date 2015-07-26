<?php
/*
 This file is part of the genericobject plugin.

 Genericobject plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Genericobject plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Genericobject. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   genericobject
 @author    the genericobject plugin team
 @copyright Copyright (c) 2010-2014 Generic Object plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/genericobject
 @link      http://www.glpi-project.org/
 @since     2014
 ---------------------------------------------------------------------- */

include ("../../../inc/includes.php");

if ( isset($_REQUEST['itemtype']) ) {
   $itemtype = $_REQUEST['itemtype'];
   if (class_exists($itemtype)) {
      $dropdown = new $itemtype();
      include (GLPI_ROOT . "/front/dropdown.common.form.php");
   } else {
      Html::displayErrorAndDie(__('The requested dropdown does not exists', 'genericobject'));
   }
} else {

   Html::displayErrorAndDie(__('Not Found!'));
}
