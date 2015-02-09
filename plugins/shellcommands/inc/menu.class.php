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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginShellcommandsMenu
 * 
 * This class shows the plugin main page
 * 
 * @package    Shellcommands
 * @author     Ludovic Dupont
 */
class PluginShellcommandsMenu extends CommonDBTM {
   
   static $rightname = 'plugin_shellcommands';

   static function getTypeName($nb=0) {
      return __('Shellcommands menu', 'shellcommands');
   }
   
   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, DELETE));
   }

   /**
    * Show config menu
    */
   function showMenu() {
      global $CFG_GLPI;
      
      if(!$this->canView()) return false;
      
      echo "<div align='center'>";
      echo "<table class='tab_cadre' cellpadding='5' height='150'>";
      echo "<tr>";
      echo "<th colspan='5'>".PluginShellcommandsShellcommand::getTypeName(2)."</th>";
      echo "</tr>";
      echo "<tr class='tab_bg_1' style='background-color:white;'>";

      // Add shell command
      echo "<td class='center shellcommands_menu_item'>";
      echo "<a  class='shellcommands_menu_a' href=\"./shellcommand.php\">";
      echo "<img class='shellcommands_menu_img' src='".$CFG_GLPI["root_doc"]."/plugins/shellcommands/pics/shellcommand.png' alt=\"".PluginShellcommandsShellcommand::getTypeName(2)."\">";
      echo "<br>".PluginShellcommandsShellcommand::getTypeName(2)."</a>";
      echo "</td>";
      
      // Command group
      echo "<td class='center shellcommands_menu_item'>";
      echo "<a  class='shellcommands_menu_a' href=\"./commandgroup.php\">";
      echo "<img class='shellcommands_menu_img' src='".$CFG_GLPI["root_doc"]."/plugins/shellcommands/pics/commandgroup.png' alt=\"".PluginShellcommandsCommandGroup::getTypeName(2)."\">";
      echo "<br>".PluginShellcommandsCommandGroup::getTypeName(2)."</a>";
      echo "</td>";
      
      // Advanced execution
      echo "<td class='center shellcommands_menu_item'>";
      echo "<a  class='shellcommands_menu_a' href=\"./advanced_execution.php\">";
      echo "<img class='shellcommands_menu_img' src='".$CFG_GLPI["root_doc"]."/plugins/shellcommands/pics/advanced_execution.png' alt=\"".PluginShellcommandsAdvanced_Execution::getTypeName(2)."\">";
      echo "<br>".PluginShellcommandsAdvanced_Execution::getTypeName(2)."</a>";
      echo "</td>";

      echo "</table></div>";
   }
}
?>