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

function plugin_moreticket_install() {
   global $DB;

   include_once (GLPI_ROOT."/plugins/moreticket/inc/profile.class.php");

   if (!TableExists("glpi_plugin_moreticket_configs")) {
      // table sql creation
      $DB->runFile(GLPI_ROOT."/plugins/moreticket/sql/empty-1.2.0.sql");
   }
   
   PluginMoreticketProfile::initProfile();
   PluginMoreticketProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   $migration = new Migration("1.1.0");
   $migration->dropTable('glpi_plugin_moreticket_profiles');
   
   if (!FieldExists("glpi_plugin_moreticket_configs", "solution_status")) {
      $DB->runFile(GLPI_ROOT."/plugins/moreticket/sql/update-1.1.1.sql");
   }
      
   if (FieldExists("glpi_plugin_moreticket_waitingtypes", "is_helpdeskvisible")) {
      $DB->runFile(GLPI_ROOT."/plugins/moreticket/sql/update-1.1.2.sql");
   }
   
   if (!FieldExists("glpi_plugin_moreticket_closetickets", "documents_id")) {
      $DB->runFile(GLPI_ROOT."/plugins/moreticket/sql/update-1.1.3.sql");
   }
   
   if (!FieldExists("glpi_plugin_moreticket_configs", "date_report_mandatory")) {
      $DB->runFile(GLPI_ROOT."/plugins/moreticket/sql/update-1.2.0.sql");
   }
   
   return true;
}

// Uninstall process for plugin : need to return true if succeeded
function plugin_moreticket_uninstall() {
   global $DB;

   // Plugin tables deletion
   $tables = array("glpi_plugin_moreticket_configs",
                   "glpi_plugin_moreticket_waitingtickets",
                   "glpi_plugin_moreticket_waitingtypes",
                   "glpi_plugin_moreticket_closetickets");

   foreach ($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   
   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginMoreticketProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(array('name' => $right['field']));
   }
   
   return true;
}

function plugin_moreticket_postinit() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['moreticket'] = array();
   $PLUGIN_HOOKS['item_add']['moreticket'] = array();
}

// Define dropdown relations
function plugin_moreticket_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("moreticket"))
      return array("glpi_entities"                       => array("glpi_plugin_moreticket_waitingtypes"   => "entities_id"),
                   "glpi_tickets"                        => array("glpi_plugin_moreticket_waitingtickets" => "tickets_id"),
                   "glpi_plugin_moreticket_waitingtypes" => array("glpi_plugin_moreticket_waitingtickets" => "plugin_moreticket_waitingtypes_id"),
                   "glpi_tickets"                        => array("glpi_plugin_moreticket_closetickets"   => "tickets_id"));
   else
      return array();
}

// Define Dropdown tables to be manage in GLPI :
function plugin_moreticket_getDropdown() {

   $plugin = new Plugin();

   if ($plugin->isActivated("moreticket"))
      return array('PluginMoreticketWaitingType'=>PluginMoreticketWaitingType::getTypeName(2));
   else
      return array();
}

// Hook done on purge item case
function plugin_pre_item_purge_moreticket($item) {
   
   switch (get_class($item)) {
      case 'Ticket' :
         $temp = new PluginMoreticketWaitingTicket();
         $temp->deleteByCriteria(array('tickets_id' => $item->getField('id')));
         break;
   }
}


////// SEARCH FUNCTIONS ///////() {

