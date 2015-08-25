<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2014 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author
   @comment
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringShinken extends CommonDBTM {


   // Comment to remove custom variable from host/service configuration
   public static $shinkenParameters = array(
      // GLPI root entity name
      'glpi' => array(
         'rootEntity'   => 'Entité racine',
         // Entity id
         'entityId' => '_ENTITIESID',
         // Entity name
         'entityName' => '_ENTITY',
         // Entity complete
         'entityComplete' => '_ENTITY_COMPLETE',
         // Item type
         'itemType' => '_ITEMTYPE',
         // Item id
         'itemId' => '_ITEMSID',
         // Location
         'location' => '_LOC_NAME',
         // Latitude
         'lat' => '_LOC_LAT',
         // Longitude
         'lng' => '_LOC_LNG',
         // Altitude
         'alt' => '_LOC_ALT',
      ),
      // Shinken configuration
      'shinken' => array(
         // Build fake hosts for parents relationship
         'fake_hosts' => array(
            // Default values
            'build' => true,
            // Fake hosts tag
            //'use' => 'fake',
            // Fake hosts name prefix
            'name_prefix' => '_fake_',
            // Hostgroup name
            'hostgroup_name' => 'fake_hosts',
            // Hostgroup alias
            'hostgroup_alias' => 'Fake hosts',
            // Main root parent
            'root_parent' => 'Root',
            // Main root parent
            'bp_host' => 'BP_host'
         ),
         // Build fake contacts for fake hosts
         'fake_contacts' => array(
            // Default values
            'build' => true,
            // Contact name
            'contact_name' => 'monitoring',
         ),
         'hosts' => array(
            // Default values
            //'use' => 'pm-important',
            'check_period' => '24x7',
            'process_perf_data' => '1',
            'notification_period' => '24x7',
            'notification_options' => 'd,u,r,f,s',
            'notification_interval' => '86400',
            // Set as 'entity' to use hostgroupname else use the defined value ...
            // When fake_hosts are built (see upper), use 'entity' !
            'parents' => 'entity',
            // Shinken host parameters
            'notes' => '',
            'notes_url' => '',
            'action_url' => '',
            'icon_image' => '',
            'icon_image_alt' => '',
            'vrml_image' => '',
            'statusmap_image' => '',
         ),
         'services' => array(
            // Default check_period
            'check_period' => '24x7',
            'process_perf_data' => '1',
            // Shinken service parameters
            'notes' => '',
            'notes_url' => '',
            'action_url' => '',
            'icon_image' => '',
            'icon_image_alt' => '',
         ),
         'contacts' => array(
            // Default check_period
            'user_category' => 'glpi',
         )
      ),
      // Graphite configuration
      'graphite' => array(
         // Prefix
         'prefix' => array(
            'name'   => '_GRAPHITE_PRE',
            'value'  => 'knm.kiosks.'
         )
      ),
      // WebUI configuration
      'webui' => array(
         // Hosts custom view
         'hostView' => array(
            'name'      => 'custom_views',
            'value'     => 'kiosk'
         ),
         // Hosts icon set
         'hostIcons' => array(
            'name'      => 'icon_set',
            'value'     => 'host'
         ),
         // Services icon set
         'serviceIcons' => array(
            'name'      => 'icon_set',
            'value'     => 'service'
         ),
         // Contacts role
         'contacts' => array(
            // Used if not defined in contact template
            'is_admin'              => '1',
            'can_submit_commands'   => '0',
            // Use this password if user has an empty password
            'password'              => 'ipmfrance'
         ),
      ),
   );

   function generateConfig() {

      return true;
   }



   function writeFile($name, $array) {
      $config = '';
      $config .= "define ".$name."{\n";
      foreach ($array as $key => $value) {
         $c = 35;
         $c = $c - strlen($key);
         $config .= "       ".$key;
         for ($t=0; $t < $c; $t++) {
            $config .= " ";
         }
         $config .= $value."\n";
      }
      $config .= "}\n";
      $config .= "\n\n";
      return $config;
   }

   function removeAccents($str) {
      $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
      $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');

      return str_replace($a, $b, $str);
   }

   function shinkenFilter($str) {

      return preg_replace("/[^A-Za-z0-9\-_]/","", strtolower(self::removeAccents($str)));
   }



   function generateCommandsCfg($file=0) {

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "Starting generateCommandsCfg ...\n"
      );
      $pmCommand = new PluginMonitoringCommand();
      $pmNotificationcommand = new PluginMonitoringNotificationcommand();
      $pmEventhandler = new PluginMonitoringEventhandler();

      $a_commands = array();
      $i=0;

      // Only active commands and notification commands ...
      $a_list = $pmCommand->find("`is_active`='1'");
      $a_listnotif = $pmNotificationcommand->find("`is_active`='1'");
      $a_list = array_merge($a_list, $a_listnotif);

      $reload_shinken_found = false;
      $restart_shinken_found = false;
      $restart_shinken_1_4_found = false;
      foreach ($a_list as $data) {
         if ($data['command_name'] == "bp_rule") { continue; }

         // For comments ...
         $a_commands[$i]['name'] = $data['name'];

         // For Shinken ...
         $a_commands[$i]['command_name'] = PluginMonitoringCommand::$command_prefix . $data['command_name'];
         $a_commands[$i]['command_line'] = $data['command_line'];
         if (! empty($data['module_type'])) {
            $a_commands[$i]['module_type'] = $data['module_type'];
         }
         if (! empty($data['poller_tag'])) {
            $a_commands[$i]['poller_tag'] = $data['poller_tag'];
         }
         if (! empty($data['reactionner_tag'])) {
            $a_commands[$i]['reactionner_tag'] = $data['reactionner_tag'];
         }

         if ($data['command_name'] == "reload-shinken") {
            $reload_shinken_found = true;
            // No prefix for this command (WS arbiter)
            $a_commands[$i]['command_name'] = $data['command_name'];
         }
         if ($data['command_name'] == "restart-shinken") {
            $restart_shinken_found = true;
            // No prefix for this command (WS arbiter)
            $a_commands[$i]['command_name'] = $data['command_name'];
         }
         if ($data['command_name'] == "restart_shinken") {
            $restart_shinken_1_4_found = true;
         }
         PluginMonitoringToolbox::logIfExtradebug(
            'pm-shinken',
            "- command: ".$a_commands[$i]['command_name']." -> ".$a_commands[$i]['name']."\n"
         );
         $i++;
      }
      if (! $restart_shinken_1_4_found) {
         // * Restart shinken command
         $a_commands[$i]['name'] = 'Restart Shinken (1.4)';
         $a_commands[$i]['command_name'] = 'restart_shinken';
         $a_commands[$i]['command_line'] = "nohup sh -c '/usr/local/shinken/bin/stop_arbiter.sh && sleep 3 && /usr/local/shinken/bin/launch_arbiter.sh'  > /dev/null 2>&1 &";
      }
      if (! $reload_shinken_found) {
         // * Reload shinken command (2.0)
         $a_commands[$i]['name'] = 'Reload Shinken configuration';
         $a_commands[$i]['command_name'] = 'reload-shinken';
         $a_commands[$i]['command_line'] = "nohup sh -c '/etc/init.d/shinken reload'    > /dev/null 2>&1 &";
      }
      if (! $restart_shinken_found) {
         // * Restart shinken command (2.0)
         $a_commands[$i]['name'] = 'Restart Shinken';
         $a_commands[$i]['command_name'] = 'restart-shinken';
         $a_commands[$i]['command_line'] = "nohup sh -c '/etc/init.d/shinken restart'    > /dev/null 2>&1 &";
      }

      // Event handlers
      $a_list = $pmEventhandler->find("`is_active`='1'");
      foreach ($a_list as $data) {
         if ($data['command_name'] != "bp_rule") {
            $a_commands[$i]['name'] = $data['name'];

            $a_commands[$i]['command_name'] = PluginMonitoringCommand::$command_prefix . $data['command_name'];
            $a_commands[$i]['command_line'] = $data['command_line'];
            PluginMonitoringToolbox::logIfExtradebug(
               'pm-shinken',
               "- command: ".$a_commands[$i]['command_name']." -> ".$a_commands[$i]['name']."\n"
            );
            $i++;
         }
      }
      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "End generateCommandsCfg\n"
      );

      if ($file == "1") {
         $config = "# Generated by plugin monitoring for GLPI\n# on ".date("Y-m-d H:i:s")."\n\n";
         foreach ($a_commands as $data) {
            $config .= "# ".$data['name']."\n";
            unset($data['name']);
            $config .= $this->writeFile("command", $data);
         }
         return array('commands.cfg', $config);
      } else {
         return $a_commands;
      }
   }



   function generateHostsCfg($file=0, $tag='') {
      global $DB;

      // Log Shinken restart event with Tag information ...
      $pmLog = new PluginMonitoringLog();
      if (isset($_SERVER['HTTP_USER_AGENT'])
              AND strstr($_SERVER['HTTP_USER_AGENT'], 'xmlrpclib.py')) {
         if (!isset($_SESSION['glpi_currenttime'])) {
            $_SESSION['glpi_currenttime'] = date("Y-m-d H:i:s");
         }
         $input = array();
         $input['user_name'] = "Shinken";
         $input['action'] = "restart";
         $input['date_mod'] = date("Y-m-d H:i:s");
         $input['value'] = $tag;
         $pmLog->add($input);
      }

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "Starting generateHostsCfg ($tag) ...\n"
      );
      $pmCommand     = new PluginMonitoringCommand();
      $pmCheck       = new PluginMonitoringCheck();
      $pmComponent   = new PluginMonitoringComponent();
      $pmEntity      = new PluginMonitoringEntity();
      $pmHostconfig  = new PluginMonitoringHostconfig();
      $pmHost        = new PluginMonitoringHost();
      $calendar      = new Calendar();
      $pmRealm       = new PluginMonitoringRealm();
      $networkEquipment = new NetworkEquipment();
      $pmContact_Item = new PluginMonitoringContact_Item();
      $profile_User   = new Profile_User();
      $pmEventhandler = new PluginMonitoringEventhandler();
      $user           = new User();
      $pmConfig       = new PluginMonitoringConfig();
      $computerType   = new ComputerType();

      // Get computer type contener / VM
      $conteners = $computerType->find("`name`='BSDJail'");

      $pmConfig->getFromDB(1);

      $a_hosts = array();
      $i=0;
      $a_parents_found = array();
      $a_hosts_found = array();

      $a_entities_allowed = $pmEntity->getEntitiesByTag($tag);
      $a_entities_list = array();
      foreach ($a_entities_allowed as $entity) {
         $a_entities_list = getSonsOf("glpi_entities", $entity);
      }
      $where = '';
      if (! isset($a_entities_allowed['-1'])) {
         $where = getEntitiesRestrictRequest("WHERE", "glpi_entities", '', $a_entities_list);
      }


      // * Prepare contacts
      $a_contacts_entities = array();
      $a_list_contact = $pmContact_Item->find("`itemtype`='PluginMonitoringComponentscatalog'
         AND `users_id`>0");
      foreach ($a_list_contact as $data) {
         $contactentities = getSonsOf('glpi_entities', $data['entities_id']);
         if (isset($a_contacts_entities[$data['items_id']][$data['users_id']])) {
            $contactentities = array_merge($contactentities, $a_contacts_entities[$data['items_id']][$data['users_id']]);
         }
         $a_contacts_entities[$data['items_id']][$data['users_id']] = $contactentities;
      }

      // $query = "SELECT *
         // FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         // GROUP BY `itemtype`, `items_id`";
      $query = "SELECT
         `glpi_plugin_monitoring_componentscatalogs_hosts`.*,
         `glpi_computers`.`id`,
         `glpi_entities`.`id` AS entityId, `glpi_entities`.`name` AS entityName, `glpi_entities`.`completename` AS entityFullName,
         `glpi_locations`.`id`, `glpi_locations`.`completename` AS locationName,
         `glpi_locations`.`comment` AS locationComment, `glpi_locations`.`building` AS locationGPS,
         `glpi_plugin_monitoring_services`.`networkports_id`
         FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         LEFT JOIN `glpi_computers` ON `glpi_computers`.`id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id`
         LEFT JOIN `glpi_entities` ON `glpi_computers`.`entities_id` = `glpi_entities`.`id`
         LEFT JOIN `glpi_locations` ON `glpi_locations`.`id` = `glpi_computers`.`locations_id`
         LEFT JOIN `glpi_plugin_monitoring_services`
            ON `glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id`
               = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
         $where
         GROUP BY `itemtype`, `items_id`";
      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "Hosts: $query\n"
      );
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         // Toolbox::logInFile("pm-shinken", " - fetch host ".$data['itemtype']." / ".$data['items_id']."\n");

         $classname = $data['itemtype'];
         $class = new $classname;
         if ($class->getFromDB($data['items_id'])) {

            // if (isset($a_entities_allowed['-1'])
                    // OR isset($a_entities_allowed[$class->fields['entities_id']])) {

               $pmHost->getFromDBByQuery("WHERE `glpi_plugin_monitoring_hosts`.`itemtype` = '" . $data['itemtype'] . "' AND `glpi_plugin_monitoring_hosts`.`items_id` = '" . $data['items_id'] . "' LIMIT 1");

               $a_hosts[$i]['host_name'] = self::shinkenFilter($class->fields['name']);
               if ($pmConfig->fields['append_id_hostname'] == 1) {
                  $a_hosts[$i]['host_name'] .= "-".$class->fields['id'];
               }
               // Fix if hostname is not defined ...
               if (empty($a_hosts[$i]['host_name'])) {
                  continue;
               }
               $a_hosts_found[$a_hosts[$i]['host_name']] = 1;
               PluginMonitoringToolbox::logIfExtradebug(
                  'pm-shinken',
                  " - add host ".$a_hosts[$i]['host_name']."\n"
               );

               $a_hosts[$i]['_HOSTID'] =
                  $pmHost->getField('id');
               if (isset(self::$shinkenParameters['glpi']['entityId'])) {
                  $a_hosts[$i][self::$shinkenParameters['glpi']['entityId']] =
                     $data['entityId'];
               }
               if (isset(self::$shinkenParameters['glpi']['itemType'])) {
                  $a_hosts[$i][self::$shinkenParameters['glpi']['itemType']] =
                     $classname;
               }
               if (isset(self::$shinkenParameters['glpi']['itemId'])) {
                  $a_hosts[$i][self::$shinkenParameters['glpi']['itemId']] =
                     $data['items_id'];
               }

               if (isset(self::$shinkenParameters['glpi']['entityName'])) {
                  $a_hosts[$i][self::$shinkenParameters['glpi']['entityName']] =
                     strtolower(self::shinkenFilter($data['entityName']));
               }

               $data['entityFullName'] = preg_replace("/ > /","#",$data['entityFullName']);
               $data['entityFullName'] = preg_replace("/". self::$shinkenParameters['glpi']['rootEntity'] ."#/","",$data['entityFullName']);
               $data['entityFullName'] = preg_replace("/#/","_",$data['entityFullName']);
               if (isset(self::$shinkenParameters['glpi']['entityComplete'])) {
                  $a_hosts[$i][self::$shinkenParameters['glpi']['entityComplete']] =
                     self::shinkenFilter ($data['entityFullName']);
               }
               $data['entityFullName'] = preg_replace("/_/",".",$data['entityFullName']);

               if (isset(self::$shinkenParameters['glpi']['location'])) {
                  if (! empty($data['locationName'])) {
                     $string = utf8_decode(strip_tags(trim($data['locationName'])));
                     $string = preg_replace("/[\r\n]/",".",$data['locationName']);
                     $string = preg_replace("/[^A-Za-z0-9\-_ <>\/',;.:!?%*()éèàù]/",'',$string);
                     $a_hosts[$i][self::$shinkenParameters['glpi']['location']] =
                        $string;
                     $data['hostLocation'] = $string;
                  }
               }

               if (isset(self::$shinkenParameters['graphite']['prefix']['name'])) {
                  $a_hosts[$i][self::$shinkenParameters['graphite']['prefix']['name']] =
                     strtolower(self::$shinkenParameters['graphite']['prefix']['value'] . self::removeAccents($data['entityFullName']));
               }

               if (isset(self::$shinkenParameters['glpi']['lat'])) {
                  if (! empty($data['locationGPS'])) {
                     $split = explode(',', $data['locationGPS']);
                     if (count($split) > 2) {
                        // At least 3 elements, let us consider as GPS coordinates with altitude ...
                        $a_hosts[$i][self::$shinkenParameters['glpi']['lat']] = $split[0];
                        $a_hosts[$i][self::$shinkenParameters['glpi']['lng']] = $split[1];
                        $a_hosts[$i][self::$shinkenParameters['glpi']['alt']] = $split[2];
                     } else if (count($split) > 1) {
                        // At least 2 elements, let us consider as GPS coordinates ...
                        $a_hosts[$i][self::$shinkenParameters['glpi']['lat']] = $split[0];
                        $a_hosts[$i][self::$shinkenParameters['glpi']['lng']] = $split[1];
                     // } else {
                        // $a_hosts[$i]['_LOC_BUILDING'] = preg_replace("/[\r\n]/",".",$data['locationGPS']);
                        // $a_hosts[$i]['_LOC_BUILDING'] = preg_replace("/[^A-Za-z0-9\-_]/"," / ",$a_hosts[$i]['_LOC_BUILDING']);
                     }
                  }
               }

               // Hostgroup name
               $a_hosts[$i]['hostgroups'] = self::shinkenFilter($data['entityName']);
               $a_hosts[$i]['hostgroups'] = preg_replace("/[ ]/","_",$a_hosts[$i]['hostgroups']);

               // Alias
               $a_hosts[$i]['alias'] = $data['entityName']." / ". $a_hosts[$i]['host_name'];
/*
               if (isset($class->fields['networkequipmenttypes_id'])) {
                  if ($class->fields['networkequipmenttypes_id'] > 0) {
                     $a_hosts[$i]['alias'] .= " (".Dropdown::getDropdownName("glpi_networkequipmenttypes", $class->fields['networkequipmenttypes_id']).")";
                  }
               } else if (isset($class->fields['computertypes_id'])) {
                  if ($class->fields['computertypes_id'] > 0) {
                     $a_hosts[$i]['alias'] .= " (".Dropdown::getDropdownName("glpi_computertypes", $class->fields['computertypes_id']).")";
                  }
               } else if (isset($class->fields['printertypes_id'])) {
                  if ($class->fields['printertypes_id'] > 0) {
                     $a_hosts[$i]['alias'] .= " (".Dropdown::getDropdownName("glpi_printertypes", $class->fields['printertypes_id']).")";
                  }
               }
*/
               if (isset($data['hostLocation'])) {
                  $a_hosts[$i]['alias'] .= " (".$data['hostLocation'].")";
               }

               // WebUI user interface ...
               if (isset(self::$shinkenParameters['webui']['hostIcons']['name'])) {
                  $a_hosts[$i][self::$shinkenParameters['webui']['hostIcons']['name']] =
                     self::$shinkenParameters['webui']['hostIcons']['value'];
               }
               if (isset(self::$shinkenParameters['webui']['hostView']['name'])) {
                  $a_hosts[$i][self::$shinkenParameters['webui']['hostView']['name']] =
                     self::$shinkenParameters['webui']['hostView']['value'];
               }

               // IP address
               $ip = PluginMonitoringHostaddress::getIp($data['items_id'], $data['itemtype'], $class->fields['name']);
               $a_hosts[$i]['address'] = $ip;

               // use host IP of contener if activated
                  if ($data['itemtype'] == 'Computer') {
                     if ($pmConfig->fields['nrpe_prefix_contener'] == 1) {
                        if (isset($conteners[$class->fields['computertypes_id']])) {
                           // get Host of contener/VM
                           $where = "LOWER(`uuid`)".  ComputerVirtualMachine::getUUIDRestrictRequest($class->fields['uuid']);
                           $hosts = getAllDatasFromTable('glpi_computervirtualmachines', $where);
                           if (!empty($hosts)) {
                              $host = current($hosts);
                              $ip = PluginMonitoringHostaddress::getIp($host['computers_id'], 'Computer', '');
                              $a_hosts[$i]['address'] = $ip;
                           }
                        }
                     }
                  }


               // Manage dependencies
               $parent = '';
               if ($data['itemtype'] != 'NetworkEquipment') {
                  $networkPort = new NetworkPort();
                  $a_networkports = $networkPort->find("`itemtype`='".$data['itemtype']."'
                     AND `items_id`='".$data['items_id']."'");
                  foreach ($a_networkports as $data_n) {
                     $networkports_id = $networkPort->getContact($data_n['id']);
                     if ($networkports_id) {
                        $networkPort->getFromDB($networkports_id);
                        if ($networkPort->fields['itemtype'] == 'NetworkEquipment') {
                           $networkEquipment->getFromDB($networkPort->fields['items_id']);
                           $parent = self::shinkenFilter($networkEquipment->fields['name']);
                           $a_parents_found[$parent] = 1;
                           $pmHost->updateDependencies($classname, $data['items_id'], 'NetworkEquipment-'.$networkPort->fields['items_id']);
                        }
                     }
                  }

                  if (empty($parent)) {
                     if (self::$shinkenParameters['shinken']['hosts']['parents'] == 'entity') {
                        $parent = self::$shinkenParameters['shinken']['fake_hosts']['name_prefix'] . $a_hosts[$i]['hostgroups'];
                     } else {
                        $parent = self::$shinkenParameters['shinken']['hosts']['parents'];
                     }
                  }
               }
               $a_hosts[$i]['parents'] = $parent;

               $a_fields = array();

               $pmComponent->getFromDB($pmHostconfig->getValueAncestor('plugin_monitoring_components_id',
                                                                        $class->fields['entities_id'],
                                                                        $classname,
                                                                        $class->getID()));

               $pmCommand->getFromDB($pmComponent->fields['plugin_monitoring_commands_id']);

               $a_fields = $pmComponent->fields;

               // Manage host check_command arguments
               // Toolbox::logInFile("pm-shinken", "Command line : ".$pmCommand->fields['command_line']."\n");
               // Toolbox::logInFile("pm-shinken", "Arguments : ".$a_fields['arguments']."\n");
               // Toolbox::logInFile("pm-shinken", "Arguments : ".$pmCommand->fields['arguments']."\n");

               $array = array();
               preg_match_all("/\\$(ARG\d+)\\$/", $pmCommand->fields['command_line'], $array);
               sort($array[0]);
               $a_arguments = importArrayFromDB($pmCommand->fields['arguments']);
               $a_argumentscustom = importArrayFromDB($pmComponent->fields['arguments']);
               foreach ($a_argumentscustom as $key=>$value) {
                  $a_arguments[$key] = $value;
               }
               foreach ($a_arguments as $key=>$value) {
                  $a_arguments[$key] = str_replace('!', '\!', html_entity_decode($value));
               }
               $args = '';
               foreach ($array[0] as $arg) {
                  if ($arg != '$PLUGINSDIR$'
                          AND $arg != '$NAGIOSPLUGINSDIR$'
                          AND $arg != '$HOSTADDRESS$'
                          AND $arg != '$MYSQLUSER$'
                          AND $arg != '$MYSQLPASSWORD$') {
                     $arg = str_replace('$', '', $arg);
                     if (!isset($a_arguments[$arg])) {
                        $args .= '!';
                     } else {
                        if (strstr($a_arguments[$arg], "[[HOSTNAME]]")) {
                           $a_arguments[$arg] = str_replace("[[HOSTNAME]]", $hostname, $a_arguments[$arg]);
                        } elseif (strstr($a_arguments[$arg], "[[NETWORKPORTDESCR]]")){
                           if (class_exists("PluginFusioninventoryNetworkPort")) {
                              $pfNetworkPort = new PluginFusioninventoryNetworkPort();
                              $pfNetworkPort->loadNetworkport($data['networkports_id']);
                              $descr = $pfNetworkPort->getValue("ifdescr");
                              $a_arguments[$arg] = str_replace("[[NETWORKPORTDESCR]]", $descr, $a_arguments[$arg]);
                           }
                        } elseif (strstr($a_arguments[$arg], "[[NETWORKPORTNUM]]")){
                           $networkPort = new NetworkPort();
                           $networkPort->getFromDB($data['networkports_id']);
                           $logicalnum = $pfNetworkPort->fields['logical_number'];
                           $a_arguments[$arg] = str_replace("[[NETWORKPORTNUM]]", $logicalnum, $a_arguments[$arg]);
                        } elseif (strstr($a_arguments[$arg], "[[NETWORKPORTNAME]]")) {
                           if (isset($data['networkports_id'])
                                   && $data['networkports_id'] > 0) {
                              $networkPort = new NetworkPort();
                              $networkPort->getFromDB($data['networkports_id']);
                              $portname = $pfNetworkPort->fields['name'];
                              $a_arguments[$arg] = str_replace("[[NETWORKPORTNAME]]", $portname, $a_arguments[$arg]);
                           } else if ($classname == 'Computer') {
                              // Get networkportname of networkcard defined
                              $pmHostaddress = new PluginMonitoringHostaddress();
                              $a_hostaddresses = $pmHostaddress->find("`itemtype`='Computer'"
                                      . " AND  `items_id`='".$class->fields['id']."'", '', 1);
                              if (count($a_hostaddresses) == 1) {
                                 $a_hostaddress = current($a_hostaddresses);
                                 if ($a_hostaddress['networkports_id'] > 0) {
                                    $networkPort = new NetworkPort();
                                    $networkPort->getFromDB($a_hostaddress['networkports_id']);
                                    $a_arguments[$arg] = str_replace("[[NETWORKPORTNAME]]", $networkPort->fields['name'], $a_arguments[$arg]);
                                 }
                              }
                           }
                        } else if (strstr($a_arguments[$arg], "[")) {
                           $a_arguments[$arg] = PluginMonitoringService::convertArgument($data['id'], $a_arguments[$arg]);
                        }
                        if ($a_arguments == '') {
                           $notadd = 1;
                           if ($notadddescription != '') {
                              $notadddescription .= ", ";
                           }
                           $notadddescription .= "Argument ".$a_arguments[$arg]." Not have value";
                        }
                        $args .= '!'.$a_arguments[$arg];
                        if ($a_arguments[$arg] == ''
                                AND $a_component['alias_command'] != '') {
                           $args .= $a_component['alias_command'];
                        }
                     }
                  }
               }

               $a_hosts[$i]['check_command'] = PluginMonitoringCommand::$command_prefix . $pmCommand->fields['command_name'].$args;
               // Toolbox::logInFile("pm", "check_command : ".$a_hosts[$i]['check_command']."\n");


               $pmCheck->getFromDB($pmComponent->fields['plugin_monitoring_checks_id']);
               $a_hosts[$i]['check_interval'] = $pmCheck->fields['check_interval'];
               $a_hosts[$i]['retry_interval'] = $pmCheck->fields['retry_interval'];
               $a_hosts[$i]['max_check_attempts'] = $pmCheck->fields['max_check_attempts'];

               $timeperiodsuffix = '-'.$pmHostconfig->getValueAncestor('jetlag', $class->fields['entities_id']);
               if ($timeperiodsuffix == '-0') {
                  $timeperiodsuffix = '';
               }
               if ($calendar->getFromDB($pmComponent->fields['calendars_id'])) {
                  $a_hosts[$i]['check_period'] = $calendar->fields['name'].$timeperiodsuffix;
               } else {
                  $a_hosts[$i]['check_period'] = self::$shinkenParameters['shinken']['hosts']['check_period'].$timeperiodsuffix;
               }
               $a_hosts[$i]['active_checks_enabled'] = $a_fields['active_checks_enabled'];
               $a_hosts[$i]['passive_checks_enabled'] = $a_fields['passive_checks_enabled'];

               // Manage freshness
               if ($a_fields['freshness_count'] == 0) {
                  $a_hosts[$i]['check_freshness'] = '0';
                  $a_hosts[$i]['freshness_threshold'] = '0';
               } else {
                  $multiple = 1;
                  if ($a_fields['freshness_type'] == 'seconds') {
                     $multiple = 1;
                  } else if ($a_fields['freshness_type'] == 'minutes') {
                     $multiple = 60;
                  } else if ($a_fields['freshness_type'] == 'hours') {
                     $multiple = 3600;
                  } else if ($a_fields['freshness_type'] == 'days') {
                     $multiple = 86400;
                  }
                  $a_hosts[$i]['check_freshness'] = '1';
                  $a_hosts[$i]['freshness_threshold'] = (string)($a_fields['freshness_count'] * $multiple);
               }

               // Manage event handler
               if ($a_fields['plugin_monitoring_eventhandlers_id'] > 0) {
                  if ($a_fields->getFromDB($a_fields['plugin_monitoring_eventhandlers_id'])) {
                     $a_hosts[$i]['event_handler'] = $pmEventhandler->fields['command_name'];
                  }
               }

               // Realm
               $pmRealm->getFromDB($pmHostconfig->getValueAncestor('plugin_monitoring_realms_id',
                                                                                    $class->fields['entities_id'],
                                                                                    $classname,
                                                                                    $class->getID()));
               $a_hosts[$i]['realm'] = $pmRealm->fields['name'];

               if (! empty(self::$shinkenParameters['shinken']['hosts']['use'])) $a_hosts[$i]['use'] = self::$shinkenParameters['shinken']['hosts']['use'];

               if (! empty(self::$shinkenParameters['shinken']['hosts']['process_perf_data'])) $a_hosts[$i]['process_perf_data'] = self::$shinkenParameters['shinken']['hosts']['process_perf_data'];
               if (! empty(self::$shinkenParameters['shinken']['hosts']['notification_period'])) $a_hosts[$i]['notification_period'] = self::$shinkenParameters['shinken']['hosts']['notification_period'];
               if (! empty(self::$shinkenParameters['shinken']['hosts']['notification_options'])) $a_hosts[$i]['notification_options'] = self::$shinkenParameters['shinken']['hosts']['notification_options'];
               if (! empty(self::$shinkenParameters['shinken']['hosts']['notification_interval'])) $a_hosts[$i]['notification_interval'] = self::$shinkenParameters['shinken']['hosts']['notification_interval'];

               if (! empty(self::$shinkenParameters['shinken']['hosts']['notes'])) $a_hosts[$i]['notes'] = self::$shinkenParameters['shinken']['hosts']['notes'];
               if (! empty(self::$shinkenParameters['shinken']['hosts']['notes_url'])) $a_hosts[$i]['notes_url'] = self::$shinkenParameters['shinken']['hosts']['notes_url'];
               if (! empty(self::$shinkenParameters['shinken']['hosts']['action_url'])) $a_hosts[$i]['action_url'] = self::$shinkenParameters['shinken']['hosts']['action_url'];
               if (! empty(self::$shinkenParameters['shinken']['hosts']['icon_image'])) $a_hosts[$i]['icon_image'] = self::$shinkenParameters['shinken']['hosts']['icon_image'];
               if (! empty(self::$shinkenParameters['shinken']['hosts']['icon_image_alt'])) $a_hosts[$i]['icon_image_alt'] = self::$shinkenParameters['shinken']['hosts']['icon_image_alt'];
               if (! empty(self::$shinkenParameters['shinken']['hosts']['vrml_image'])) $a_hosts[$i]['vrml_image'] = self::$shinkenParameters['shinken']['hosts']['vrml_image'];
               if (! empty(self::$shinkenParameters['shinken']['hosts']['statusmap_image'])) $a_hosts[$i]['statusmap_image'] = self::$shinkenParameters['shinken']['hosts']['statusmap_image'];


               // For contacts, check if a component catalog contains the host associated component ...
               $a_hosts[$i]['contacts'] = '';

               if (($a_fields['passive_checks_enabled'] == '1') and ($a_fields['active_checks_enabled'] == '0')) {
                  // Specific query if host is only passively checked ...
                  // Find the first component catalog in which the host is used ...
                  $querycont = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
                     LEFT JOIN `glpi_plugin_monitoring_services`
                        ON `plugin_monitoring_componentscatalogs_hosts_id`
                           = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
                     WHERE `items_id`='".$data['items_id']."' AND `itemtype`='".$data['itemtype']."'
                     LIMIT 1";
               } else {
                  // Find component catalog which contains the host associated component ...
                  $querycont = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
                     LEFT JOIN `glpi_plugin_monitoring_services`
                        ON `plugin_monitoring_componentscatalogs_hosts_id`
                           = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
                     WHERE `plugin_monitoring_components_id`='".$pmComponent->fields['id']."' AND
                        `items_id`='".$data['items_id']."' AND `itemtype`='".$data['itemtype']."'
                     LIMIT 1";
               }

               $resultcont = $DB->query($querycont);
               if ($DB->numrows($resultcont) != 0) {
                  $a_componentscatalogs_hosts = $DB->fetch_assoc($resultcont);
                  // Notification interval
                  $pmComponentscatalog = new PluginMonitoringComponentscatalog();
                  $pmComponentscatalog->getFromDB($a_componentscatalogs_hosts['plugin_monitoring_componentscalalog_id']);
                  $a_hosts[$i]['notification_interval'] = $pmComponentscatalog->fields['notification_interval'];

                  $a_contacts = array();
                  $a_list_contact = $pmContact_Item->find("`itemtype`='PluginMonitoringComponentscatalog'
                     AND `items_id`='".$a_componentscatalogs_hosts['plugin_monitoring_componentscalalog_id']."'");
                  foreach ($a_list_contact as $data_contact) {
                     if (isset($a_contacts_entities[$a_componentscatalogs_hosts['plugin_monitoring_componentscalalog_id']][$data_contact['users_id']])) {
                        if (in_array($class->fields['entities_id'], $a_contacts_entities[$a_componentscatalogs_hosts['plugin_monitoring_componentscalalog_id']][$data_contact['users_id']])) {
                           $user->getFromDB($data_contact['users_id']);
                           $a_contacts[] = $user->fields['name'];
                        }
                     }
                  }
                  if (count($a_contacts) > 0) {
                     $a_contacts_unique = array_unique($a_contacts);
                     $a_hosts[$i]['contacts'] = implode(',', $a_contacts_unique);
                  }
               }

               $i++;
            // }
         }
      }


      // Fake host for business rules
      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         " - add host_for_bp\n"
      );
      $a_hosts[$i]['host_name'] = self::$shinkenParameters['shinken']['fake_hosts']['name_prefix'] . self::$shinkenParameters['shinken']['fake_hosts']['bp_host'];
      $a_hosts[$i]['check_command'] = PluginMonitoringCommand::$command_prefix . 'check_dummy!0';
      $a_hosts[$i]['alias'] = 'Fake host for business rules';
      $a_hosts[$i]['_HOSTID'] = '0';
      $a_hosts[$i]['_ITEMSID'] = '0';
      $a_hosts[$i]['_ITEMTYPE'] = 'Computer';
      $a_hosts[$i]['address'] = '127.0.0.1';
      $a_hosts[$i]['parents'] = self::$shinkenParameters['shinken']['fake_hosts']['name_prefix'] . self::$shinkenParameters['shinken']['fake_hosts']['root_parent'];
      $a_hosts[$i]['hostgroups'] = self::$shinkenParameters['shinken']['fake_hosts']['hostgroup_name'];
      $a_hosts[$i]['check_interval'] = '60';
      $a_hosts[$i]['retry_interval'] = '1';
      $a_hosts[$i]['max_check_attempts'] = '1';
      $a_hosts[$i]['check_period'] = '24x7';
      if (self::$shinkenParameters['shinken']['fake_contacts']['build']) {
         $a_hosts[$i]['contacts'] = self::$shinkenParameters['shinken']['fake_contacts']['contact_name'];
      } else {
         $a_hosts[$i]['contacts'] = '';
      }
      if (! empty(self::$shinkenParameters['shinken']['fake_hosts']['use'])) $a_hosts[$i]['use'] = self::$shinkenParameters['shinken']['fake_hosts']['use'];

      if (! empty(self::$shinkenParameters['shinken']['hosts']['process_perf_data'])) $a_hosts[$i]['process_perf_data'] = self::$shinkenParameters['shinken']['hosts']['process_perf_data'];
      if (! empty(self::$shinkenParameters['shinken']['hosts']['notification_period'])) $a_hosts[$i]['notification_period'] = self::$shinkenParameters['shinken']['hosts']['notification_period'];
      if (! empty(self::$shinkenParameters['shinken']['hosts']['notification_options'])) $a_hosts[$i]['notification_options'] = self::$shinkenParameters['shinken']['hosts']['notification_options'];
      if (! empty(self::$shinkenParameters['shinken']['hosts']['notification_interval'])) $a_hosts[$i]['notification_interval'] = self::$shinkenParameters['shinken']['hosts']['notification_interval'];

      if (! empty(self::$shinkenParameters['shinken']['hosts']['notes'])) $a_hosts[$i]['notes'] = self::$shinkenParameters['shinken']['hosts']['notes'];
      if (! empty(self::$shinkenParameters['shinken']['hosts']['notes_url'])) $a_hosts[$i]['notes_url'] = self::$shinkenParameters['shinken']['hosts']['notes_url'];
      if (! empty(self::$shinkenParameters['shinken']['hosts']['action_url'])) $a_hosts[$i]['action_url'] = self::$shinkenParameters['shinken']['hosts']['action_url'];
      if (! empty(self::$shinkenParameters['shinken']['hosts']['icon_image'])) $a_hosts[$i]['icon_image'] = self::$shinkenParameters['shinken']['hosts']['icon_image'];
      if (! empty(self::$shinkenParameters['shinken']['hosts']['icon_image_alt'])) $a_hosts[$i]['icon_image_alt'] = self::$shinkenParameters['shinken']['hosts']['icon_image_alt'];
      if (! empty(self::$shinkenParameters['shinken']['hosts']['vrml_image'])) $a_hosts[$i]['vrml_image'] = self::$shinkenParameters['shinken']['hosts']['vrml_image'];
      if (! empty(self::$shinkenParameters['shinken']['hosts']['statusmap_image'])) $a_hosts[$i]['statusmap_image'] = self::$shinkenParameters['shinken']['hosts']['statusmap_image'];
      $i++;

