<?php
/**
 * @version $Id: config.class.php 172 2014-11-15 17:41:55Z yllen $
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Behaviors plugin for GLPI.

 Behaviors is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Behaviors is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

 @package   behaviors
 @author    Remi Collet
 @copyright Copyright (c) 2010-2014 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2010

 --------------------------------------------------------------------------
*/

class PluginBehaviorsConfig extends CommonDBTM {

   static private $_instance = NULL;
   static $rightname         = 'config';

   static function canCreate() {
      return Session::haveRight('config', UPDATE);
   }


   static function canView() {
      return Session::haveRight('config', READ);
   }


   static function getTypeName($nb=0) {
      return __('Setup');
   }


   function getName($with_comment=0) {
      return __('Behaviours', 'behaviors');
   }


   /**
    * Singleton for the unique config record
    */
   static function getInstance() {

      if (!isset(self::$_instance)) {
         self::$_instance = new self();
         if (!self::$_instance->getFromDB(1)) {
            self::$_instance->getEmpty();
         }
      }
      return self::$_instance;
   }


   static function install(Migration $mig) {
      global $DB;

      $table = 'glpi_plugin_behaviors_configs';
      if (!TableExists($table)) { //not installed

         $query = "CREATE TABLE `". $table."`(
                     `id` int(11) NOT NULL,
                     `use_requester_item_group` tinyint(1) NOT NULL default '0',
                     `use_requester_user_group` tinyint(1) NOT NULL default '0',
                     `is_ticketsolutiontype_mandatory` tinyint(1) NOT NULL default '0',
                     `is_ticketsolution_mandatory` tinyint(1) NOT NULL default '0',
                     `is_ticketcategory_mandatory` tinyint(1) NOT NULL default '0',
                     `is_ticketrealtime_mandatory` tinyint(1) NOT NULL default '0',
                     `is_requester_mandatory` tinyint(1) NOT NULL default '0',
                     `is_ticketdate_locked` tinyint(1) NOT NULL default '0',
                     `use_assign_user_group` tinyint(1) NOT NULL default '0',
                     `tickets_id_format` VARCHAR(15) NULL,
                     `is_problemsolutiontype_mandatory` tinyint(1) NOT NULL default '0',
                     `remove_from_ocs` tinyint(1) NOT NULL default '0',
                     `add_notif` tinyint(1) NOT NULL default '0',
                     `use_lock` tinyint(1) NOT NULL default '0',
                     `single_tech_mode` int(11) NOT NULL default '0',
                     `date_mod` datetime default NULL,
                     `comment` text,
                     PRIMARY KEY  (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->queryOrDie($query, __('Error in creating glpi_plugin_behaviors_configs', 'behaviors').
                                 "<br>".$DB->error());

         $query = "INSERT INTO `$table`
                         (id, date_mod)
                   VALUES (1, NOW())";
         $DB->queryOrDie($query, __('Error during update glpi_plugin_behaviors_configs', 'behaviors').
                                 "<br>" . $DB->error());

      } else {
         // Upgrade

         $mig->addField($table, 'tickets_id_format',        'string');
         $mig->addField($table, 'remove_from_ocs',          'bool');
         $mig->addField($table, 'is_requester_mandatory',   'bool');

         // version 0.78.0 - feature #2801 Forbid change of ticket's creation date
         $mig->addField($table, 'is_ticketdate_locked',     'bool');

         // Version 0.80.0 - set_use_date_on_state now handle in GLPI
         $mig->dropField($table, 'set_use_date_on_state');

         // Version 0.80.4 - feature #3171 additional notifications
         $mig->addField($table, 'add_notif',                'bool');

         // Version 0.83.0 - groups now have is_requester and is_assign attribute
         $mig->dropField($table, 'sql_user_group_filter');
         $mig->dropField($table, 'sql_tech_group_filter');

         // Version 0.83.1 - prevent update on ticket updated by another user
         $mig->addField($table, 'use_lock',                 'bool');

         // Version 0.83.4 - single tech/group #3857
         $mig->addField($table, 'single_tech_mode',         'integer');

         // Version 0.84.2 - solution description mandatory #2803
         $mig->addField($table, 'is_ticketsolution_mandatory', 'bool');
         //- ticket category mandatory #3738
         $mig->addField($table, 'is_ticketcategory_mandatory', 'bool');
         //- solution type mandatory for a problem  #5048
         $mig->addField($table, 'is_problemsolutiontype_mandatory', 'bool');
      }

      return true;
   }


   static function uninstall() {
      global $DB;

      if (TableExists('glpi_plugin_behaviors_configs')) { //not installed

         $query = "DROP TABLE `glpi_plugin_behaviors_configs`";
         $DB->queryOrDie($query, $DB->error());
      }
      return true;
   }


   static function showConfigForm($item) {

      $yesnoall = array(0 => __('No'),
                        1 => __('First'),
                        2 => __('All'));

      $config = self::getInstance();

      $config->showFormHeader();

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' class='tab_bg_2 b center'>".__('New ticket')."</td>";
      echo "<td colspan='2' class='tab_bg_2 b center'>".__('Inventory', 'behaviors')."</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Ticket's number format", "behaviors")."</td><td>";
      $tab = array('NULL' => Dropdown::EMPTY_VALUE);
      foreach (array('Y000001', 'Ym0001', 'Ymd01', 'ymd0001') as $fmt) {
         $tab[$fmt] = date($fmt) . '  (' . $fmt . ')';
      }
      Dropdown::showFromArray("tickets_id_format", $tab,
                              array('value' => $config->fields['tickets_id_format']));
      echo "<td>".__('Delete computer in OCSNG when purged from GLPI', 'behaviors')."</td><td>";
      $plugin = new Plugin();
      if ($plugin->isActivated('uninstall')) {
         Dropdown::showYesNo('remove_from_ocs', $config->fields['remove_from_ocs']);
      } else {
         _e("Plugin \"Item's uninstallation\" not installed", "behaviors");
      }
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use the associated item's group", "behaviors")."</td><td>";
      Dropdown::showYesNo("use_requester_item_group", $config->fields['use_requester_item_group']);
      echo "</td><td colspan='2' class='tab_bg_2 b center'>"._n('Notification', 'Notifications', 2,
                                                                'behaviors');
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use the requester's group", "behaviors")."</td><td>";
      Dropdown::showFromArray('use_requester_user_group', $yesnoall,
                              array('value' => $config->fields['use_requester_user_group']));
      echo "<td>".__('Additional notifications', 'behaviors')."</td><td>";
      Dropdown::showYesNo('add_notif', $config->fields['add_notif']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use the technician's group", "behaviors")."</td><td>";
      Dropdown::showFromArray('use_assign_user_group', $yesnoall,
                              array('value' => $config->fields['use_assign_user_group']));
      echo "</td><td colspan='2' class='tab_bg_2 b center'>".__('Comments');
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Requester is mandatory", "behaviors")."</td><td>";
      Dropdown::showYesNo("is_requester_mandatory", $config->fields['is_requester_mandatory']);
      echo "</td><td rowspan='7' colspan='2' class='center'>";
      echo "<textarea cols='60' rows='12' name='comment' >".$config->fields['comment']."</textarea>";
      echo "<br>".sprintf(__('%1$s; %2$s'), __('Last update'),
                             Html::convDateTime($config->fields["date_mod"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>"; // Ticket - Update
      echo "<td colspan='2' class='tab_bg_2 b center'>".__('Update of a ticket')."</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Duration is mandatory before ticket is solved/closed', 'behaviors')."</td><td>";
      Dropdown::showYesNo("is_ticketrealtime_mandatory",
                          $config->fields['is_ticketrealtime_mandatory']);
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Category is mandatory before ticket is solved/closed', 'behaviors')."</td><td>";
      Dropdown::showYesNo("is_ticketcategory_mandatory",
                          $config->fields['is_ticketcategory_mandatory']);
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Type of solution is mandatory before ticket is solved/closed', 'behaviors');
      echo "</td><td>";
      Dropdown::showYesNo("is_ticketsolutiontype_mandatory",
                          $config->fields['is_ticketsolutiontype_mandatory']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Description of solution is mandatory before ticket is solved/closed', 'behaviors');
      echo "</td><td>";
      Dropdown::showYesNo("is_ticketsolution_mandatory",
                          $config->fields['is_ticketsolution_mandatory']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Deny change of ticket's creation date", "behaviors")."</td><td>";
      Dropdown::showYesNo("is_ticketdate_locked", $config->fields['is_ticketdate_locked']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Protect from simultaneous update', 'behaviors')."</td><td>";
      Dropdown::showYesNo("use_lock", $config->fields['use_lock']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Single technician and group', 'behaviors')."</td><td>";
      $tab = array(0 => __('No'),
                   1 => __('Single user and single group', 'behaviors'),
                   2 => __('Single user or group', 'behaviors'));
      Dropdown::showFromArray('single_tech_mode', $tab,
                              array('value' => $config->fields['single_tech_mode']));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>"; // Problem - Update
      echo "<td colspan='2' class='tab_bg_2 b center'>".__('Update of a problem')."</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Type of solution is mandatory before problem is solved/closed', 'behaviors');
      echo "</td><td>";
      Dropdown::showYesNo("is_problemsolutiontype_mandatory",
                          $config->fields['is_problemsolutiontype_mandatory']);
      echo "</td></tr>";

      $config->showFormButtons(array('candel'=>false));

      return false;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Config') {
            return self::getName();
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Config') {
         self::showConfigForm($item);
      }
      return true;
   }
}
