<?php

/*
  -------------------------------------------------------------------------
  Moreticket plugin for GLPI
  Copyright (C) 2013 by the Moreticket Development Team.
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Moreticket.

  Moreticket is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Moreticket is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Moreticket. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

$plugin = new Plugin();
if ($plugin->isActivated("moreticket")) {

   $config = new PluginMoreticketConfig();

   if (isset($_POST["update"])) {
      if (isset($_POST['solution_status'])) {
         $_POST['solution_status'] = json_encode($_POST['solution_status']);
      } else {
         $_POST['solution_status'] = "";
      }
     
      $config->update($_POST);
      //Update singelton
      PluginMoreticketConfig::getConfig(true);
      Html::redirect($_SERVER['HTTP_REFERER']);
      
   } else {
      Html::header(PluginMoreticketConfig::getTypeName(), '', "plugins", "moreticket");
      $config->showForm();
      Html::footer();
   }
   
} else {
   Html::header(__('Setup'), '', "config", "plugins");
   echo "<div align='center'><br><br>";
   echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt='warning'><br><br>";
   echo "<b>".__('Please activate the plugin', 'moreticket')."</b></div>";
   Html::footer();
}
?>