//      // Add important
//      $a_hosts[$i]['name']            = 'pm-important';
//      $a_hosts[$i]['host_name']       = '';
//      $a_hosts[$i]['register']        = '0';
//      $a_hosts[$i]['business_impact'] = '4';
//      $i++;
//
//      // Add fake
//      $a_hosts[$i]['name']            = 'fake';
//      $a_hosts[$i]['host_name']       = '';
//      $i++;

      // Add one fake host for each entity
      if (self::$shinkenParameters['shinken']['fake_hosts']['build']) {
         PluginMonitoringToolbox::logIfExtradebug(
            'pm-shinken',
            " - add fake hosts for parents relationship\n"
         );

         // Main root parent
         $a_hosts[$i]['host_name'] = self::$shinkenParameters['shinken']['fake_hosts']['name_prefix'] . self::$shinkenParameters['shinken']['fake_hosts']['root_parent'];
         $a_hosts[$i]['check_command'] = PluginMonitoringCommand::$command_prefix . 'check_dummy!0';
         $a_hosts[$i]['alias'] = 'Main root parent';
         $a_hosts[$i]['_HOSTID'] = '0';
         $a_hosts[$i]['_ITEMSID'] = '0';
         $a_hosts[$i]['_ITEMTYPE'] = 'Computer';
         $a_hosts[$i]['address'] = '127.0.0.1';
         $a_hosts[$i]['parents'] = '';
         $a_hosts[$i]['hostgroups'] = self::$shinkenParameters['shinken']['fake_hosts']['hostgroup_name'];
         $a_hosts[$i]['check_interval'] = '60';
         $a_hosts[$i]['retry_interval'] = '1';
         $a_hosts[$i]['max_check_attempts'] = '1';
         $a_hosts[$i]['check_period'] = '24x7';
         if (self::$shinkenParameters['shinken']['fake_contacts']['build']) {
            $a_hosts[$i]['contacts'] = self::$shinkenParameters['shinken']['fake_contacts']['contact_name'];
         } else {
            $a_hosts[$i]['contacts'] = '';
         }
         if (! empty(self::$shinkenParameters['shinken']['fake_hosts']['use'])) $a_hosts[$i]['use'] = self::$shinkenParameters['shinken']['fake_hosts']['use'];

         if (! empty(self::$shinkenParameters['shinken']['hosts']['process_perf_data'])) $a_hosts[$i]['process_perf_data'] = self::$shinkenParameters['shinken']['hosts']['process_perf_data'];
         if (! empty(self::$shinkenParameters['shinken']['hosts']['notification_period'])) $a_hosts[$i]['notification_period'] = self::$shinkenParameters['shinken']['hosts']['notification_period'];
         if (! empty(self::$shinkenParameters['shinken']['hosts']['notification_options'])) $a_hosts[$i]['notification_options'] = self::$shinkenParameters['shinken']['hosts']['notification_options'];
         if (! empty(self::$shinkenParameters['shinken']['hosts']['notification_interval'])) $a_hosts[$i]['notification_interval'] = self::$shinkenParameters['shinken']['hosts']['notification_interval'];

         if (! empty(self::$shinkenParameters['shinken']['hosts']['notes'])) $a_hosts[$i]['notes'] = self::$shinkenParameters['shinken']['hosts']['notes'];
         if (! empty(self::$shinkenParameters['shinken']['hosts']['notes_url'])) $a_hosts[$i]['notes_url'] = self::$shinkenParameters['shinken']['hosts']['notes_url'];
         if (! empty(self::$shinkenParameters['shinken']['hosts']['action_url'])) $a_hosts[$i]['action_url'] = self::$shinkenParameters['shinken']['hosts']['action_url'];
         if (! empty(self::$shinkenParameters['shinken']['hosts']['icon_image'])) $a_hosts[$i]['icon_image'] = self::$shinkenParameters['shinken']['hosts']['icon_image'];
         if (! empty(self::$shinkenParameters['shinken']['hosts']['icon_image_alt'])) $a_hosts[$i]['icon_image_alt'] = self::$shinkenParameters['shinken']['hosts']['icon_image_alt'];
         if (! empty(self::$shinkenParameters['shinken']['hosts']['vrml_image'])) $a_hosts[$i]['vrml_image'] = self::$shinkenParameters['shinken']['hosts']['vrml_image'];
         if (! empty(self::$shinkenParameters['shinken']['hosts']['statusmap_image'])) $a_hosts[$i]['statusmap_image'] = self::$shinkenParameters['shinken']['hosts']['statusmap_image'];
         $i++;

         $a_entities_allowed = $pmEntity->getEntitiesByTag($tag);
         $a_entities_list = array();
         foreach ($a_entities_allowed as $entity) {
            $a_entities_list = getSonsOf("glpi_entities", $entity);
         }
         $where = '';
         if (! isset($a_entities_allowed['-1'])) {
            $where = getEntitiesRestrictRequest("WHERE", "glpi_entities", '', $a_entities_list);
         }

         $query = "SELECT
            `glpi_entities`.`id` AS entityId, `glpi_entities`.`name` AS entityName
            FROM `glpi_entities` $where";
         $result = $DB->query($query);
         while ($dataEntity=$DB->fetch_array($result)) {
            // Hostgroup name : used as host name for parents ...
            $fake_host_name = self::shinkenFilter($dataEntity['entityName']);
            $fake_host_name = preg_replace("/[ ]/","_",$fake_host_name);

            $a_hosts[$i]['host_name'] = self::$shinkenParameters['shinken']['fake_hosts']['name_prefix'] . $fake_host_name;
            $a_hosts[$i]['check_command'] = PluginMonitoringCommand::$command_prefix . 'check_dummy!0';
            $a_hosts[$i]['alias'] = $dataEntity['entityName'];
            $a_hosts[$i]['_HOSTID'] = '0';
            $a_hosts[$i]['_ITEMSID'] = '0';
            $a_hosts[$i]['_ITEMTYPE'] = 'Computer';
            $a_hosts[$i]['address'] = '127.0.0.1';
            $a_hosts[$i]['parents'] = self::$shinkenParameters['shinken']['fake_hosts']['name_prefix'] . self::$shinkenParameters['shinken']['fake_hosts']['root_parent'];
            $a_hosts[$i]['hostgroups'] = self::$shinkenParameters['shinken']['fake_hosts']['hostgroup_name'];
            $a_hosts[$i]['check_interval'] = '60';
            $a_hosts[$i]['retry_interval'] = '1';
            $a_hosts[$i]['max_check_attempts'] = '1';
            $a_hosts[$i]['check_period'] = '24x7';
            if (self::$shinkenParameters['shinken']['fake_contacts']['build']) {
               $a_hosts[$i]['contacts'] = self::$shinkenParameters['shinken']['fake_contacts']['contact_name'];
            } else {
               $a_hosts[$i]['contacts'] = '';
            }
            if (! empty(self::$shinkenParameters['shinken']['fake_hosts']['use'])) $a_hosts[$i]['use'] = self::$shinkenParameters['shinken']['fake_hosts']['use'];

            if (! empty(self::$shinkenParameters['shinken']['hosts']['process_perf_data'])) $a_hosts[$i]['process_perf_data'] = self::$shinkenParameters['shinken']['hosts']['process_perf_data'];
            if (! empty(self::$shinkenParameters['shinken']['hosts']['notification_period'])) $a_hosts[$i]['notification_period'] = self::$shinkenParameters['shinken']['hosts']['notification_period'];
            if (! empty(self::$shinkenParameters['shinken']['hosts']['notification_options'])) $a_hosts[$i]['notification_options'] = self::$shinkenParameters['shinken']['hosts']['notification_options'];
            if (! empty(self::$shinkenParameters['shinken']['hosts']['notification_interval'])) $a_hosts[$i]['notification_interval'] = self::$shinkenParameters['shinken']['hosts']['notification_interval'];

            if (! empty(self::$shinkenParameters['shinken']['hosts']['notes'])) $a_hosts[$i]['notes'] = self::$shinkenParameters['shinken']['hosts']['notes'];
            if (! empty(self::$shinkenParameters['shinken']['hosts']['notes_url'])) $a_hosts[$i]['notes_url'] = self::$shinkenParameters['shinken']['hosts']['notes_url'];
            if (! empty(self::$shinkenParameters['shinken']['hosts']['action_url'])) $a_hosts[$i]['action_url'] = self::$shinkenParameters['shinken']['hosts']['action_url'];
            if (! empty(self::$shinkenParameters['shinken']['hosts']['icon_image'])) $a_hosts[$i]['icon_image'] = self::$shinkenParameters['shinken']['hosts']['icon_image'];
            if (! empty(self::$shinkenParameters['shinken']['hosts']['icon_image_alt'])) $a_hosts[$i]['icon_image_alt'] = self::$shinkenParameters['shinken']['hosts']['icon_image_alt'];
            if (! empty(self::$shinkenParameters['shinken']['hosts']['vrml_image'])) $a_hosts[$i]['vrml_image'] = self::$shinkenParameters['shinken']['hosts']['vrml_image'];
            if (! empty(self::$shinkenParameters['shinken']['hosts']['statusmap_image'])) $a_hosts[$i]['statusmap_image'] = self::$shinkenParameters['shinken']['hosts']['statusmap_image'];

            $i++;
         }
         PluginMonitoringToolbox::logIfExtradebug(
            'pm-shinken',
            "End generateHostgroupsCfg\n"
         );
      }

      // Check if parents all exist in hosts config
      foreach ($a_parents_found as $host => $num) {
         if (!isset($a_hosts_found[$host])) {
            // Delete parents not added in hosts config
            foreach ($a_hosts as $id=>$data) {
               if ($data['parents'] == $host) {
                  $a_hosts[$id]['parents'] = '';
               }
            }
         }
      }


      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "End generateHostsCfg\n"
      );

      if ($file == "1") {
         $config = "# Generated by plugin monitoring for GLPI\n# on ".date("Y-m-d H:i:s")."\n\n";

         foreach ($a_hosts as $data) {
            $config .= $this->writeFile("host", $data);
         }
         return array('hosts.cfg', $config);

      } else {
         return $a_hosts;
      }
   }



   function generateServicesCfg($file=0, $tag='') {
      global $DB;

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "Starting generateServicesCfg services ($tag) ...\n"
      );
      $pMonitoringCommand      = new PluginMonitoringCommand();
      $pmEventhandler          = new PluginMonitoringEventhandler();
      $pMonitoringCheck        = new PluginMonitoringCheck();
      $pmComponent             = new PluginMonitoringComponent();
      $pmEntity                = new PluginMonitoringEntity();
      $pmContact_Item          = new PluginMonitoringContact_Item();
      $networkPort             = new NetworkPort();
      $pmService               = new PluginMonitoringService();
      $pmComponentscatalog     = new PluginMonitoringComponentscatalog();
      $pmHostconfig            = new PluginMonitoringHostconfig();
      $calendar                = new Calendar();
      $user                    = new User();
      $profile_User            = new Profile_User();
      $pmConfig                = new PluginMonitoringConfig();
      $computerType            = new ComputerType();

      $a_services = array();
      $i=0;

      // Get computer type contener / VM
      $conteners = $computerType->find("`name`='BSDJail'");

      $pmConfig->getFromDB(1);

      // TODO: only contacts in allowed entities ...
      // Prepare individual contacts
      $a_contacts_entities = array();
      $a_list_contact = $pmContact_Item->find("`itemtype`='PluginMonitoringComponentscatalog'
         AND `users_id`>0");
      foreach ($a_list_contact as $data) {
         $contactentities = getSonsOf('glpi_entities', $data['entities_id']);
         if (isset($a_contacts_entities[$data['items_id']][$data['users_id']])) {
            $contactentities = array_merge($contactentities, $a_contacts_entities[$data['items_id']][$data['users_id']]);
         }
         $a_contacts_entities[$data['items_id']][$data['users_id']] = $contactentities;
      }
      // Prepare groups contacts
      $group = new Group();
      $a_list_contact = $pmContact_Item->find("`itemtype`='PluginMonitoringComponentscatalog'
         AND `groups_id`>0");
      foreach ($a_list_contact as $data) {
         $group->getFromDB($data['groups_id']);
         if ($group->fields['is_recursive'] == 1) {
            $contactentities = getSonsOf('glpi_entities', $group->fields['entities_id']);
         } else {
            $contactentities = array($group->fields['entities_id'] => $group->fields['entities_id']);
         }
         $queryg = "SELECT * FROM `glpi_groups_users`
            WHERE `groups_id`='".$data['groups_id']."'";
         $resultg = $DB->query($queryg);
         while ($datag=$DB->fetch_array($resultg)) {
            if (isset($a_contacts_entities[$data['items_id']][$datag['users_id']])) {
               $contactentities = array_merge($contactentities, $a_contacts_entities[$data['items_id']][$datag['users_id']]);
            }
            $a_contacts_entities[$data['items_id']][$datag['users_id']] = $contactentities;
         }
      }



      $a_entities_allowed = $pmEntity->getEntitiesByTag($tag);
      // Toolbox::logInFile("pm-shinken", " Allowed entities:\n");
      $a_entities_list = array();
      foreach ($a_entities_allowed as $entity) {
         $a_entities_list = getSonsOf("glpi_entities", $entity);
      }
      $where = '';
      if (! isset($a_entities_allowed['-1'])) {
         $where = getEntitiesRestrictRequest("WHERE", "glpi_plugin_monitoring_services", '', $a_entities_list);
      }

      // --------------------------------------------------
      // "Normal" services ....
      $query = "SELECT * FROM `glpi_plugin_monitoring_services` $where";
      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "Services: $query\n"
      );
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         // Toolbox::logInFile("pm-shinken", " - fetch service ".$data['id']."\n");

         // if (isset($a_entities_allowed['-1'])
                 // OR isset($a_entities_allowed[$item->fields['entities_id']])) {
            $notadd = 0;
            $notadddescription = '';
            $a_component = current($pmComponent->find("`id`='".$data['plugin_monitoring_components_id']."'", "", 1));
            if (empty($a_component)) {
               continue;
            }
            $a_hostname        = array();
            $a_hostname_single = array();
            $a_hostname_type   = array();
            $a_hostname_id     = array();
            $queryh = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
               WHERE `id` = '".$data['plugin_monitoring_componentscatalogs_hosts_id']."'
               LIMIT 1";
            $resulth = $DB->query($queryh);
            $hostname = '';
            $plugin_monitoring_componentscatalogs_id = 0;
            $computerTypes_id = 0;
            $entities_id = 0;
            while ($datah=$DB->fetch_array($resulth)) {
               $itemtype = $datah['itemtype'];
               $item = new $itemtype();
               if ($item->getFromDB($datah['items_id'])) {
                  // if (isset($a_entities_allowed['-1'])
                          // OR isset($a_entities_allowed[$item->fields['entities_id']])) {

                     // Fix if hostname is not defined ...
                     if (! empty($item->fields['name'])) {
                        $h = self::shinkenFilter($item->fields['name']);
                        $a_hostname_single[] = $h;
                        if ($pmConfig->fields['append_id_hostname'] == 1) {
                           $h .= "-".$datah['items_id'];
                        }
                        $a_hostname[] = $h;
                        $a_hostname_type[] = $datah['itemtype'];
                        $a_hostname_id[] = $datah['items_id'];
                        $hostname = $item->fields['name'];
                        $entities_id = $item->fields['entities_id'];
                        $plugin_monitoring_componentscatalogs_id = $datah['plugin_monitoring_componentscalalog_id'];
                        if ($itemtype == 'Computer') {
                           $computerTypes_id = $item->fields['computertypes_id'];
                        }
                     }
                  // }
               }
            }
            if (count($a_hostname) > 0) {
               if (isset($_SESSION['plugin_monitoring']['servicetemplates'][$a_component['id']])) {
                  $a_services[$i]['use'] = $_SESSION['plugin_monitoring']['servicetemplates'][$a_component['id']];
               }
               $a_services[$i]['host_name'] = implode(",", array_unique($a_hostname));
               $a_services[$i]['_HOSTITEMSID'] = implode(",", array_unique($a_hostname_id));
               $a_services[$i]['_HOSTITEMTYPE'] = implode(",", array_unique($a_hostname_type));

               // Define display_name / service_description
               $a_services[$i]['service_description'] = (! empty($a_component['description'])) ? $a_component['description'] : self::shinkenFilter($a_component['name']);
               // In case have multiple networkt port, may have description different, else be dropped by shinken
               if ($data['networkports_id'] > 0) {
                  $networkPort->getFromDB($data['networkports_id']);
                  $a_services[$i]['service_description'] .= '-'.self::shinkenFilter($networkPort->fields['name']);
               }
               $a_services[$i]['display_name'] = $a_component['name'];
               // $a_services[$i]['_ENTITIESID'] = $item->fields['entities_id'];
               // $a_services[$i]['_ITEMSID'] = $data['id'];
               // $a_services[$i]['_ITEMTYPE'] = 'Service';
               PluginMonitoringToolbox::logIfExtradebug(
                  'pm-shinken',
                  " - add service ".$a_services[$i]['service_description']." on ".$a_services[$i]['host_name']."\n"
               );

               if (isset(self::$shinkenParameters['glpi']['entityId'])) {
                  $a_services[$i][self::$shinkenParameters['glpi']['entityId']] =
                     $item->fields['entities_id'];
               }
               if (isset(self::$shinkenParameters['glpi']['itemType'])) {
                  $a_services[$i][self::$shinkenParameters['glpi']['itemType']] =
                     'Service';
               }
               if (isset(self::$shinkenParameters['glpi']['itemId'])) {
                  $a_services[$i][self::$shinkenParameters['glpi']['itemId']] =
                     $data['id'];
               }

               // Manage freshness
               if ($a_component['freshness_count'] == 0) {
                  $a_services[$i]['check_freshness'] = '0';
                  $a_services[$i]['freshness_threshold'] = '3600';
               } else {
                  $multiple = 1;
                  if ($a_component['freshness_type'] == 'seconds') {
                     $multiple = 1;
                  } else if ($a_component['freshness_type'] == 'minutes') {
                     $multiple = 60;
                  } else if ($a_component['freshness_type'] == 'hours') {
                     $multiple = 3600;
                  } else if ($a_component['freshness_type'] == 'days') {
                     $multiple = 86400;
                  }
                  $a_services[$i]['check_freshness'] = '1';
                  $a_services[$i]['freshness_threshold'] = (string)($a_component['freshness_count'] * $multiple);
               }

               $pMonitoringCommand->getFromDB($a_component['plugin_monitoring_commands_id']);
               // Manage arguments
               $array = array();
               preg_match_all("/\\$(ARG\d+)\\$/", $pMonitoringCommand->fields['command_line'], $array);
               sort($array[0]);
               $a_arguments = importArrayFromDB($a_component['arguments']);
               $a_argumentscustom = importArrayFromDB($data['arguments']);
               foreach ($a_argumentscustom as $key=>$value) {
                  $a_arguments[$key] = $value;
               }
               foreach ($a_arguments as $key=>$value) {
                  $a_arguments[$key] = str_replace('!', '\!', html_entity_decode($value));
               }
               $args = '';
               foreach ($array[0] as $arg) {
                  if ($arg != '$PLUGINSDIR$'
                          AND $arg != '$NAGIOSPLUGINSDIR$'
                          AND $arg != '$HOSTADDRESS$'
                          AND $arg != '$MYSQLUSER$'
                          AND $arg != '$MYSQLPASSWORD$') {
                     $arg = str_replace('$', '', $arg);
                     if (!isset($a_arguments[$arg])) {
                        $args .= '!';
                     } else {
                        if (strstr($a_arguments[$arg], "[[HOSTNAME]]")) {
                           $a_arguments[$arg] = str_replace("[[HOSTNAME]]", $hostname, $a_arguments[$arg]);
                        } elseif (strstr($a_arguments[$arg], "[[NETWORKPORTDESCR]]")){
                           if (class_exists("PluginFusioninventoryNetworkPort")) {
                              $pfNetworkPort = new PluginFusioninventoryNetworkPort();
                              $pfNetworkPort->loadNetworkport($data['networkports_id']);
                              $descr = $pfNetworkPort->getValue("ifdescr");
                              $a_arguments[$arg] = str_replace("[[NETWORKPORTDESCR]]", $descr, $a_arguments[$arg]);
                           }
                        } elseif (strstr($a_arguments[$arg], "[[NETWORKPORTNUM]]")){
                           $networkPort = new NetworkPort();
                           $networkPort->getFromDB($data['networkports_id']);
                           $logicalnum = $pfNetworkPort->fields['logical_number'];
                           $a_arguments[$arg] = str_replace("[[NETWORKPORTNUM]]", $logicalnum, $a_arguments[$arg]);
                        } elseif (strstr($a_arguments[$arg], "[[NETWORKPORTNAME]]")){
                           if (isset($data['networkports_id'])
                                   && $data['networkports_id'] > 0) {
                              $networkPort = new NetworkPort();
                              $networkPort->getFromDB($data['networkports_id']);
                              $portname = $pfNetworkPort->fields['name'];
                              $a_arguments[$arg] = str_replace("[[NETWORKPORTNAME]]", $portname, $a_arguments[$arg]);
                           } else if ($a_services[$i]['_HOSTITEMTYPE'] == 'Computer') {
                              // Get networkportname of networkcard defined
                              $pmHostaddress = new PluginMonitoringHostaddress();
                              $a_hostaddresses = $pmHostaddress->find("`itemtype`='Computer'"
                                      . " AND  `items_id`='".$a_services[$i]['_HOSTITEMSID']."'", '', 1);
                              if (count($a_hostaddresses) == 1) {
                                 $a_hostaddress = current($a_hostaddresses);
                                 if ($a_hostaddress['networkports_id'] > 0) {
                                    $networkPort = new NetworkPort();
                                    $networkPort->getFromDB($a_hostaddress['networkports_id']);
                                    $a_arguments[$arg] = str_replace("[[NETWORKPORTNAME]]", $networkPort->fields['name'], $a_arguments[$arg]);
                                 }
                              }
                           }
                        } else if (strstr($a_arguments[$arg], "[")) {
                           $a_arguments[$arg] = PluginMonitoringService::convertArgument($data['id'], $a_arguments[$arg]);
                        }
                        if ($a_arguments == '') {
                           $notadd = 1;
                           if ($notadddescription != '') {
                              $notadddescription .= ", ";
                           }
                           $notadddescription .= "Argument ".$a_arguments[$arg]." do not have value";
                        }
                        $args .= '!'.$a_arguments[$arg];
                        if ($a_arguments[$arg] == ''
                                AND $a_component['alias_command'] != '') {
                           $args .= $a_component['alias_command'];
                        }
                     }
                  }
               }
               // End manage arguments
               if ($a_component['remotesystem'] == 'nrpe') {
                  if ($a_component['alias_command'] != '') {
                     $alias_command = $a_component['alias_command'];
                     if (strstr($alias_command, '[[IP]]')) {
                        $split = explode('-', current($a_hostname));
                        $ip = PluginMonitoringHostaddress::getIp(
                                $a_hostname_id[0], $a_hostname_type[0], '');
                        $alias_command = str_replace("[[IP]]", $ip, $alias_command);
                     }
                     if (current($a_hostname_type) == 'Computer') {
                        if ($pmConfig->fields['nrpe_prefix_contener'] == 1) {
                           if (isset($conteners[$computerTypes_id])) {
                              // get Host of contener/VM
                              $where = "LOWER(`uuid`)".  ComputerVirtualMachine::getUUIDRestrictRequest($item->fields['uuid']);
                              $hosts = getAllDatasFromTable('glpi_computervirtualmachines', $where);
                              if (!empty($hosts)) {
                                 $host = current($hosts);
//                                 $ip = PluginMonitoringHostaddress::getIp($host['computers_id'], 'Computer', '');
                                 $alias_command = current($a_hostname_single)."_".$alias_command;
                              }
                           }
                        }
                     }
                     $a_services[$i]['check_command'] = PluginMonitoringCommand::$command_prefix . "check_nrpe!".$alias_command;
                  } else {
                     $a_services[$i]['check_command'] = PluginMonitoringCommand::$command_prefix . "check_nrpe!".$pMonitoringCommand->fields['command_name'];
                  }
               } else {
                  $a_services[$i]['check_command'] = PluginMonitoringCommand::$command_prefix . $pMonitoringCommand->fields['command_name'].$args;
               }

               // * Manage event handler
               if ($a_component['plugin_monitoring_eventhandlers_id'] > 0) {
                  if ($pmEventhandler->getFromDB($a_component['plugin_monitoring_eventhandlers_id'])) {
                     $a_services[$i]['event_handler'] = $pmEventhandler->fields['command_name'];
                  }
               }

               if (! empty(self::$shinkenParameters['shinken']['services']['process_perf_data'])) $a_services[$i]['process_perf_data'] = self::$shinkenParameters['shinken']['services']['process_perf_data'];

               if (! empty(self::$shinkenParameters['shinken']['services']['notes'])) $a_services[$i]['notes'] = self::$shinkenParameters['shinken']['services']['notes'];
               if (! empty(self::$shinkenParameters['shinken']['services']['notes_url'])) $a_services[$i]['notes_url'] = self::$shinkenParameters['shinken']['services']['notes_url'];
               if (! empty(self::$shinkenParameters['shinken']['services']['action_url'])) $a_services[$i]['action_url'] = self::$shinkenParameters['shinken']['services']['action_url'];
               if (! empty(self::$shinkenParameters['shinken']['services']['icon_image'])) $a_services[$i]['icon_image'] = self::$shinkenParameters['shinken']['services']['icon_image'];
               if (! empty(self::$shinkenParameters['shinken']['services']['icon_image_alt'])) $a_services[$i]['icon_image_alt'] = self::$shinkenParameters['shinken']['services']['icon_image_alt'];

               // * Contacts
               $a_contacts = array();
               $a_list_contact = $pmContact_Item->find("`itemtype`='PluginMonitoringComponentscatalog'
                  AND `items_id`='".$plugin_monitoring_componentscatalogs_id."'");
               foreach ($a_list_contact as $data_contact) {
                  if ($data_contact['users_id'] > 0) {
                     if (isset($a_contacts_entities[$plugin_monitoring_componentscatalogs_id][$data_contact['users_id']])) {
                        if (in_array($data['entities_id'], $a_contacts_entities[$plugin_monitoring_componentscatalogs_id][$data_contact['users_id']])) {
                           $user->getFromDB($data_contact['users_id']);
                           $a_contacts[] = $user->fields['name'];
                        }
                     }
                  } else if ($data_contact['groups_id'] > 0) {
                     $queryg = "SELECT * FROM `glpi_groups_users`
                        WHERE `groups_id`='".$data_contact['groups_id']."'";
                     $resultg = $DB->query($queryg);
                     while ($datag=$DB->fetch_array($resultg)) {
                        if (in_array($data['entities_id'], $a_contacts_entities[$plugin_monitoring_componentscatalogs_id][$datag['users_id']])) {
                           $user->getFromDB($datag['users_id']);
                           $a_contacts[] = $user->fields['name'];
                        }
                     }
                  }
               }

               $a_contacts_unique = array_unique($a_contacts);
               $a_services[$i]['contacts'] = implode(',', $a_contacts_unique);

               // ** If shinken not use templates or template not defined :
               if (!isset($_SESSION['plugin_monitoring']['servicetemplates'][$a_component['id']])) {
                  $pMonitoringCheck->getFromDB($a_component['plugin_monitoring_checks_id']);
                  $a_services[$i]['check_interval'] = $pMonitoringCheck->fields['check_interval'];
                  $a_services[$i]['retry_interval'] = $pMonitoringCheck->fields['retry_interval'];
                  $a_services[$i]['max_check_attempts'] = $pMonitoringCheck->fields['max_check_attempts'];
                  $timeperiodsuffix = '-'.$pmHostconfig->getValueAncestor('jetlag', $entities_id);
                  if ($timeperiodsuffix == '-0') {
                     $timeperiodsuffix = '';
                  }
                  if ($calendar->getFromDB($a_component['calendars_id'])) {
                     $a_services[$i]['check_period'] = $calendar->fields['name'].$timeperiodsuffix;
                  }
                  $a_services[$i]['notification_interval'] = '30';
                  $a_services[$i]['notification_period'] = "24x7";
                  $a_services[$i]['notification_options'] = 'w,u,c,r,f,s';
                  $a_services[$i]['process_perf_data'] = '1';
                  $a_services[$i]['active_checks_enabled'] = '1';
                  $a_services[$i]['passive_checks_enabled'] = '1';
                  $a_services[$i]['parallelize_check'] = '1';
                  $a_services[$i]['obsess_over_service'] = '1';
                  $a_services[$i]['check_freshness'] = '1';
                  $a_services[$i]['freshness_threshold'] = '3600';
                  $a_services[$i]['notifications_enabled'] = '1';

                  if (isset($a_services[$i]['event_handler'])) {
                     $a_services[$i]['event_handler_enabled'] = '1';
                  } else {
                     $a_services[$i]['event_handler_enabled'] = '0';
                     // $a_services[$i]['event_handler_enabled'] = '';
                  }
                  $a_services[$i]['flap_detection_enabled'] = '1';
                  $a_services[$i]['failure_prediction_enabled'] = '1';
                  $a_services[$i]['retain_status_information'] = '1';
                  $a_services[$i]['retain_nonstatus_information'] = '1';
                  $a_services[$i]['is_volatile'] = '0';
                  // $a_services[$i]['_httpstink'] = 'NO';
               } else {
                  // Notification options
                  $a_services[$i]['notification_interval'] = '30';
                  $pmComponentscatalog->getFromDB($plugin_monitoring_componentscatalogs_id);
                  if ($pmComponentscatalog->fields['notification_interval'] != '30') {
                     $a_services[$i]['notification_interval'] = $pmComponentscatalog->fields['notification_interval'];
                  }
                  $a_services[$i]['notification_period'] = '24x7';
                  $a_services[$i]['check_period'] = '24x7';
                  $timeperiodsuffix = '-'.$pmHostconfig->getValueAncestor('jetlag', $entities_id);
                  if ($timeperiodsuffix == '-0') {
                     $timeperiodsuffix = '';
                  }
                  if ($calendar->getFromDB($a_component['calendars_id'])) {
                     $a_services[$i]['check_period'] = $calendar->fields['name'].$timeperiodsuffix;
                  }
               }

               // WebUI user interface ...
               if (isset(self::$shinkenParameters['webui']['serviceIcons']['name'])) {
                  $a_services[$i][self::$shinkenParameters['webui']['serviceIcons']['name']] =
                     self::$shinkenParameters['webui']['serviceIcons']['value'];
               }

               if ($notadd == '1') {
                  unset($a_services[$i]);
                  $input = array();
                  $input['id'] = $data['id'];
                  $input['event'] = $notadddescription;
                  $input['state'] = "CRITICAL";
                  $input['state_type'] = "HARD";
                  $pmService->update($input);
               } else {
                  $i++;
               }
            }
         // }
      }

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "End generateServicesCfg services\n"
      );

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "Starting generateServicesCfg business rules ...\n"
      );

      // --------------------------------------------------
      // Business rules services ...
      $pmService = new PluginMonitoringService();
      $pmServicescatalog = new PluginMonitoringServicescatalog();
      $pmBusinessrulegroup = new PluginMonitoringBusinessrulegroup();
      $pmBusinessrule = new PluginMonitoringBusinessrule();
      $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
      $pmBusinessrule_component = new PluginMonitoringBusinessrule_component();
      // Prepare individual contacts
      $a_contacts_entities = array();
      $a_list_contact = $pmContact_Item->find("`itemtype`='PluginMonitoringServicescatalog'
         AND `users_id`>0");
      foreach ($a_list_contact as $data) {
         $contactentities = getSonsOf('glpi_entities', $data['entities_id']);
         if (isset($a_contacts_entities[$data['items_id']][$data['users_id']])) {
            $contactentities = array_merge($contactentities, $a_contacts_entities[$data['items_id']][$data['users_id']]);
         }
         $a_contacts_entities[$data['items_id']][$data['users_id']] = $contactentities;
      }
      // Prepare groups contacts
      $group = new Group();
      $a_list_contact = $pmContact_Item->find("`itemtype`='PluginMonitoringServicescatalog'
         AND `groups_id`>0");
      foreach ($a_list_contact as $data) {
         $group->getFromDB($data['groups_id']);
         if ($group->fields['is_recursive'] == 1) {
            $contactentities = getSonsOf('glpi_entities', $group->fields['entities_id']);
         } else {
            $contactentities = array($group->fields['entities_id'] => $group->fields['entities_id']);
         }
         $queryg = "SELECT * FROM `glpi_groups_users`
            WHERE `groups_id`='".$data['groups_id']."'";
         $resultg = $DB->query($queryg);
         while ($datag=$DB->fetch_array($resultg)) {
            if (isset($a_contacts_entities[$data['items_id']][$datag['users_id']])) {
               $contactentities = array_merge($contactentities, $a_contacts_entities[$data['items_id']][$datag['users_id']]);
            }
            $a_contacts_entities[$data['items_id']][$datag['users_id']] = $contactentities;
         }
      }

      // Services catalogs
      $a_listBA = $pmServicescatalog->find("`is_generic`='0'");
      foreach ($a_listBA as $dataBA) {

         if (isset($a_entities_allowed['-1'])
                 OR isset($a_entities_allowed[$dataBA['entities_id']])) {

            $a_grouplist = $pmBusinessrulegroup->find("`plugin_monitoring_servicescatalogs_id`='".$dataBA['id']."'");
            $a_group = array();
            foreach ($a_grouplist as $gdata) {

               $pmBusinessrule_component->replayDynamicServices($gdata['id']);
               $a_listBR = $pmBusinessrule->find(
                       "`plugin_monitoring_businessrulegroups_id`='".$gdata['id']."'");
               foreach ($a_listBR as $dataBR) {
                  if ($pmService->getFromDB($dataBR['plugin_monitoring_services_id'])) {
                     if ($pmService->getHostName() != '') {
                        $hostname = self::shinkenFilter($pmService->getHostName());

                        if ($gdata['operator'] == 'and'
                                OR $gdata['operator'] == 'or'
                                OR strstr($gdata['operator'], ' of:')) {

                           $operator = '|';
                           if ($gdata['operator'] == 'and') {
                              $operator = '&';
                           }
                           if (!isset($a_group[$gdata['id']])) {
                              $a_group[$gdata['id']] = '';
                              if (strstr($gdata['operator'], ' of:')) {
                                 $a_group[$gdata['id']] = $gdata['operator'];
                              }
                              $a_group[$gdata['id']] .= $hostname.",".self::shinkenFilter($pmService->getName(array('shinken'=>true)));
                           } else {
                              $a_group[$gdata['id']] .= $operator.$hostname.",".self::shinkenFilter($pmService->getName(array('shinken'=>true)));
                           }
                        } else {
                           $a_group[$gdata['id']] = $gdata['operator']." ".$hostname.",".self::shinkenFilter($item->getName());
                        }
                     }
                  }
                  PluginMonitoringToolbox::logIfExtradebug(
                     'pm-shinken',
                     "   - SC group : ".$a_group[$gdata['id']]."\n"
                  );
               }
            }
            if (count($a_group) > 0) {
               $pMonitoringCheck->getFromDB($dataBA['plugin_monitoring_checks_id']);
               $a_services[$i]['check_interval'] = $pMonitoringCheck->fields['check_interval'];
               $a_services[$i]['retry_interval'] = $pMonitoringCheck->fields['retry_interval'];
               $a_services[$i]['max_check_attempts'] = $pMonitoringCheck->fields['max_check_attempts'];
               if ($calendar->getFromDB($dataBA['calendars_id'])) {
                  $a_services[$i]['check_period'] = $calendar->fields['name'];
               }
               $a_services[$i]['host_name'] = self::$shinkenParameters['shinken']['fake_hosts']['name_prefix'] . self::$shinkenParameters['shinken']['fake_hosts']['bp_host'];
               $a_services[$i]['business_impact'] = $dataBA['business_priority'];
               $a_services[$i]['service_description'] = self::shinkenFilter($dataBA['name']);
               $a_services[$i]['_ENTITIESID'] = $dataBA['id'];
               $a_services[$i]['_ITEMSID'] = $dataBA['id'];
               $a_services[$i]['_ITEMTYPE'] = 'ServiceCatalog';
               $command = "bp_rule!";

               foreach ($a_group as $key=>$value) {
                  if (!strstr($value, "&")
                          AND !strstr($value, "|")) {
                     $a_group[$key] = trim($value);
                  } else {
                     $a_group[$key] = "(".trim($value).")";
                  }
               }
               $a_services[$i]['check_command'] = $command.implode("&", $a_group);
               if ($dataBA['notification_interval'] != '30') {
                  $a_services[$i]['notification_interval'] = $dataBA['notification_interval'];
               } else {
                  $a_services[$i]['notification_interval'] = '30';
               }
               $a_services[$i]['notification_period'] = "24x7";
               $a_services[$i]['notification_options'] = 'w,u,c,r,f,s';
               $a_services[$i]['active_checks_enabled'] = '1';
               $a_services[$i]['process_perf_data'] = '1';
               $a_services[$i]['active_checks_enabled'] = '1';
               $a_services[$i]['passive_checks_enabled'] = '1';
               $a_services[$i]['parallelize_check'] = '1';
               $a_services[$i]['obsess_over_service'] = '1';
               $a_services[$i]['check_freshness'] = '1';
               $a_services[$i]['freshness_threshold'] = '3600';
               $a_services[$i]['notifications_enabled'] = '1';
               $a_services[$i]['event_handler_enabled'] = '0';
               //$a_services[$i]['event_handler'] = 'super_event_kill_everyone!DIE';
               $a_services[$i]['flap_detection_enabled'] = '1';
               $a_services[$i]['failure_prediction_enabled'] = '1';
               $a_services[$i]['retain_status_information'] = '1';
               $a_services[$i]['retain_nonstatus_information'] = '1';
               $a_services[$i]['is_volatile'] = '0';
               // $a_services[$i]['_httpstink'] = 'NO';

               // * Contacts
               $a_contacts = array();
               $a_list_contact = $pmContact_Item->find("`itemtype`='PluginMonitoringServicescatalog'
                  AND `items_id`='".$dataBA['id']."'");
               foreach ($a_list_contact as $data_contact) {
                  if ($data_contact['users_id'] > 0) {
                     if (isset($a_contacts_entities[$dataBA['id']][$data_contact['users_id']])) {
                        if (in_array($data['entities_id'], $a_contacts_entities[$dataBA['id']][$data_contact['users_id']])) {
                           $user->getFromDB($data_contact['users_id']);
                           $a_contacts[] = $user->fields['name'];
                        }
                     }
                  } else if ($data_contact['groups_id'] > 0) {
                     $queryg = "SELECT * FROM `glpi_groups_users`
                        WHERE `groups_id`='".$data_contact['groups_id']."'";
                     $resultg = $DB->query($queryg);
                     while ($datag=$DB->fetch_array($resultg)) {
                        if (in_array($data['entities_id'], $a_contacts_entities[$dataBA['id']][$datag['users_id']])) {
                           $user->getFromDB($datag['users_id']);
                           $a_contacts[] = $user->fields['name'];
                        }
                     }
                  }
               }

               $a_contacts_unique = array_unique($a_contacts);
               $a_services[$i]['contacts'] = implode(',', $a_contacts_unique);
               $i++;
            }
         }
      }

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "End generateServicesCfg business rules\n"
      );

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "Starting generateServicesCfg business rules templates ...\n"
      );

      // Services catalogs templates
      // TODO : correctly test and improve it !
      $a_listBA = $pmServicescatalog->find("`is_generic`='1'");
      foreach ($a_listBA as $dataBA) {
         PluginMonitoringToolbox::logIfExtradebug(
            'pm-shinken',
            "   - SC : ".$dataBA['id']."\n"
         );

         if (isset($a_entities_allowed['-1'])
                 OR isset($a_entities_allowed[$dataBA['entities_id']])) {

            $pmServicescatalog->getFromDB($dataBA['id']);

            $a_entitiesServices = $pmServicescatalog->getGenericServicesEntities();
            foreach ($a_entitiesServices as $idEntity=>$a_entityServices) {
               // New entity ... so new business rule !
               PluginMonitoringToolbox::logIfExtradebug(
                  'pm-shinken',
                  "   - SC templated services for an entity : ".$idEntity."\n"
               );

               $pmDerivatedSC = new PluginMonitoringServicescatalog();
               $a_derivatedSC = $pmDerivatedSC->find("`entities_id`='$idEntity' AND `name` LIKE '".$dataBA['name']."%'");
               foreach ($a_derivatedSC as $a_derivated) {
                  PluginMonitoringToolbox::logIfExtradebug(
                     'pm-shinken',
                     "   - a_derivated : ".$a_derivated['name']."\n"
                  );
                  $a_derivatedSC = $a_derivated;
               }

               $a_group = array();
               foreach ($a_entityServices as $services) {
                  if ($pmService->getFromDB($services['serviceId'])) {
                     // Toolbox::logInFile("pm-shinken", "   - SC templated service entity : ".$services['entityId'].", service :  ".$pmService->getName(true)." on ".$pmService->getHostName()."\n");
                     if ($pmService->getHostName() != '') {
                        $hostname = self::shinkenFilter($pmService->getHostName());

                        $serviceFakeId = $services['entityId'];

                        $pmBusinessrulegroup->getFromDB($services['BRgroupId']);
                        $BRoperator = $pmBusinessrulegroup->getField('operator');
                        if ($BRoperator == 'and'
                                OR $BRoperator == 'or'
                                OR strstr($BRoperator, ' of:')) {

                           $operator = '|';
                           if ($BRoperator == 'and') {
                              $operator = '&';
                           }
                           if (!isset($a_group[$serviceFakeId])) {
                              $a_group[$serviceFakeId] = '';
                              if (strstr($BRoperator, ' of:')) {
                                 $a_group[$serviceFakeId] = $BRoperator;
                              }
                              $a_group[$serviceFakeId] .= $hostname.",".self::shinkenFilter($pmService->getName(array('shinken'=>true)));
                           } else {
                              $a_group[$serviceFakeId] .= $operator.$hostname.",".self::shinkenFilter($pmService->getName(array('shinken'=>true)));
                           }
                        } else {
                           $a_group[$serviceFakeId] = $BRoperator." ".$hostname.",".self::shinkenFilter($pmService->getHostName());
                        }
                        // Toolbox::logInFile("pm-shinken", "   - SCT group : ".$a_group[$serviceFakeId]."\n");
                     }
                  }
               }
               if (count($a_group) > 0) {
                  $pMonitoringCheck->getFromDB($a_derivatedSC['plugin_monitoring_checks_id']);
                  $a_services[$i]['check_interval'] = $pMonitoringCheck->fields['check_interval'];
                  $a_services[$i]['retry_interval'] = $pMonitoringCheck->fields['retry_interval'];
                  $a_services[$i]['max_check_attempts'] = $pMonitoringCheck->fields['max_check_attempts'];
                  if ($calendar->getFromDB($a_derivatedSC['calendars_id'])) {
                     $a_services[$i]['check_period'] = $calendar->fields['name'];
                  }
                  $a_services[$i]['host_name'] = self::shinkenFilter($a_derivatedSC['name']);
                  $a_services[$i]['host_name'] = self::$shinkenParameters['shinken']['fake_hosts']['name_prefix'] . self::$shinkenParameters['shinken']['fake_hosts']['bp_host'];
                  $a_services[$i]['business_impact'] = $a_derivatedSC['business_priority'];
                  $a_services[$i]['service_description'] = self::shinkenFilter($a_derivatedSC['name']);
                  $a_services[$i]['_ENTITIESID'] = $a_derivatedSC['entities_id'];
                  $a_services[$i]['_ITEMSID'] = $a_derivatedSC['id'];
                  $a_services[$i]['_ITEMTYPE'] = 'ServiceCatalog';
                  $command = "bp_rule!";

                  foreach ($a_group as $key=>$value) {
                     if (!strstr($value, "&")
                             AND !strstr($value, "|")) {
                        $a_group[$key] = trim($value);
                     } else {
                        $a_group[$key] = "(".trim($value).")";
                     }
                  }
                  $a_services[$i]['check_command'] = $command.implode("&", $a_group);
                  if ($a_derivatedSC['notification_interval'] != '30') {
                     $a_services[$i]['notification_interval'] = $a_derivatedSC['notification_interval'];
                  } else {
                     $a_services[$i]['notification_interval'] = '30';
                  }
                  $a_services[$i]['notification_period'] = "24x7";
                  $a_services[$i]['notification_options'] = 'w,u,c,r,f,s';
                  $a_services[$i]['active_checks_enabled'] = '1';
                  $a_services[$i]['process_perf_data'] = '1';
                  $a_services[$i]['active_checks_enabled'] = '1';
                  $a_services[$i]['passive_checks_enabled'] = '1';
                  $a_services[$i]['parallelize_check'] = '1';
                  $a_services[$i]['obsess_over_service'] = '1';
                  $a_services[$i]['check_freshness'] = '1';
                  $a_services[$i]['freshness_threshold'] = '3600';
                  $a_services[$i]['notifications_enabled'] = '1';
                  $a_services[$i]['event_handler_enabled'] = '0';
                  //$a_services[$i]['event_handler'] = 'super_event_kill_everyone!DIE';
                  $a_services[$i]['flap_detection_enabled'] = '1';
                  $a_services[$i]['failure_prediction_enabled'] = '1';
                  $a_services[$i]['retain_status_information'] = '1';
                  $a_services[$i]['retain_nonstatus_information'] = '1';
                  $a_services[$i]['is_volatile'] = '0';
                  // $a_services[$i]['_httpstink'] = 'NO';

                  // * Contacts
                  $a_contacts = array();
                  $a_list_contact = $pmContact_Item->find("`itemtype`='PluginMonitoringServicescatalog'
                     AND `items_id`='".$dataBA['id']."'");
                  foreach ($a_list_contact as $data_contact) {
                     if ($data_contact['users_id'] > 0) {
                        if (isset($a_contacts_entities[$dataBA['id']][$data_contact['users_id']])) {
                           if (in_array($data['entities_id'], $a_contacts_entities[$dataBA['id']][$data_contact['users_id']])) {
                              $user->getFromDB($data_contact['users_id']);
                              $a_contacts[] = $user->fields['name'];
                           }
                        }
                     } else if ($data_contact['groups_id'] > 0) {
                        $queryg = "SELECT * FROM `glpi_groups_users`
                           WHERE `groups_id`='".$data_contact['groups_id']."'";
                        $resultg = $DB->query($queryg);
                        while ($datag=$DB->fetch_array($resultg)) {
                           if (in_array($data['entities_id'], $a_contacts_entities[$dataBA['id']][$datag['users_id']])) {
                              $user->getFromDB($datag['users_id']);
                              $a_contacts[] = $user->fields['name'];
                           }
                        }
                     }
                  }

                  $a_contacts_unique = array_unique($a_contacts);
                  $a_services[$i]['contacts'] = implode(',', $a_contacts_unique);
                  $i++;
               }
            }
         }
      }

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "End generateServicesCfg business rules templates\n"
      );

      if ($file == "1") {
         $config = "# Generated by plugin monitoring for GLPI\n# on ".date("Y-m-d H:i:s")."\n\n";

         foreach ($a_services as $data) {
            $config .= $this->writeFile("service", $data);
         }
         return array('services.cfg', $config);

      } else {
         return $a_services;
      }
   }



   function generateTemplatesCfg($file=0, $tag='') {
      global $DB;

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "Starting generateTemplatesCfg ($tag) ...\n"
      );
      $pMonitoringCheck = new PluginMonitoringCheck();
      $calendar         = new Calendar();

      $a_servicetemplates = array();
      $i=0;
      $a_templatesdef = array();

      $query = "SELECT * FROM `glpi_plugin_monitoring_components`
         GROUP BY `plugin_monitoring_checks_id`, `active_checks_enabled`,
            `passive_checks_enabled`, `calendars_id`
         ORDER BY `id`";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {

         PluginMonitoringToolbox::logIfExtradebug(
            'pm-shinken',
            " - add template ".'template'.$data['id'].'-service'."\n"
         );
         $a_servicetemplates[$i]['name'] = 'template'.$data['id'].'-service';
            $pMonitoringCheck->getFromDB($data['plugin_monitoring_checks_id']);
         $a_servicetemplates[$i]['alias'] = $data['description'].' / '.$data['name'];
         $a_servicetemplates[$i]['check_interval'] = $pMonitoringCheck->fields['check_interval'];
         $a_servicetemplates[$i]['retry_interval'] = $pMonitoringCheck->fields['retry_interval'];
         $a_servicetemplates[$i]['max_check_attempts'] = $pMonitoringCheck->fields['max_check_attempts'];
         if ($calendar->getFromDB($data['calendars_id'])) {
            $a_servicetemplates[$i]['check_period'] = $calendar->fields['name'];
         }
         $a_servicetemplates[$i]['notification_interval'] = '30';
         $a_servicetemplates[$i]['notification_period'] = "24x7";
         $a_servicetemplates[$i]['notification_options'] = 'w,u,c,r,f,s';
         $a_servicetemplates[$i]['process_perf_data'] = '1';
         $a_servicetemplates[$i]['active_checks_enabled'] = $data['active_checks_enabled'];
         $a_servicetemplates[$i]['passive_checks_enabled'] = $data['passive_checks_enabled'];
         $a_servicetemplates[$i]['parallelize_check'] = '1';
         $a_servicetemplates[$i]['obsess_over_service'] = '1';
         $a_servicetemplates[$i]['check_freshness'] = '1';
         $a_servicetemplates[$i]['freshness_threshold'] = '3600';
         $a_servicetemplates[$i]['notifications_enabled'] = '1';
         $a_servicetemplates[$i]['event_handler_enabled'] = '0';
         //$a_servicetemplates[$i]['event_handler'] = 'super_event_kill_everyone!DIE';
         $a_servicetemplates[$i]['flap_detection_enabled'] = '1';
         $a_servicetemplates[$i]['failure_prediction_enabled'] = '1';
         $a_servicetemplates[$i]['retain_status_information'] = '1';
         $a_servicetemplates[$i]['retain_nonstatus_information'] = '1';
         $a_servicetemplates[$i]['is_volatile'] = '0';
