<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Shellcommands plugin for GLPI
  Copyright (C) 2003-2011 by the Shellcommands Development Team.

  https://forge.indepnet.net/projects/shellcommands
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Shellcommands.

  Shellcommands is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Shellcommands is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with shellcommands. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!isset($_GET["id"]))
   $_GET["id"] = "";
if (!isset($_GET["withtemplate"]))
   $_GET["withtemplate"] = "";

$command = new PluginShellcommandsShellcommand();
$command_item = new PluginShellcommandsShellcommand_Item();

if (isset($_POST["add"])) {
   $command->check(-1, UPDATE, $_POST);
   $newID = $command->add($_POST);
   Html::back();
   
} else if (isset($_POST["update"])) {
   $command->check($_POST['id'], UPDATE);
   $command->update($_POST);
   Html::back();
   
} else if (isset($_POST["additem"])) {
   if (!empty($_POST['itemtype'])) {
      if ($command->canCreate())
         $command_item->addItem($_POST["plugin_shellcommands_shellcommands_id"], $_POST['itemtype']);
   }
   Html::back();
   
} else if (isset($_POST["deleteitem"])) {
   if ($command->canCreate())
      $command_item->delete(array('id' => $_POST["id"]));
   Html::back();
   
} else {
   $command->checkGlobal(READ);
   Html::header(PluginShellcommandsShellcommand::getTypeName(2), '', "tools", "pluginshellcommandsshellcommand", "shellcommand");
   $command->display(array('id' => $_GET["id"]));
   Html::footer();
}
?>