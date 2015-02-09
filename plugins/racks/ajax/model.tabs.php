<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Racks plugin for GLPI
 Copyright (C) 2003-2011 by the Racks Development Team.

 https://forge.indepnet.net/projects/racks
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Racks.

 Racks is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Racks is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Racks. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!isset($_POST["id"])) {
   exit();
}

$PluginRacksItemSpecification = new PluginRacksItemSpecification();

$PluginRacksItemSpecification->checkGlobal("r");

if (empty($_POST["id"])) {
   switch($_POST['plugin_racks_tab']) {
      default :
         break;
   }
} else {
   
   $target = $CFG_GLPI['root_doc']."/plugins/racks/front/itemspecification.form.php";
   
   switch($_POST['plugin_racks_tab']) {
      case "all" :
         $_SESSION['glpi_plugin_racks_tab']="all";
         $PluginRacksItemSpecification->showForm($target,'Computer');
         $PluginRacksItemSpecification->showForm($target,'NetworkEquipment');
         $PluginRacksItemSpecification->showForm($target,'Peripheral');
         $PluginRacksItemSpecification->showForm($target,'PluginRacksOther');
         break;
      case 'Computer' :
         $_SESSION['glpi_plugin_racks_tab']='Computer';
         $PluginRacksItemSpecification->showForm($target,$_SESSION['glpi_plugin_racks_tab']);
         break;
      case 'NetworkEquipment' :
         $_SESSION['glpi_plugin_racks_tab']='NetworkEquipment';
         $PluginRacksItemSpecification->showForm($target,$_SESSION['glpi_plugin_racks_tab']);
         break;
      case 'Peripheral' :
         $_SESSION['glpi_plugin_racks_tab']='Peripheral';
         $PluginRacksItemSpecification->showForm($target,$_SESSION['glpi_plugin_racks_tab']);
         break;
      case 'PluginRacksOther' :
         $_SESSION['glpi_plugin_racks_tab']='PluginRacksOther';
         $PluginRacksItemSpecification->showForm($target,$_SESSION['glpi_plugin_racks_tab']);
         break;
      default :
         break;
   }
}

Html::ajaxFooter();

?>