/* Fred: Previous line should be commented and this comment should be removed ... but there is a bug in Shinken notifications with volatile services !
         if ($data['passive_checks_enabled'] == '1' && $data['active_checks_enabled'] == '0') {
            $a_servicetemplates[$i]['is_volatile'] = '1';
         } else {
            $a_servicetemplates[$i]['is_volatile'] = '0';
         }
*/
         // $a_servicetemplates[$i]['_httpstink'] = 'NO';
         $a_servicetemplates[$i]['register'] = '0';

         // Manage user interface ...
         $a_servicetemplates[$i]['icon_set'] = 'service';

         $queryc = "SELECT * FROM `glpi_plugin_monitoring_components`
            WHERE `plugin_monitoring_checks_id`='".$data['plugin_monitoring_checks_id']."'
               AND `active_checks_enabled`='".$data['active_checks_enabled']."'
               AND `passive_checks_enabled`='".$data['passive_checks_enabled']."'
               AND `calendars_id`='".$data['calendars_id']."'";
         $resultc = $DB->query($queryc);
         while ($datac=$DB->fetch_array($resultc)) {
            $a_templatesdef[$datac['id']] = $a_servicetemplates[$i]['name'];
         }
         $i++;
      }
      $_SESSION['plugin_monitoring']['servicetemplates'] = $a_templatesdef;

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "End generateTemplatesCfg\n"
      );

      if ($file == "1") {
         $config = "# Generated by plugin monitoring for GLPI\n# on ".date("Y-m-d H:i:s")."\n\n";

         foreach ($a_servicetemplates as $data) {
            $config .= $this->writeFile("service", $data);
         }
         return array('servicetemplates.cfg', $config);

      } else {
         return $a_servicetemplates;
      }
   }



   function generateHostgroupsCfg($file=0, $tag='') {
      global $DB;

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "Starting generateHostgroupsCfg ($tag) ...\n"
      );
      $pmEntity      = new PluginMonitoringEntity();

      $a_hostgroups = array();
      $i=0;

      $a_entities_allowed = $pmEntity->getEntitiesByTag($tag);
      $a_entities_list = array();
      foreach ($a_entities_allowed as $entity) {
         $a_entities_list = getSonsOf("glpi_entities", $entity);
      }
      $where = '';
      if (! isset($a_entities_allowed['-1'])) {
         $where = getEntitiesRestrictRequest("WHERE", "glpi_entities", '', $a_entities_list);
      }

      $query = "SELECT
         `glpi_entities`.`id` AS entityId, `glpi_entities`.`name` AS entityName, `glpi_entities`.`level` AS entityLevel
         FROM `glpi_entities` $where";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
