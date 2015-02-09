<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Financialreports plugin for GLPI
 Copyright (C) 2003-2011 by the Financialreports Development Team.

 https://forge.indepnet.net/projects/financialreports
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Financialreports.

 Financialreports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Financialreports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Financialreports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

$plugin = new Plugin();
if ($plugin->isActivated("financialreports")) {

   Session::checkRight("config",UPDATE);

   $param=new PluginFinancialreportsParameter();
   $config=new PluginFinancialreportsConfig();

   if (isset($_POST["add_state"])) {

      $config->add($_POST);
      Html::back();

   } else if (isset($_POST["delete_state"])) {
      
      foreach ($_POST["item"] as $ID => $value) {
         $config->delete(array("id"=>$ID),1);
      }
      
      Html::back();

   } else if (isset($_POST["update_parameters"])) {

      $param->update($_POST);
      Html::back();

   } else {

      Html::header(__('Setup'),'',"config","plugins");
      $param->showForm();
      $config->showForm();
      
   }

} else {
   Html::header(__('Setup'),'',"config","plugins");
   echo "<div align='center'><br><br>";
   echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>";
   echo "<b>".__('Please activate the plugin', 'financialreports')."</b></div>";
}

Html::footer();

?>