// Define search option for types of the plugins
function plugin_moreticket_getAddSearchOptions($itemtype) {

    $sopt=array();

   if ($itemtype == "Ticket") {
      if (Session::haveRight("plugin_moreticket", READ)) {
         
         $config = new PluginMoreticketConfig();

         $sopt[3451]['table']          = 'glpi_plugin_moreticket_waitingtickets';
         $sopt[3451]['field']          = 'date_report';
         $sopt[3451]['name']           = __('Postponement date', 'moreticket');
         $sopt[3451]['datatype']       = "datetime";
         $sopt[3451]['joinparams']     = array('jointype' => 'child');
         $sopt[3451]['massiveaction']  = false;
         
         $sopt[3452]['table']          = 'glpi_plugin_moreticket_waitingtypes';
         $sopt[3452]['field']          = 'name';
         $sopt[3452]['name']           = PluginMoreticketWaitingType::getTypeName(1);
         $sopt[3452]['datatype']       = "dropdown";
         $sopt[3452]['joinparams']     = array('beforejoin'
                                             => array('table'      => 'glpi_plugin_moreticket_waitingtickets',
                                                      'joinparams' => array('jointype' => 'child')));
         $sopt[3452]['massiveaction']  = false;
         
         if ($config->closeInformations()) {
            $sopt[3453]['table']         = 'glpi_plugin_moreticket_closetickets';
            $sopt[3453]['field']         = 'date';
            $sopt[3453]['name']          = __('Close ticket informations', 'moreticket')." : ".__('Date');
            $sopt[3453]['datatype']      = "datetime";
            $sopt[3453]['joinparams']    = array('jointype' => 'child');
            $sopt[3453]['massiveaction'] = false;

            $sopt[3454]['table']         = 'glpi_plugin_moreticket_closetickets';
            $sopt[3454]['field']         = 'comment';
            $sopt[3454]['name']          = __('Close ticket informations', 'moreticket')." : ".__('Comments');
            $sopt[3454]['datatype']      = "text";
            $sopt[3454]['joinparams']    = array('jointype' => 'child');
            $sopt[3454]['massiveaction'] = false;

            $sopt[3455]['table']         = 'glpi_plugin_moreticket_closetickets';
            $sopt[3455]['field']         = 'requesters_id';
            $sopt[3455]['name']          = __('Close ticket informations', 'moreticket')." : ".__('Writer');
            $sopt[3455]['datatype']      = "dropdown";
            $sopt[3455]['joinparams']    = array('jointype' => 'child');
            $sopt[3455]['massiveaction'] = false;
            
            $sopt[3486]['table']            = 'glpi_documents';
            $sopt[3486]['field']            = 'name';
            $sopt[3486]['name']             = __('Close ticket informations', 'moreticket')." : "._n('Document', 'Documents', Session::getPluralNumber());
            $sopt[3486]['forcegroupby']     = true;
            $sopt[3486]['usehaving']        = true;
            $sopt[3486]['datatype']         = 'dropdown';
            $sopt[3486]['massiveaction']    = false;
            $sopt[3486]['joinparams']       = array('beforejoin' => array('table'      => 'glpi_documents_items',
                                                                          'joinparams' => array('jointype'          => 'itemtype_item',
                                                                                                'specific_itemtype' => 'PluginMoreticketCloseTicket',
                                                                                                'beforejoin'        => array('table'      => 'glpi_plugin_moreticket_closetickets',
                                                                                                                             'joinparams' => array()))));
         }
      }
   }
   return $sopt;
}

function plugin_moreticket_addWhere($link, $nott, $type, $ID, $val) {

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   switch ($table.".".$field) {
      case "glpi_plugin_moreticket_waitingtickets.date_report" :
         $criteria = array();
         $query    = "";
         foreach ($_GET['criteria'] as $key => $search_item) {
            if (in_array($search_item['field'], array_keys($searchopt)) && $search_item['field'] == $ID) {
               $NOT = $nott ? "NOT" : "";

               $SEARCH = "";
               switch ($search_item['searchtype']) {
                  case 'morethan':
                     $SEARCH = "> '".$val."'";
                     break;
                  case 'lessthan':
                     $SEARCH = "< '".$val."'";
                     break;
                  case 'equals':
                     $SEARCH = "= '".$val."'";
                     break;
                  case 'notequals':
                     $SEARCH = "!= '".$val."'";
                     break;
                  case 'contains':
                     $SEARCH = "LIKE '%".$val."%'";
                     if ($val == 'NULL') {
                        $SEARCH = "IS NULL";
                     }
                     break;
               }

               $query = " ".$link." ".$NOT." (`".$table."`.`".$field."` ".$SEARCH;
               if ($search_item['searchtype'] != 'contains') {
                  $query .= " OR `".$table."`.`".$field."` IS NULL";
               }
               $query .= ")";
            }
         }

         return $query;
   }

   return "";
}

function plugin_moreticket_addSelect($type, $ID, $num) {

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   // Example of standard Select clause but use it ONLY for specific Select
   // No need of the function if you do not have specific cases
   switch ($table.".".$field) {
      case "glpi_plugin_moreticket_waitingtickets.date_report":
         return "max(`".$table."`.`".$field."`) AS ITEM_$num, ";
   }
   return "";
}
?>