/*
Nagios configuration file :
   define hostgroup{
      hostgroup_name	hostgroup_name
      alias	alias
      members	hosts
      hostgroup_members	hostgroups
      notes	note_string
      notes_url	url
      action_url	url
   }
*/
         // Hostgroup name
         $hostgroup_name = strtolower(self::shinkenFilter($data['entityName']));
         $hostgroup_name = preg_replace("/[ ]/","_",$hostgroup_name);

         PluginMonitoringToolbox::logIfExtradebug(
            'pm-shinken',
            " - add group $hostgroup_name ...\n"
         );

         $a_hostgroups[$i]['hostgroup_name'] = $hostgroup_name;
         $a_hostgroups[$i]['alias'] = $data['entityName'];

         // Custom variable are ignored for hostgroups ... simple information for debug purpose !
         $a_hostgroups[$i]['_GROUP_LEVEL'] = $data['entityLevel'];

         $a_sons_list = getSonsOf("glpi_entities", $data['entityId']);
         if (count($a_sons_list) > 1) {
            $a_hostgroups[$i]['hostgroup_members'] = '';
            $first_member = true;
            foreach ($a_sons_list as $son_entity) {
               if ($son_entity == $data['entityId']) continue;
               if (! in_array ($son_entity, $a_entities_list)) continue;

               $pmEntity = new Entity();
               $pmEntity->getFromDB($son_entity);
			   // Only immediate sub level are considered as hostgroup members
               if ($data['entityLevel']+1 != $pmEntity->fields['level']) continue;

               $hostgroup_name = self::shinkenFilter($pmEntity->getField('name'));
               $hostgroup_name = preg_replace("/[ ]/","_",$hostgroup_name);

               $a_hostgroups[$i]['hostgroup_members'] .= (! $first_member) ? ", $hostgroup_name" : "$hostgroup_name";
               if ($first_member) $first_member = false;
            }
         }

         $i++;
      }

      // Add an hostgroup for fake hosts
      if (self::$shinkenParameters['shinken']['fake_hosts']['build']) {
         $a_hostgroups[$i]['hostgroup_name'] = self::$shinkenParameters['shinken']['fake_hosts']['hostgroup_name'];
         $a_hostgroups[$i]['alias'] = self::$shinkenParameters['shinken']['fake_hosts']['hostgroup_alias'];
      }

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "End generateHostgroupsCfg\n"
      );

      if ($file == "1") {
         $config = "# Generated by plugin monitoring for GLPI\n# on ".date("Y-m-d H:i:s")."\n\n";

         foreach ($a_hostgroups as $data) {
            $config .= $this->writeFile("hostgroup", $data);
         }
         return array('hostgroups.cfg', $config);

      } else {
         return $a_hostgroups;
      }
   }



   function generateContactsCfg($file=0, $tag='') {
      global $DB;

      $pmEntity      = new PluginMonitoringEntity();
      $calendar      = new Calendar();

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "Starting generateContactsCfg ($tag) ...\n"
      );

      $a_users_used = array();
      $a_contacts = array();
      // Add default contact 'monitoring' for fake hosts
