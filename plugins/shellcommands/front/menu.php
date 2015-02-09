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

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   Html::header(PluginShellcommandsMenu::getTypeName(2), '', "tools", "pluginshellcommandsshellcommand");
} else {
   Html::helpHeader(PluginShellcommandsMenu::getTypeName(2));
}

$menu = new PluginShellcommandsMenu();
$menu->showMenu();

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}
?>