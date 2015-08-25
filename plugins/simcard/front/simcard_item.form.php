<?php
/*
 * @version $Id$
 LICENSE

  This file is part of the simcard plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Simcard. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   simcard
 @author    the simcard plugin team
 @copyright Copyright (c) 2010-2011 Simcard plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/simcard
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

PluginSimcardSimcard::canUpdate();

$simcard_item = new PluginSimcardSimcard_Item();
if (isset($_POST["additem"])) {
	$simcard_item->can(-1, CREATE, $_POST);
   if ($newID = $simcard_item->add($_POST)) {
   }
} else if (isset($_POST["delete_items"])) {
   if (isset($_POST['todelete'])) {
      foreach ($_POST['todelete'] as $id => $val) {
         if ($val == 'on') {
            $simcard_item->can($id, DELETE, $_POST);
            $ok = $simcard_item->delete(array('id' => $id));
         }
      }
   }
}
Html::back();
