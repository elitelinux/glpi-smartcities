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

function plugin_financialreports_install() {
   global $DB;
   
   include_once (GLPI_ROOT."/plugins/financialreports/inc/profile.class.php");
   
   $update=false;
   if (!TableExists("glpi_plugin_state_profiles") 
            && !TableExists("glpi_plugin_financialreports_configs")) {
      
      $DB->runFile(GLPI_ROOT ."/plugins/financialreports/sql/empty-2.1.0.sql");

   } else if (TableExists("glpi_plugin_state_parameters") 
            && !FieldExists("glpi_plugin_state_parameters","monitor")) {
      
      $update=true;
      $DB->runFile(GLPI_ROOT ."/plugins/financialreports/sql/update-1.5.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/financialreports/sql/update-1.6.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/financialreports/sql/update-1.7.0.sql");

   } else if (TableExists("glpi_plugin_state_profiles") 
            && FieldExists("glpi_plugin_state_profiles","interface")) {
      
      $update=true;
      $DB->runFile(GLPI_ROOT ."/plugins/financialreports/sql/update-1.6.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/financialreports/sql/update-1.7.0.sql");

   } else if (!TableExists("glpi_plugin_financialreports_configs")) {
      
      $update=true;
      $DB->runFile(GLPI_ROOT ."/plugins/financialreports/sql/update-1.7.0.sql");

   }
   
   if ($update) {
      
      //Do One time on 0.78
      $query_="SELECT *
            FROM `glpi_plugin_financialreports_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_financialreports_profiles`
                  SET `profiles_id` = '".$data["id"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }
      
      $query="ALTER TABLE `glpi_plugin_financialreports_profiles`
               DROP `name` ;";
      $result=$DB->query($query);
      
      Plugin::migrateItemType(
         array(3450=>'PluginFinancialreportsDisposalItem'),
         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"),
         array("glpi_plugin_financialreports_disposalitems"));
   }

   //Migrate profiles to the new system
   PluginFinancialreportsProfile::initProfile();
   PluginFinancialreportsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   
   $migration = new Migration("2.0.0");
   $migration->dropTable('glpi_plugin_financialreports_profiles');
   return true;
}

function plugin_financialreports_uninstall() {
   global $DB;

   $tables = array("glpi_plugin_financialreports_configs",
               "glpi_plugin_financialreports_parameters",
               "glpi_plugin_financialreports_disposalitems");

   foreach($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   
   //old versions	
   $tables = array("glpi_plugin_financialreports_profiles",
               "glpi_plugin_state_profiles",
               "glpi_plugin_state_config",
               "glpi_plugin_state_parameters",
               "glpi_plugin_state_repelled");

   foreach($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

   return true;
}

function plugin_financialreports_postinit() {
   global $CFG_GLPI, $PLUGIN_HOOKS;

   foreach (PluginFinancialreportsDisposalItem::getTypes(true) as $type) {
      CommonGLPI::registerStandardTab($type, 'PluginFinancialreportsDisposalItem');
   }
}

// Define database relations
function plugin_financialreports_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("financialreports"))
      return array (
         "glpi_states" => array ("glpi_plugin_financialreports_configs" => "states_id")
      );
   else
      return array ();
}

////// SEARCH FUNCTIONS ///////() {

// Define search option for types of the plugins
function plugin_financialreports_getAddSearchOptions($itemtype) {

    $sopt=array();

   if (in_array($itemtype, PluginFinancialreportsDisposalItem::getTypes())) {
      if (Session::haveRight("plugin_financialreports", READ)) {
         $sopt[3450]['table'] = 'glpi_plugin_financialreports_disposalitems';
         $sopt[3450]['field'] = 'date_disposal';
         $sopt[3450]['linkfield'] = '';
         $sopt[3450]['name'] = __('Asset situation', 'financialreports')." - ".
                              __('Disposal date', 'financialreports');
         $sopt[3450]['forcegroupby'] = true;
         $sopt[3450]['datatype'] = 'date';
         $sopt[3450]['massiveaction'] = false;
         $sopt[3450]['joinparams'] = array('jointype'  => 'itemtype_item');
      }
   }
   return $sopt;
}

//force groupby for multible links to items
function plugin_financialreports_forceGroupBy($type) {

   return true;
   switch ($type) {
      case 'PluginFinancialreportsDisposalItem':
         return true;
         break;

   }
   return false;
}

function plugin_financialreports_MassiveActions($type) {

   if (in_array($type,PluginFinancialreportsDisposalItem::getTypes())) {
      return array('PluginFinancialreportsDisposalItem'.MassiveAction::CLASS_ACTION_SEPARATOR.'plugin_financialreports_add_date_disposal' =>
                                                              __('Indicate the date of disposal', 'financialreports'));
   }
   return array();
}
?>