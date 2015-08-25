<?php
/*
 * @version $Id$
 LICENSE

 This file is part of the purgelogs plugin.

 purgelogs plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 purgelogs plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Behaviors. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   purgelogs
 @author    the purgelogs plugin team
 @copyright Copyright (c) 2010-2011 purgelogs plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/purgelogs
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginPurgelogsConfig extends CommonDBTM {

   static $rightname = "config";
   
   static function getConfig($update = false) {
      static $config = null;

      if (is_null($config)) {
         $config = new self();
      }
      if ($update) {
         $config->getFromDB(1);
      }
      return $config;
   }

   function __construct() {
      if (TableExists($this->getTable())) {
         $this->getFromDB(1);
      }
   }
   
   static function getTypeName($nb = 0) {
      return __("Purge history", "purgelogs");
   }
   
   function showForm() {
      $this->getFromDB(1);
      echo "<form name='form' id='purgelogs_form' method='post' action='".$this->getFormURL()."'>";
      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'><th colspan='4'>".__("Logs purge configuration", "purgelogs").
           "</th></tr>";
      echo "<tr class='tab_bg_1'><th colspan='4'><i>".__("Change all", "purgelogs")."</i>";
      $js = "function form_init_all(form, index) {
               var elem = document.getElementById('purgelogs_form').elements;
               for(var i = 0; i < elem.length; i++) {
                  if (elem[i].type == \"select-one\") {
                     elem[i].selectedIndex = index;
                  }
               }
            }";
      echo Html::scriptBlock($js);
      self::showInterval('init_all', 0, array(
         'on_change' => "form_init_all(this.form, this.selectedIndex);"
      ));
      echo "</th></tr>";
      echo "<input type='hidden' name='id' value='1'>";
      
      echo "<tr class='tab_bg_1'><th colspan='4'>".__("General")."</th></tr>";
      echo "<tr class='tab_bg_1'><td class='center'>".__("Add relation between items", "purgelogs").
           "</td><td>";
      self::showInterval('purge_addrelation', $this->fields["purge_addrelation"]);
      echo "</td>";
      echo "<td>".__("Logs purge configuration", "purgelogs")."</td><td>";
      self::showInterval('purge_deleterelation', $this->fields["purge_deleterelation"]);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'><td class='center'>".__("Add the item")."</td><td>";
      self::showInterval('purge_createitem', $this->fields["purge_createitem"]);
      echo "</td>";
      echo "<td>".__("Delete the item")."</td><td>";
      self::showInterval('purge_deleteitem', $this->fields["purge_deleteitem"]);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'><td class='center'>".__("Restore the item")."</td><td>";
      self::showInterval('purge_restoreitem', $this->fields["purge_restoreitem"]);
      echo "</td>";

      echo "<td>".__('Update the item')."</td><td>";
      self::showInterval('purge_updateitem', $this->fields["purge_updateitem"]);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'><td class='center'>".__("Comments")."</td><td>";
      self::showInterval('purge_comments', $this->fields["purge_comments"]);
      echo "</td>";
      echo "<td>".__("Last update")."</td><td>";
      self::showInterval('purge_datemod', $this->fields["purge_datemod"]);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'><th colspan='4'>"._n('Software', 'Software', 2)."</th></tr>";
      echo "<tr class='tab_bg_1'><td class='center'>".
           __("Installation/uninstallation of software on computers", "purgelogs")."</td><td>";
      self::showInterval('purge_computer_software_install',
                          $this->fields["purge_computer_software_install"]);
      echo "</td>";
      echo "<td>".__("Installation/uninstallation versions on softwares", "purgelogs")."</td><td>";
      self::showInterval('purge_software_version_install',
                         $this->fields["purge_software_version_install"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><th colspan='4'>".__('Financial and administrative information').
           "</th></tr>";
      echo "<tr class='tab_bg_1'><td class='center'>".
           __("Add financial information to an item", "purgelogs")."</td><td>";
      self::showInterval('purge_infocom_creation', $this->fields["purge_infocom_creation"]);
      echo "</td>";
      echo "<td colspan='2'></td></tr>";
      
      echo "<tr class='tab_bg_1'><th colspan='4'>"._n('User','Users',2)."</th></tr>";
      
      echo "<tr class='tab_bg_1'><td class='center'>".
           __("Add/remove profiles to users", "purgelogs")."</td><td>";
      self::showInterval('purge_profile_user', $this->fields["purge_profile_user"]);
      echo "</td>";
      echo "<td>".__("Add/remove groups to users", "purgelogs")."</td><td>";
      self::showInterval('purge_group_user', $this->fields["purge_group_user"]);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'><td class='center'>".
           __("User authentication method changes", "purgelogs")."</td><td>";
      self::showInterval('purge_user_auth_changes', $this->fields["purge_user_auth_changes"]);
      echo "</td>";
      echo "<td class='center'>".__("Deleted user in LDAP directory").
           "</td><td>";
      self::showInterval('purge_userdeletedfromldap', $this->fields["purge_userdeletedfromldap"]);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'><th colspan='4'>".__("OCSNG", "purgelogs")."</th></tr>";
      
      echo "<tr class='tab_bg_1'><td class='center'>".__("OCS ID Change", "purgelogs")."</td><td>";
      self::showInterval('purge_ocsid_changes', $this->fields["purge_ocsid_changes"]);
      echo "</td>";
      echo "<td>".__("Add from OCS", "purgelogs")."</td><td>";
      self::showInterval('purge_ocsimport', $this->fields["purge_ocsimport"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td class='center'>".__("Link with OCS", "purgelogs")."</td><td>";
      self::showInterval('purge_ocslink', $this->fields["purge_ocslink"]);
      echo "</td>";
      echo "<td>".__("Delete from OCS", "purgelogs")."</td><td>";
      self::showInterval('purge_ocsdelete', $this->fields["purge_ocsdelete"]);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'><th colspan='4'>"._n('Component', 'Components', 2)."</th></tr>";

      echo "<tr class='tab_bg_1'><td class='center'>".__("Add component", "purgelogs")."</td><td>";
      self::showInterval('purge_adddevice', $this->fields["purge_adddevice"]);
      echo "</td>";
      echo "<td>".__("Update component", "purgelogs")."</td><td>";
      self::showInterval('purge_updatedevice', $this->fields["purge_updatedevice"]);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'><td class='center'>".__("Disconnect a component", "purgelogs").
           "</td><td>";
      self::showInterval('purge_disconnectdevice', $this->fields["purge_disconnectdevice"]);
      echo "</td>";
      echo "<td>".__("Connect a component", "purgelogs")."</td><td>";
      self::showInterval('purge_connectdevice', $this->fields["purge_connectdevice"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td class='center'>".__("Delete component", "purgelogs").
           "</td><td>";
      self::showInterval('purge_deletedevice', $this->fields["purge_deletedevice"]);
      echo "</td>";
      echo "<td colspan='2'></td></tr>";
      
      echo "<tr class='tab_bg_1'><th colspan='4'>".__("Plugins")."</th></tr>";
      
      echo "<tr class='tab_bg_1'><td class='center'>".
           __("Logs Webservices connections", "purgelogs")."</td><td>";
      self::showInterval('purge_webservices_logs', $this->fields["purge_webservices_logs"]);
      echo "</td>";
      echo "<td class='center'>".__("Old Genericobject item types", "purgelogs")."</td><td>";
      self::showInterval('purge_genericobject_unusedtypes', $this->fields["purge_genericobject_unusedtypes"]);
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_1'><th colspan='4'>".__("All sections","purgelogs")."</th></tr>";

      echo "<tr class='tab_bg_1'><td class='center'>".__("Purge all log entries","purgelogs")."</td><td>";
      self::showInterval('purge_all', $this->fields["purge_all"]);
      echo "</td>";
      echo "<td colspan='2'></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' name='update' value=\""._sx('button','Save')."\" class='submit' >";
      echo"</td>";
      echo "</tr>";
      
      echo "</table></div>";
      Html::closeForm();
   }
   
   static function showInterval($name, $value, $options=array()) {
      $values[-1] = __("All");
      $values[0]  = __("Never");
      for ($i = 1; $i < 121; $i++) {
         $values[$i] = $i. " "._n('month', 'months', 1);
      }
      $options['value'] = $value;
      return Dropdown::showFromArray($name, $values, $options);
   }
   
   //----------------- Install & uninstall -------------------//

   static function install(Migration $migration) {
      global $DB;


      $table = getTableForItemType(__CLASS__);
      $config = new self();

      // Install
      if (!TableExists($table)) {
            $migration->displayMessage("Installing $table");

            //Install
            $query = "CREATE TABLE `$table` (
                     `id` int(11) NOT NULL auto_increment,
                     `purge_computer_software_install` int(11) NOT NULL default '0',
                     `purge_software_version_install` int(11) NOT NULL default '0',
                     `purge_infocom_creation` int(11) NOT NULL default '0',
                     `purge_profile_user` int(11) NOT NULL default '0',
                     `purge_group_user` int(11) NOT NULL default '0',
                     `purge_webservices_logs` int(11) NOT NULL default '0',
                     `purge_ocsid_changes` int(11) NOT NULL default '0',
                     `purge_ocsimport` int(11) NOT NULL default '0',
                     `purge_ocslink` int(11) NOT NULL default '0',
                     `purge_ocsdelete` int(11) NOT NULL default '0',
                     `purge_adddevice` tinyint(1) NOT NULL default '0',
                     `purge_updatedevice` tinyint(1) NOT NULL default '0',
                     `purge_deletedevice` tinyint(1) NOT NULL default '0',
                     `purge_connectdevice` tinyint(1) NOT NULL default '0',
                     `purge_disconnectdevice` tinyint(1) NOT NULL default '0',
                     `purge_userdeletedfromldap` tinyint(1) NOT NULL default '0',
                     `purge_addrelation` tinyint(1) NOT NULL default '0',
                     `purge_deleterelation` tinyint(1) NOT NULL default '0',
                     `purge_createitem` tinyint(1) NOT NULL default '0',
                     `purge_deleteitem` tinyint(1) NOT NULL default '0',
                     `purge_restoreitem` tinyint(1) NOT NULL default '0',
                     `purge_updateitem` tinyint(1) NOT NULL default '0',
                     `purge_comments` tinyint(1) NOT NULL default '0',
                     `purge_datemod` tinyint(1) NOT NULL default '0',
                     `purge_genericobject_unusedtypes` tinyint(1) NOT NULL default '0',
                     `purge_all` tinyint(1) NOT NULL default '0',
                     `purge_user_auth_changes` tinyint(1) NOT NULL default '0',
                     PRIMARY KEY  (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
               $DB->query($query) or die ($DB->error());
               //Add config
               $config->add(array('id' => 1));
      }

      // Update
      if(TableExists($table) ) {

         // for 0.84
         if(!FieldExists($table, "purge_genericobject_unusedtypes")) {

            $migration->displayMessage("Updating $table adding field purge_genericobject_unusedtypes");

            $migration->addField($table, "purge_genericobject_unusedtypes", 
                                 "tinyint(1) NOT NULL default '0'",
                                 array('after'     => "purge_datemod",
                                       'update'    => "0"));
         }

         // for 0.84.1
         if(!FieldExists($table, "purge_all")) {

            $migration->displayMessage("Updating $table adding fiel purge_all");

            $migration->addField($table, "purge_all", "tinyint(1) NOT NULL default '0'",
                                 array('after'     => "purge_genericobject_unusedtypes",
                                       'update'    => "0"));
         }
         $migration->addfield($table, 'purge_user_auth_changes', 'bool');
      }

      $migration->executeMigration();

      return true;
   }
   
   static function uninstall() {
      global $DB;
      //New table
      $DB->query("DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`");
   }
}

?>
