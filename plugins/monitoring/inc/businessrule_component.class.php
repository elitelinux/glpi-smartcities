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
   @since     2014

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringBusinessrule_component extends CommonDBTM {

   static $rightname = 'plugin_monitoring_servicescatalog';

   function replayDynamicServices($plugin_monitoring_businessrulegroups_id) {
      global $DB;

      if ($plugin_monitoring_businessrulegroups_id == 0) {
         return;
      }
      // Get entity and if recursif
      $pmBusinessrulegroup = new PluginMonitoringBusinessrulegroup();
      $pmServicescatalog = new PluginMonitoringServicescatalog();

      $pmBusinessrulegroup->getFromDB($plugin_monitoring_businessrulegroups_id);
      $pmServicescatalog->getFromDB($pmBusinessrulegroup->fields['plugin_monitoring_servicescatalogs_id']);

      if ($pmServicescatalog->fields['is_recursive']) {
         $a_sons = getSonsOf("glpi_entities", $pmServicescatalog->fields['entities_id']);
         $restrict_entities = "AND ( `glpi_plugin_monitoring_services`.`entities_id` IN ('".implode("','", $a_sons)."') )";
      } else {
         $restrict_entities = "AND ( `glpi_plugin_monitoring_services`.`entities_id` = '".
                 $pmServicescatalog->fields['entities_id']."' )";
      }

      $a_brcomponents = $this->find("`plugin_monitoring_businessrulegroups_id`='".$plugin_monitoring_businessrulegroups_id."'");

      $a_services = array();

      foreach ($a_brcomponents as $a_brcomponent) {

         $pmComponentscatalog_Component = new PluginMonitoringComponentscatalog_Component();
         $pmComponentscatalog_Component->getFromDB($a_brcomponent['plugin_monitoring_componentscatalogs_components_id']);

         // Get all services of component of component catalog
         $query = "SELECT `glpi_plugin_monitoring_services`.`id` FROM `glpi_plugin_monitoring_services`"
                 . " LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`"
                 . "    ON plugin_monitoring_componentscatalogs_hosts_id="
                 . "       `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`"
                 . " WHERE `glpi_plugin_monitoring_services`.`plugin_monitoring_components_id`"
                 . "          = '".$pmComponentscatalog_Component->fields['plugin_monitoring_components_id']."' ".
                 $restrict_entities;
         $result = $DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $a_services[$data['id']] = $data['id'];
         }
      }

      // get static services of the group (so not add dynamic if yet in static)
      $pmBusinessrule_component = new PluginMonitoringBusinessrule_component;
      $pmBusinessrule = new PluginMonitoringBusinessrule();
      $a_static = $pmBusinessrule->find(
              "`plugin_monitoring_businessrulegroups_id`='".$plugin_monitoring_businessrulegroups_id."'"
              . " AND `is_dynamic`=0");
      foreach ($a_static as $data) {
         if (isset($a_services[$data['plugin_monitoring_services_id']])) {
            unset($a_services[$data['plugin_monitoring_services_id']]);

            // Update generic status
            $pmBusinessrule->getFromDB($data['id']);
            $input = array(
                'id' => $data['id'],
                'is_generic' => $pmServicescatalog->fields['is_generic']
            );
            $pmBusinessrule->update($input);
         }
      }

      // update services + is_dynamic=1
      $query = "SELECT * FROM `glpi_plugin_monitoring_businessrules`"
              . " WHERE `plugin_monitoring_businessrulegroups_id`='".$plugin_monitoring_businessrulegroups_id."'"
              . " AND `is_dynamic`=1";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         // Update if yet in DB
         if (isset($a_services[$data['plugin_monitoring_services_id']])) {
            unset($a_services[$data['plugin_monitoring_services_id']]);

            // Update generic status
            $pmBusinessrule->getFromDB($data['id']);
            $input = array(
                'id' => $data['id'],
                'is_generic' => $pmServicescatalog->fields['is_generic']
            );
            $pmBusinessrule->update($input);
         } else {
            // delete if not exist
            $pmBusinessrule->delete($data);
         }
      }

      // Add new
      foreach ($a_services as $services_id) {
         $input = array(
             'plugin_monitoring_businessrulegroups_id' => $plugin_monitoring_businessrulegroups_id,
             'plugin_monitoring_services_id' => $services_id,
             'is_dynamic' => '1',
             'is_generic' => $pmServicescatalog->fields['is_generic']
         );
         $pmBusinessrule->add($input);
      }
   }
}

?>