<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Webapplications plugin for GLPI
 Copyright (C) 2003-2011 by the Webapplications Development Team.

 https://forge.indepnet.net/projects/webapplications
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Webapplications.

 Webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_webapplications_install() {
   global $DB;

   include_once (GLPI_ROOT."/plugins/webapplications/inc/profile.class.php");

   $update = false;
   if (!TableExists("glpi_application")
       && !TableExists("glpi_plugin_appweb")
       && !TableExists("glpi_plugin_webapplications_webapplications")) {

      $DB->runFile(GLPI_ROOT ."/plugins/webapplications/sql/empty-2.0.0.sql");

   } else {
      
      if (TableExists("glpi_application") && !TableExists("glpi_plugin_appweb")) {
         $update = true;
         $DB->runFile(GLPI_ROOT ."/plugins/webapplications/sql/update-1.1.sql");
      }

      //from 1.1 version
      if (TableExists("glpi_plugin_appweb") && !FieldExists("glpi_plugin_appweb","location")) {
         $update = true;
         $DB->runFile(GLPI_ROOT ."/plugins/webapplications/sql/update-1.3.sql");
      }

      //from 1.3 version
      if (TableExists("glpi_plugin_appweb") && !FieldExists("glpi_plugin_appweb","recursive")) {
         $update = true;
         $DB->runFile(GLPI_ROOT ."/plugins/webapplications/sql/update-1.4.sql");
      }

      if (TableExists("glpi_plugin_appweb_profiles")
          && FieldExists("glpi_plugin_appweb_profiles","interface")) {
         $update = true;
         $DB->runFile(GLPI_ROOT ."/plugins/webapplications/sql/update-1.5.0.sql");
      }

      if (TableExists("glpi_plugin_appweb")
              && !FieldExists("glpi_plugin_appweb","helpdesk_visible")) {
         $update = true;
         $DB->runFile(GLPI_ROOT ."/plugins/webapplications/sql/update-1.5.1.sql");
      }

      if (!TableExists("glpi_plugin_webapplications_webapplications")) {
         $update = true;
         $DB->runFile(GLPI_ROOT ."/plugins/webapplications/sql/update-1.6.0.sql");
      }
      
      //from 1.6 version
      if (TableExists("glpi_plugin_webapplications_webapplications") 
         && !FieldExists("glpi_plugin_webapplications_webapplications","users_id_tech")) {
         $DB->runFile(GLPI_ROOT ."/plugins/webapplications/sql/update-1.8.0.sql");
      }
   }
   
   if (TableExists("glpi_plugin_webapplications_profiles")) {
   
      $notepad_tables = array('glpi_plugin_webapplications_webapplications');

      foreach ($notepad_tables as $t) {
         // Migrate data
         if (FieldExists($t, 'notepad')) {
            $query = "SELECT id, notepad
                      FROM `$t`
                      WHERE notepad IS NOT NULL
                            AND notepad <>'';";
            foreach ($DB->request($query) as $data) {
               $iq = "INSERT INTO `glpi_notepads`
                             (`itemtype`, `items_id`, `content`, `date`, `date_mod`)
                      VALUES ('".getItemTypeForTable($t)."', '".$data['id']."',
                              '".addslashes($data['notepad'])."', NOW(), NOW())";
               $DB->queryOrDie($iq, "0.85 migrate notepad data");
            }
            $query = "ALTER TABLE `glpi_plugin_webapplications_webapplications` DROP COLUMN `notepad`;";
            $DB->query($query);
         }
      }
   }

   if ($update) {
      $query_= "SELECT *
                FROM `glpi_plugin_webapplications_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query = "UPDATE `glpi_plugin_webapplications_profiles`
                      SET `profiles_id` = '".$data["id"]."'
                      WHERE `id` = '".$data["id"]."';";
            $result = $DB->query($query);
         }
      }

      $query = "ALTER TABLE `glpi_plugin_webapplications_profiles`
               DROP `name` ;";
      $result = $DB->query($query);

      Plugin::migrateItemType(array(1300 => 'PluginWebapplicationsWebapplication'),
                              array("glpi_bookmarks", "glpi_bookmarks_users",
                                    "glpi_displaypreferences", "glpi_documents_items",
                                    "glpi_infocoms", "glpi_logs", "glpi_tickets"),
                              array("glpi_plugin_webapplications_webapplications_items"));

      Plugin::migrateItemType(array(1200 => "PluginAppliancesAppliance"),
                              array("glpi_plugin_webapplications_webapplications_items"));
   }

   PluginWebapplicationsProfile::initProfile();
   PluginWebapplicationsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   $migration = new Migration("2.0.0");
   $migration->dropTable('glpi_plugin_webapplications_profiles');
   
   return true;
}


function plugin_webapplications_uninstall() {
   global $DB;
   
   include_once (GLPI_ROOT."/plugins/webapplications/inc/profile.class.php");
   include_once (GLPI_ROOT."/plugins/webapplications/inc/menu.class.php");
   
   $tables = array("glpi_plugin_webapplications_webapplications",
                   "glpi_plugin_webapplications_webapplicationtypes",
                   "glpi_plugin_webapplications_webapplicationservertypes",
                   "glpi_plugin_webapplications_webapplicationtechnics",
                   "glpi_plugin_webapplications_webapplications_items");

   foreach($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   //old versions
   $tables = array("glpi_plugin_appweb",
                   "glpi_dropdown_plugin_appweb_type",
                   "glpi_dropdown_plugin_appweb_server_type",
                   "glpi_dropdown_plugin_appweb_technic",
                   "glpi_plugin_appweb_device",
                   "glpi_plugin_appweb_profiles",
                   "glpi_plugin_webapplications_profiles");

   foreach($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   $tables_glpi = array("glpi_displaypreferences",
                        "glpi_documents_items",
                        "glpi_bookmarks",
                        "glpi_logs",
                        "glpi_notepads");

   foreach($tables_glpi as $table_glpi) {
      $DB->query("DELETE
                  FROM `$table_glpi`
                  WHERE `itemtype` = 'PluginWebapplicationsWebapplication'");
   }

   if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(array('itemtype' => 'PluginWebapplicationsWebapplication'));
   }
   
   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginWebapplicationsProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(array('name' => $right['field']));
   }
   PluginWebapplicationsMenu::removeRightsFromSession();
   PluginWebapplicationsProfile::removeRightsFromSession();

   return true;
}


// Define dropdown relations
function plugin_webapplications_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("webapplications")) {
      return array("glpi_plugin_webapplications_webapplicationtypes"
                        => array("glpi_plugin_webapplications_webapplications"
                                    => "plugin_webapplications_webapplicationtypes_id"),
                   "glpi_plugin_webapplications_webapplicationservertypes"
                        => array("glpi_plugin_webapplications_webapplications"
                                    =>"plugin_webapplications_webapplicationservertypes_id"),
                   "glpi_plugin_webapplications_webapplicationtechnics"
                        => array("glpi_plugin_webapplications_webapplications"
                                    =>"plugin_webapplications_webapplicationtechnics_id"),
                   "glpi_users"
                        => array("glpi_plugin_webapplications_webapplications" => "users_id_tech"),
                   "glpi_groups"
                        => array("glpi_plugin_webapplications_webapplications" => "groups_id_tech"),
                   "glpi_suppliers"
                        => array("glpi_plugin_webapplications_webapplications" => "suppliers_id"),
                   "glpi_manufacturers"
                        => array("glpi_plugin_webapplications_webapplications" => "manufacturers_id"),
                   "glpi_locations"
                        => array("glpi_plugin_webapplications_webapplications" => "locations_id"),
                   "glpi_plugin_webapplications_webapplications"
                        => array("glpi_plugin_webapplications_webapplications_items"
                                    => "plugin_webapplications_webapplications_id"),
                   "glpi_entities"
                        => array("glpi_plugin_webapplications_webapplications"     => "entities_id",
                                 "glpi_plugin_webapplications_webapplicationtypes" => "entities_id"));
   }
   return array();
}


// Define Dropdown tables to be manage in GLPI :
function plugin_webapplications_getDropdown() {

   $plugin = new Plugin();

   if ($plugin->isActivated("webapplications")) {
      return array('PluginWebapplicationsWebapplicationType'
                        => PluginWebapplicationsWebapplicationType::getTypeName(2),
                   'PluginWebapplicationsWebapplicationServerType'
                        => PluginWebapplicationsWebapplicationServerType::getTypeName(2),
                   'PluginWebapplicationsWebapplicationTechnic'
                        => PluginWebapplicationsWebapplicationTechnic::getTypeName(2));
   }
   return array();
}


function plugin_webapplications_AssignToTicket($types) {

   if (Session::haveRight("plugin_webapplications_open_ticket", "1")) {
      $types['PluginWebapplicationsWebapplication'] = PluginWebapplicationsWebapplication::getTypeName(2);
   }
   return $types;
}


////// SEARCH FUNCTIONS ///////() {

function plugin_webapplications_getAddSearchOptions($itemtype) {

   $sopt = array();

   if (in_array($itemtype, PluginWebapplicationsWebapplication::getTypes(true))) {
      
      if (Session::haveRight("plugin_webapplications", READ)) {
         $sopt[1310]['table']          = 'glpi_plugin_webapplications_webapplications';
         $sopt[1310]['field']          = 'name';
         $sopt[1310]['name']           = PluginWebapplicationsWebapplication::getTypeName(2)." - ".
                                         __('Name');
         $sopt[1310]['forcegroupby']   = true;
         $sopt[1310]['datatype']       = 'itemlink';
         $sopt[1310]['massiveaction']  = false;
         $sopt[1310]['itemlink_type']  = 'PluginWebapplicationsWebapplication';
         $sopt[1310]['joinparams']     = array('beforejoin'
                                                   => array('table'      => 'glpi_plugin_webapplications_webapplications_items',
                                                            'joinparams' => array('jointype' => 'itemtype_item')));
                                                            
         $sopt[1311]['table']          = 'glpi_plugin_webapplications_webapplicationtypes';
         $sopt[1311]['field']          = 'name';
         $sopt[1311]['name']           = PluginWebapplicationsWebapplication::getTypeName(2)." - ".
                                         PluginWebapplicationsWebapplicationType::getTypeName(1);
         $sopt[1311]['forcegroupby']   = true;
         $sopt[1311]['datatype']       = 'dropdown';
         $sopt[1311]['massiveaction']  = false;
         $sopt[1311]['joinparams']     = array('beforejoin' => array(
                                                      array('table'      => 'glpi_plugin_webapplications_webapplications',
                                                            'joinparams' => $sopt[1310]['joinparams'])));
      }
   }

   return $sopt;
}

//display custom fields in the search
function plugin_webapplications_giveItem($type, $ID, $data, $num) {
   global $CFG_GLPI, $DB;

   $searchopt  = &Search::getOptions($type);
   $table      = $searchopt[$ID]["table"];
   $field      = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      //display associated items with webapplications
      case "glpi_plugin_webapplications_webapplications_items.items_id" :
         $query_device     = "SELECT DISTINCT `itemtype`
                              FROM `glpi_plugin_webapplications_webapplications_items`
                              WHERE `plugin_webapplications_webapplications_id` = '".$data['id']."'
                              ORDER BY `itemtype`";
         $result_device    = $DB->query($query_device);
         $number_device    = $DB->numrows($result_device);
         $out              = '';
         $webapplications  = $data['id'];
         if ($number_device > 0) {
            for ($i=0 ; $i < $number_device ; $i++) {
               $column   = "name";
               $itemtype = $DB->result($result_device, $i, "itemtype");
               if (!class_exists($itemtype)) {
                  continue;
               }
               $item = new $itemtype();
               if ($item->canView()) {
                  $table_item = getTableForItemType($itemtype);

                  if ($itemtype != 'Entity') {
                     $query = "SELECT `".$table_item."`.*,
                                      `glpi_plugin_webapplications_webapplications_items`.`id` AS table_items_id,
                                      `glpi_entities`.`id` AS entity
                               FROM `glpi_plugin_webapplications_webapplications_items`,
                                    `".$table_item."`
                               LEFT JOIN `glpi_entities`
                                 ON (`glpi_entities`.`id` = `".$table_item."`.`entities_id`)
                               WHERE `".$table_item."`.`id` = `glpi_plugin_webapplications_webapplications_items`.`items_id`
                                     AND `glpi_plugin_webapplications_webapplications_items`.`itemtype` = '$itemtype'
                                     AND `glpi_plugin_webapplications_webapplications_items`.`plugin_webapplications_webapplications_id` = '".$webapplications."' "
                                   . getEntitiesRestrictRequest(" AND ", $table_item, '', '',
                                                                $item->maybeRecursive());

                     if ($item->maybeTemplate()) {
                        $query .= " AND ".$table_item.".is_template = '0'";
                     }
                     $query .= " ORDER BY `glpi_entities`.`completename`,
                                          `".$table_item."`.`$column` ";

                  } else {
                     $query = "SELECT `".$table_item."`.*,
                                      `glpi_plugin_webapplications_webapplications_items`.`id` AS table_items_id,
                                      `glpi_entities`.`id` AS entity
                               FROM `glpi_plugin_webapplications_webapplications_items`, `".$table_item."`
                               WHERE `".$table_item."`.`id` = `glpi_plugin_webapplications_webapplications_items`.`items_id`
                                     AND `glpi_plugin_webapplications_webapplications_items`.`itemtype` = '$itemtype'
                                     AND `glpi_plugin_webapplications_webapplications_items`.`plugin_webapplications_webapplications_id` = '".$webapplications."' "
                                   . getEntitiesRestrictRequest(" AND ", $table_item, '', '',
                                                                $item->maybeRecursive());

                     if ($item->maybeTemplate()) {
                        $query .= " AND ".$table_item.".is_template = '0'";
                     }
                     $query .= " ORDER BY `glpi_entities`.`completename`,
                                          `".$table_item."`.`$column` ";
                  }
               
                  if ($result_linked=$DB->query($query)) {
                     if ($DB->numrows($result_linked)) {
                        $item = new $itemtype();
                        while ($datal=$DB->fetch_assoc($result_linked)) {
                           if ($item->getFromDB($datal['id'])) {
                              $out .= $item->getTypeName()." - ".$item->getLink()."<br>";
                           }
                        }
                     } else {
                        $out .= ' ';
                     }
                  } else {
                     $out .= ' ';
                  }
               } else {
                  $out .= ' ';
               }
            }
         }
         return $out;
   }
   return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_webapplications_MassiveActions($type) {

   if (in_array($type,PluginWebapplicationsWebapplication::getTypes(true))) {
      return array('PluginWebapplicationsWebapplication'.MassiveAction::CLASS_ACTION_SEPARATOR.'plugin_webapplications_add_item' =>
                                                              __('Associate a web application', 'webapplications'));
   }
   return array();
}

/*
function plugin_webapplications_MassiveActionsDisplay($options=array()) {

   $web = new PluginWebapplicationsWebapplication();

   if (in_array($options['itemtype'], PluginWebapplicationsWebapplication::getTypes(true))) {
      $web->dropdownWebApplications("plugin_webapplications_webapplications_id");
      echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . _sx('button','Post') . "\" >";
   }
   return "";
}


function plugin_webapplications_MassiveActionsProcess($data) {
   
   $web_item = new PluginWebapplicationsWebapplication_Item();
   
   $res = array('ok' => 0,
               'ko' => 0,
               'noright' => 0);

   switch ($data['action']) {
      case "plugin_webapplications_add_item":     
         foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $input = array('plugin_webapplications_webapplications_id' => $data['plugin_webapplications_webapplications_id'],
                        'items_id'      => $key,
                        'itemtype'      => $data['itemtype']);
               if ($web_item->can(-1,'w',$input)) {
                  if ($web_item->add($input)){
                     $res['ok']++;
                  } else {
                     $res['ko']++;
                  }
               } else {
                  $res['noright']++;
               }
            }
         }
         break;
   }
   return $res;
}
*/
function plugin_webapplications_postinit() {
   global $CFG_GLPI, $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['webapplications'] = array();

   foreach (PluginWebapplicationsWebapplication::getTypes(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['webapplications'][$type]
         = array('PluginWebapplicationsWebapplication_Item','cleanForItem');

      CommonGLPI::registerStandardTab($type, 'PluginWebapplicationsWebapplication_Item');
   }
}

function plugin_datainjection_populate_webapplications() {
   global $INJECTABLE_TYPES;

   $INJECTABLE_TYPES['PluginWebapplicationsWebapplicationInjection'] = 'webapplications';
}

?>