//      $a_calendars = current($calendar->find("", "", 1));
//      $cal = '24x7';
//      if (isset($a_calendars['name'])) {
//         $cal = $a_calendars['name'];
//      }
//      $a_contacts[-1] = array(
//          'contact_name'                   => 'monitoring',
//          'alias'                          => 'monitoring',
//          'host_notifications_enabled'     => '0',
//          'service_notifications_enabled'  => '0',
//          'service_notification_period'    => $cal,
//          'host_notification_period'       => $cal,
//          'service_notification_options' => '',
//          'host_notification_options'    => '',
//          'service_notification_commands'  => '',
//          'host_notification_commands'     => '',
//          'email'                          => '',
//          'pager'                          => '',
//      );


      $a_entities_allowed = $pmEntity->getEntitiesByTag($tag);
      $a_entities_list = array();
      foreach ($a_entities_allowed as $entity) {
         $a_entities_list = getSonsOf("glpi_entities", $entity);
      }
	  // Always add root entity
      $a_entities_list[] = '0';
      $where = '';
      if (! isset($a_entities_allowed['-1'])) {
         $where = getEntitiesRestrictRequest("WHERE", "glpi_plugin_monitoring_contacts_items", '', $a_entities_list);
      }


      $i=0;

      $query = "SELECT * FROM `glpi_plugin_monitoring_contacts_items` $where";
      // Toolbox::logInFile("pm-shinken", "- Contacts query: $query\n");
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         if ($data['users_id'] > 0) {
            if ((!isset($a_users_used[$data['users_id']]))) {
               $a_contacts = $this->_addContactUser($a_contacts, $data['users_id'], $i);
               $i++;
               $a_users_used[$data['users_id']] = 1;
            }
         } else if ($data['groups_id'] > 0) {
            $queryg = "SELECT * FROM `glpi_groups_users`
               WHERE `groups_id`='".$data['groups_id']."'";
            $resultg = $DB->query($queryg);
            while ($datag=$DB->fetch_array($resultg)) {
               if ((!isset($a_users_used[$datag['users_id']]))) {
                  $a_contacts = $this->_addContactUser($a_contacts, $datag['users_id'], $i);
                  $i++;
                  $a_users_used[$datag['users_id']] = 1;
               }
            }
         }
      }
      // Add user monitoring
      $user = new User();
      $a_monit_user = current($user->find("`name`='monitoring'", '', 1));
      if ((!isset($a_users_used[$a_monit_user['id']]))) {
         $a_contacts = $this->_addContactUser($a_contacts, $a_monit_user['id'], $i);
      }

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "End generateContactsCfg\n"
      );

      if ($file == "1") {
         $config = "# Generated by plugin monitoring for GLPI\n# on ".date("Y-m-d H:i:s")."\n\n";

         foreach ($a_contacts as $data) {
            $config .= $this->writeFile("contact", $data);
         }
         return array('contacts.cfg', $config);

      } else {
         return $a_contacts;
      }
   }



   function _addContactUser($a_contacts, $users_id, $i) {

      $pmContact              = new PluginMonitoringContact();
      $pmNotificationcommand  = new PluginMonitoringNotificationcommand();
      $pmContacttemplate      = new PluginMonitoringContacttemplate();
      $user                   = new User();
      $calendar               = new Calendar();

      $user->getFromDB($users_id);

      // Get contact template
      $a_pmcontact = current($pmContact->find("`users_id`='".$users_id."'", "", 1));
      if (empty($a_pmcontact) OR
              (isset($a_pmcontact['plugin_monitoring_contacttemplates_id'])
              AND $a_pmcontact['plugin_monitoring_contacttemplates_id'] == '0')) {
         // Use default template
         $a_pmcontact = current($pmContacttemplate->find("`is_default`='1'", "", 1));
      } else {
         // Use contact defined template
         $a_pmcontact = current($pmContacttemplate->find("`id`='".$a_pmcontact['plugin_monitoring_contacttemplates_id']."'", "", 1));
      }
      $a_contacts[$i]['contact_name'] = $user->fields['name'];
      $a_contacts[$i]['alias'] = $user->getName();
      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "- contact ".$user->fields['name']." - ".$user->getName()."\n"
      );
      // Toolbox::logInFile("pm-contacts", "- contact ".serialize($user->fields)."\n");

      if (!isset($a_pmcontact['host_notification_period'])) {
         $a_calendars = current($calendar->find("", "", 1));
         $cal = '24x7';
         if (isset($a_calendars['name'])) {
            $cal = $a_calendars['name'];
         }
         $a_pmcontact['host_notifications_enabled'] = '0';
         $a_pmcontact['service_notifications_enabled'] = '0';
         $a_pmcontact['service_notification_period'] = $cal;
         $a_pmcontact['host_notification_period'] = $cal;
         $a_pmcontact['service_notification_options_w'] = '0';
         $a_pmcontact['service_notification_options_u'] = '0';
         $a_pmcontact['service_notification_options_c'] = '0';
         $a_pmcontact['service_notification_options_r'] = '0';
         $a_pmcontact['service_notification_options_f'] = '0';
         $a_pmcontact['service_notification_options_n'] = '0';
         $a_pmcontact['host_notification_options_d'] = '0';
         $a_pmcontact['host_notification_options_u'] = '0';
         $a_pmcontact['host_notification_options_r'] = '0';
         $a_pmcontact['host_notification_options_f'] = '0';
         $a_pmcontact['host_notification_options_s'] = '0';
         $a_pmcontact['host_notification_options_n'] = '0';
         $a_pmcontact['service_notification_commands'] = '2';
         $a_pmcontact['host_notification_commands'] = '1';
      }
      $a_contacts[$i]['host_notifications_enabled'] = $a_pmcontact['host_notifications_enabled'];
      $a_contacts[$i]['service_notifications_enabled'] = $a_pmcontact['service_notifications_enabled'];

      $calendar->getFromDB($a_pmcontact['service_notification_period']);
      if (isset($calendar->fields['name'])) {
         $a_contacts[$i]['service_notification_period'] = $calendar->fields['name'];
      } else {
         $a_contacts[$i]['service_notification_period'] = '24x7';
      }

      $calendar->getFromDB($a_pmcontact['host_notification_period']);
      if (isset($calendar->fields['name'])) {
         $a_contacts[$i]['host_notification_period'] = $calendar->fields['name'];
      } else {
         $a_contacts[$i]['host_notification_period'] = '24x7';
      }

      $a_servicenotif = array();
      if ($a_pmcontact['service_notification_options_w'] == '1')
         $a_servicenotif[] = "w";
      if ($a_pmcontact['service_notification_options_u'] == '1')
         $a_servicenotif[] = "u";
      if ($a_pmcontact['service_notification_options_c'] == '1')
         $a_servicenotif[] = "c";
      if ($a_pmcontact['service_notification_options_r'] == '1')
         $a_servicenotif[] = "r";
      if ($a_pmcontact['service_notification_options_f'] == '1')
         $a_servicenotif[] = "f";
      if ($a_pmcontact['service_notification_options_n'] == '1')
         $a_servicenotif = array("n");
      if (count($a_servicenotif) == "0")
         $a_servicenotif = array("n");
      $a_contacts[$i]['service_notification_options'] = implode(",", $a_servicenotif);

      $a_hostnotif = array();
      if ($a_pmcontact['host_notification_options_d'] == '1')
         $a_hostnotif[] = "d";
      if ($a_pmcontact['host_notification_options_u'] == '1')
         $a_hostnotif[] = "u";
      if ($a_pmcontact['host_notification_options_r'] == '1')
         $a_hostnotif[] = "r";
      if ($a_pmcontact['host_notification_options_f'] == '1')
         $a_hostnotif[] = "f";
      if ($a_pmcontact['host_notification_options_s'] == '1')
         $a_hostnotif[] = "s";
      if ($a_pmcontact['host_notification_options_n'] == '1')
         $a_hostnotif = array("n");
      if (count($a_hostnotif) == "0")
         $a_hostnotif = array("n");
      $a_contacts[$i]['host_notification_options'] = implode(",", $a_hostnotif);

      $pmNotificationcommand->getFromDB($a_pmcontact['service_notification_commands']);
      if (isset($pmNotificationcommand->fields['command_name'])) {
         $a_contacts[$i]['service_notification_commands'] = PluginMonitoringCommand::$command_prefix . $pmNotificationcommand->fields['command_name'];
      } else {
         $a_contacts[$i]['service_notification_commands'] = '';
      }
      $pmNotificationcommand->getFromDB($a_pmcontact['host_notification_commands']);
      if (isset($pmNotificationcommand->fields['command_name'])) {
         $a_contacts[$i]['host_notification_commands'] = PluginMonitoringCommand::$command_prefix . $pmNotificationcommand->fields['command_name'];
      } else {
         $a_contacts[$i]['host_notification_commands'] = '';
      }

      // Get first email
      $a_emails = UserEmail::getAllForUser($users_id);
      $first = 0;
      foreach ($a_emails as $email) {
         if ($first == 0) {
            $a_contacts[$i]['email'] = $email;
         }
         $first++;
      }
      if (!isset($a_contacts[$i]['email'])) {
         $a_contacts[$i]['email'] = '';
      }
      $a_contacts[$i]['pager'] = $user->fields['phone'];

      if (isset($a_pmcontact['shinken_administrator'])) {
         $a_contacts[$i]['is_admin'] = $a_pmcontact['shinken_administrator'];
      } else {
         $a_contacts[$i]['is_admin'] = self::$shinkenParameters['webui']['contacts']['is_admin'];
      }
      if (isset($a_pmcontact['shinken_can_submit_commands'])) {
         $a_contacts[$i]['can_submit_commands'] = $a_pmcontact['shinken_can_submit_commands'];
      } else {
         $a_contacts[$i]['can_submit_commands'] = self::$shinkenParameters['webui']['contacts']['can_submit_commands'];
      }
      if (empty($user->fields['password'])) {
         $a_contacts[$i]['password'] = self::$shinkenParameters['webui']['contacts']['password'];
      } else {
         $a_contacts[$i]['password'] = $user->fields['password'];
      }

      /*
      TODO:
      address1, address2, ..., address6 are available in Shinken
      */

      return $a_contacts;
   }



   function generateTimeperiodsCfg($file=0, $tag='') {
      global $DB;

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "Starting generateTimeperiodsCfg ...\n"
      );
      $calendar         = new Calendar();
      $calendarSegment  = new CalendarSegment();
      $calendar_Holiday = new Calendar_Holiday();
      $holiday          = new Holiday();
      $hostconfig       = new PluginMonitoringHostconfig();
      $pmEntity         = new PluginMonitoringEntity();

      // Get list of entities
      $a_entities_allowed = $pmEntity->getEntitiesByTag($tag);
      $a_entities_list = array();
      foreach ($a_entities_allowed as $entity) {
         $a_entities_list = getSonsOf("glpi_entities", $entity);
      }
      $where = '';
      if (!isset($a_entities_allowed['-1'])) {
         $where = getEntitiesRestrictRequest("WHERE", "glpi_entities", '', $a_entities_list);
      }

      foreach ($a_entities_list as $entities_id) {
         $jetlag = $hostconfig->getValueAncestor('jetlag', $entities_id);
         $a_entities_list[$entities_id] = $jetlag;
      }

      // Get all calendars of components
      $cals_entities = array();
      $query = "SELECT `entities_id`, `calendars_id`
         FROM `glpi_plugin_monitoring_services`
         LEFT JOIN `glpi_plugin_monitoring_components`
            ON `plugin_monitoring_components_id` = `glpi_plugin_monitoring_components`.`id` ";
      if ($where == '') {
         $query .= "WHERE `glpi_plugin_monitoring_components`.`id` IS NOT NULL";
      } else {
         $query .= $where." AND `glpi_plugin_monitoring_components`.`id` IS NOT NULL";
      }
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         if (!isset($cals_entities[$data['entities_id']])) {
            $cals_entities[$data['entities_id']] = array();
         }
         $cals_entities[$data['entities_id']][$data['calendars_id']] = $data['calendars_id'];
      }
      // get all calendars of servciescatalog
      $query = "SELECT `entities_id`, `calendars_id`
         FROM `glpi_plugin_monitoring_servicescatalogs` ".$where;
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         if (!isset($cals_entities[$data['entities_id']])) {
            $cals_entities[$data['entities_id']] = array();
         }
         $cals_entities[$data['entities_id']][$data['calendars_id']] = $data['calendars_id'];
      }
      // Get all calendars of contacts TODO

      $calendars_added = array();
      $a_timeperiods = array();
      $i=0;
      foreach ($cals_entities as $entities_id => $calendars) {
         foreach ($calendars as $calendars_id) {
            $jetlag = $a_entities_list[$entities_id];
            if (!isset($calendars_added[$calendars_id."-".$jetlag])) {
               $calendars_added[$calendars_id."-".$jetlag] = 1;

               $calendar->getFromDB($calendars_id);
               $tmp = array();
               $tmp['timeperiod_name'] = $calendar->fields['name'];
               $tmp['alias'] = $calendar->fields['name'];
               $a_listsegment = $calendarSegment->find("`calendars_id`='".$calendar->fields['id']."'");
               $a_cal = array();
               foreach ($a_listsegment as $datasegment) {
                  $begin = preg_replace("/:00$/", "", $datasegment['begin']);
                  $end = preg_replace("/:00$/", "", $datasegment['end']);
                  $day = "";
                  switch ($datasegment['day']) {

                     case "0":
                        $day = "sunday";
                        break;

                     case "1":
                        $day = "monday";
                        break;

                     case "2":
                        $day = "tuesday";
                        break;

                     case "3":
                        $day = "wednesday";
                        break;

                     case "4":
                        $day = "thursday";
                        break;

                     case "5":
                        $day = "friday";
                        break;

                     case "6":
                        $day = "saturday";
                        break;

                  }
                  $a_cal[$day][] = $begin."-".$end;
               }
               foreach ($a_cal as $day=>$a_times) {
                  $tmp[$day] = implode(',', $a_times);
               }
               $a_cholidays = $calendar_Holiday->find("`calendars_id`='".$calendar->fields['id']."'");
               foreach ($a_cholidays as $a_choliday) {
                  $holiday->getFromDB($a_choliday['holidays_id']);
                  if ($holiday->fields['is_perpetual'] == 1
                          && $holiday->fields['begin_date'] == $holiday->fields['end_date']) {
                     $datetime = strtotime($holiday->fields['begin_date']);
                     $tmp[strtolower(date('F', $datetime)).
                         ' '.date('j', $datetime)] = '00:00-00:00';
                  }
               }


               if ($jetlag == 0) {
                  $tmp['timeperiod_name'] = $tmp['timeperiod_name'];
                  $tmp['alias'] = $tmp['alias'];
               } else {
                  $tmp['timeperiod_name'] = $tmp['timeperiod_name']."-".$jetlag;
                  $tmp['alias'] = $tmp['alias']."-".$jetlag;
               }
               $days = array('sunday','monday','tuesday', 'wednesday','thursday',
                             'friday', 'saturday');
               $saturday = '';
               $reportHours = 0;
               $beforeday = 'saturday';
               foreach ($days as $numday=>$day) {
                  if (isset($tmp[$day])) {
                     $splitDay = explode(',', $tmp[$day]);
                     $toAdd = '';
                     if ($reportHours > 0) {
                        $toAdd = '00:00-'.sprintf("%02s", $reportHours).':00';
                        $reportHours = 0;
                     }
                     foreach ($splitDay as $num=>$hourMinute) {
                        $previous_begin = 0;
                        $beginEnd = explode('-', $hourMinute);
                        // ** Begin **
                        $split = explode(':', $beginEnd[0]);
                        $split[0] += $jetlag;
                        if ($split[0] > 24) {
                           //$reportHours = $split[0] - 24;
                           unset($splitDay[$num]);
                        } else {
                           if ($split[0] < 0) {
                              $reportHours = $split[0];
                              $previous_begin = 24 + $split[0];
                              $split[0] = '00';
                           }
                           $beginEnd[0] = sprintf("%02s", $split[0]).':'.$split[1];
                           // ** End **
                           $split = explode(':', $beginEnd[1]);
                           $split[0] += $jetlag;
                           if ($split[0] < 0) {
                              if ($numday-1 == -1) {
                                 $saturday .= ",".sprintf("%02s", $previous_begin).":00-".sprintf("%02s", (24 + $split[0])).":00";
                              } else {
                                 $tmp[$days[($numday-1)]] .= ",".sprintf("%02s", $previous_begin).":00-".sprintf("%02s", (24 + $split[0])).":00";
                              }
                              unset($splitDay[$num]);
                           } else {
                              if ($split[0] > 24) {
                                 $reportHours = $split[0] - 24;
                                 $split[0] = 24;
                              }
                              $beginEnd[1] = sprintf("%02s", $split[0]).':'.$split[1];

                              $hourMinute = implode('-', $beginEnd);
                              $splitDay[$num] = $hourMinute;
                           }
                        }
                     }
                     if ($reportHours < 0) {
   //                     if (!isset($tmp[$beforeday])) {
   //                        $tmp[$beforeday] = array();
   //                     }
   //                     $splitBeforeDay = explode(',', $tmp[$beforeday]);
   //                     $splitBeforeDay[] = sprintf("%02s", (24 + $reportHours)).':00-24:00';
   //                     $tmp[$beforeday] = implode(',', $splitBeforeDay);
                        $reportHours = 0;
                     }
                     if (!empty($toAdd)) {
                        array_unshift($splitDay, $toAdd);
                     }
                     $tmp[$day] = implode(',', $splitDay);
                  } else if ($reportHours > 0) {
                     //$tmp[$day] = '00:00-'.$reportHours.':00';
                     $reportHours = 0;
                  }
                  $beforeday = $day;
               }
               // Manage for report hours from saturday to sunday
               if ($reportHours > 0) {
                  $splitDay = explode(',', $tmp['sunday']);
                  array_unshift($splitDay, '00:00-'.sprintf("%02s", $reportHours).':00');
                  $tmp['sunday'] = implode(',', $splitDay);
               }
               if ($saturday != '') {
                  if (isset($tmp['saturday'])) {
                     $tmp['saturday'] .= $saturday;
                  } else {
                     $tmp['saturday'] = $saturday;
                  }
               }

               // concatain if need
               foreach ($days as $day) {
                  if (isset($tmp[$day])) {
                     $splitDay = explode(',', $tmp[$day]);
                     $beforeHour = '';
                     $beforeNum  = 0;
                     foreach ($splitDay as $num=>$data) {
                        if (substr($data, 0, 2) == $beforeHour) {
                           $splitDay[$beforeNum] = substr($splitDay[$beforeNum], 0, 6).substr($data, 6, 5);
                           $beforeHour = substr($data, 6, 2);
                           unset($splitDay[$num]);
                        } else {
                           $beforeHour = substr($data, 6, 2);
                           $beforeNum = $num;
                        }
                     }
                     $tmp[$day] = implode(',', $splitDay);
                  }
               }

               $a_timeperiods[$i] = $tmp;
               $i++;


            }
         }
      }

      PluginMonitoringToolbox::logIfExtradebug(
         'pm-shinken',
         "End generateTimeperiodsCfg\n"
      );

      if ($file == "1") {
         $config = "# Generated by plugin monitoring for GLPI\n# on ".date("Y-m-d H:i:s")."\n\n";

         foreach ($a_timeperiods as $data) {
            $config .= $this->writeFile("timeperiod", $data);
         }
         return array('timeperiods.cfg', $config);

      } else {
         return $a_timeperiods;
      }
   }


}

?>
