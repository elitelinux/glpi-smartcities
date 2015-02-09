<?php
/*
 -------------------------------------------------------------------------
 Shellcommands plugin for GLPI
 Copyright (C) 2014 by the Shellcommands Development Team.
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
 along with Shellcommands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------  */

include ('../../../inc/includes.php');

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

$commandgroup = new PluginShellcommandsCommandGroup();

if (isset($_POST["add"])) {
   // Check add rights for fields
   $commandgroup->check(-1, UPDATE, $_POST);
   $commandgroup->add($_POST);

   Html::back();

} elseif (isset($_POST["update"])) {
   // Check update rights for fields
   $commandgroup->check($_POST['id'], UPDATE, $_POST);
   $commandgroup->update($_POST);

   Html::back();

} elseif (isset($_POST["delete"])) {
   // Check delete rights for fields
   $commandgroup->check($_POST['id'], UPDATE, $_POST);
   $commandgroup->delete($_POST, 1);
   $commandgroup->redirectToList();
   
} else {
   $commandgroup->checkGlobal(READ);
   Html::header(PluginShellcommandsCommandGroup::getTypeName(2), '', "tools", "pluginshellcommandsshellcommand", "commandgroup");
   $commandgroup->display(array('id' => $_GET["id"]));
   Html::footer();
}
?>
