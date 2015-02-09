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

class PluginMonitoringServicedef extends CommonDBTM {


   static $rightname = 'plugin_monitoring_service';

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {

      return "Service template";
   }


   /*
    * Add some services templates at install
    *
    */
   function initTemplates() {



   }



   function getSearchOptions() {
      $tab = array();

      $tab['common'] = __('Resources', 'monitoring');

		$tab[1]['table'] = $this->getTable();
		$tab[1]['field'] = 'name';
		$tab[1]['linkfield'] = 'name';
		$tab[1]['name'] = __('Name');
		$tab[1]['datatype'] = 'itemlink';

      $tab[2]['table']         = $this->getTable();
      $tab[2]['field']         = 'id';
      $tab[2]['name']          = __('ID');
      $tab[2]['massiveaction'] = false; // implicit field is id

      return $tab;
   }



   function defineTabs($options=array()){
      $ong = array();

      $ong[2] = __('Business rules', 'monitoring');

      return $ong;
   }



   function maybeTemplate() {

      if (!isset($this->fields['id'])) {
         $this->getEmpty();
      }
      if (strstr($_SERVER['PHP_SELF'], 'service.form.php')) {
         return false;
      } else {
         return isset($this->fields['is_template']);
      }
   }



   /**
   * Display form for service configuration
   *
   * @param $items_id integer ID
   * @param $options array
   *
   *@return bool true if form is ok
   *
   **/
   function showForm($items_id, $options=array()) {
      $pMonitoringCommand = new PluginMonitoringCommand();


      if ($items_id == '0') {
         $this->getEmpty();
      } else {
         $this->getFromDB($items_id);
      }

      $this->showFormHeader($options);

      echo "<tr>";
      echo "<td>";
      echo __('Template name')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      echo "<input type='hidden' name='is_template' value='1' />";
      $objectName = autoName($this->fields["name"], "name", 1,
                             $this->getType());
      Html::autocompletionTextField($this, 'name', array('value' => $objectName));
      echo "</td>";

      // * commande
      echo "<td>";
      echo __('Command', 'monitoring')."&nbsp;:";
      echo "</td>";
      echo "<td align='center'>";
      if ($this->fields['is_template'] == '1') {
         $pMonitoringCommand->getFromDB($this->fields['plugin_monitoring_commands_id']);
         echo $pMonitoringCommand->getLink(1);
      } else {
         $pMonitoringCommand->getFromDB($this->fields['plugin_monitoring_commands_id']);
         Dropdown::show("PluginMonitoringCommand", array(
                              'name' =>'plugin_monitoring_commands_id',
                              'value'=>$this->fields['plugin_monitoring_commands_id']
                              ));
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      // * checks
      echo "<td>".__('Check definition', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      if ($this->fields['is_template'] == '1') {
         $pMonitoringCheck = new PluginMonitoringCheck();
         $pMonitoringCheck->getFromDB($this->fields['plugin_monitoring_checks_id']);
         echo $pMonitoringCheck->getLink(1);
      } else {
         Dropdown::show("PluginMonitoringCheck",
                        array('name'=>'plugin_monitoring_checks_id',
                              'value'=>$this->fields['plugin_monitoring_checks_id']));
      }
      echo "</td>";
      // * active check
      echo "<td>";
      echo __('Active checks enable', 'monitoring')."&nbsp;:";
      echo "</td>";
      echo "<td align='center'>";
      if ($this->fields['is_template'] == '1') {
         echo Dropdown::getYesNo($this->fields['active_checks_enabled']);
      } else {
         Dropdown::showYesNo("active_checks_enabled", $this->fields['active_checks_enabled']);
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      // * passive check
      echo "<td>";
      echo __('Passive checks enable', 'monitoring')."&nbsp;:";
      echo "</td>";
      echo "<td align='center'>";
      if ($this->fields['is_template'] == '1') {
         echo Dropdown::getYesNo($this->fields['passive_checks_enabled']);
      } else {
         Dropdown::showYesNo("passive_checks_enabled", $this->fields['passive_checks_enabled']);
      }
      echo "</td>";
      // * calendar
      echo "<td>".__('Check period', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      if ($this->fields['is_template'] == '1') {
         $calendar = new Calendar();
         $calendar->getFromDB($this->fields['calendars_id']);
         echo $calendar->getLink(1);
      } else {
         dropdown::show("Calendar", array('name'=>'calendars_id',
                                 'value'=>$this->fields['calendars_id']));
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<th colspan='4'>".__('Remote check', 'monitoring')."</th>";
      echo "</tr>";

      echo "<tr>";
      // * remotesystem
      echo "<td>";
      echo __('Utility used for remote check', 'monitoring')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      $input = array();
      $input[''] = '------';
      $input['byssh'] = __('By ssh', 'monitoring');
      $input['nrpe'] = 'nrpe';
      $input['nsca'] = 'nsca';
      if ($this->fields['is_template'] == '1') {
         echo $input[$this->fields['remotesystem']];
      } else {
         Dropdown::showFromArray("remotesystem",
                              $input,
                              array('value'=>$this->fields['remotesystem']));
      }
      echo "</td>";
      // * is_argument
      echo "<td>";
      echo __('Use arguments (Only for NRPE)', 'monitoring')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      if ($this->fields['is_template'] == '1') {
         echo Dropdown::getYesNo($this->fields['is_arguments']);
      } else {
         Dropdown::showYesNo("is_arguments", $this->fields['is_arguments']);
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      // alias command
      echo "<td>";
      echo __('Alias command if required (Only for NRPE)', 'monitoring')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      if ($this->fields['is_template'] == '1') {
         echo "<input type='text' name='alias_commandservice' value='".$this->fields['alias_command']."' />";
      } else {
         echo "<input type='text' name='alias_command' value='".$this->fields['alias_command']."' />";
      }
      echo "</td>";
      echo "<td>";
      echo __('Command link (used for graphs generation)', 'monitoring')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      if ($this->fields['is_template'] == '1') {
         $pMonitoringCommand->getFromDB($this->fields['aliasperfdata_commands_id']);
         echo $pMonitoringCommand->getLink(1);
      } else {
         $pMonitoringCommand->getFromDB($this->fields['aliasperfdata_commands_id']);
         Dropdown::show("PluginMonitoringCommand", array(
                              'name' =>'aliasperfdata_commands_id',
                              'value'=>$this->fields['aliasperfdata_commands_id']
                              ));
      }
      echo "</td>";
      echo "</tr>";


      // * Manage arguments
      $array = array();
      $a_displayarg = array();
      if (isset($pMonitoringCommand->fields['command_line'])) {
         preg_match_all("/\\$(ARG\d+)\\$/", $pMonitoringCommand->fields['command_line'], $array);
         $a_arguments = importArrayFromDB($this->fields['arguments']);
         foreach ($array[0] as $arg) {
            if (strstr($arg, "ARG")) {
               $arg = str_replace('$', '', $arg);
               if (!isset($a_arguments[$arg])) {
                  $a_arguments[$arg] = '';
               }
               $a_displayarg[$arg] = $a_arguments[$arg];

            }
         }
      }
      if (count($a_displayarg) > 0) {
         $a_argtext = importArrayFromDB($pMonitoringCommand->fields['arguments']);
         echo "<tr>";
         echo "<th colspan='4'>".__('Arguments', 'monitoring')."&nbsp;</th>";
         echo "</tr>";

         foreach ($a_displayarg as $key=>$value) {
         echo "<tr>";
         echo "<th>".$key."</th>";
         echo "<td colspan='2'>";
            if (isset($a_argtext[$key])) {
               echo nl2br($a_argtext[$key])."&nbsp;:";
            } else {
               echo __('Argument', 'monitoring')."&nbsp;:";
            }
            echo "</td>";
            echo "<td>";
            echo "<input type='text' name='arg[".$key."]' value='".$value."'/><br/>";
            echo "</td>";
            echo "</tr>";
         }
      }

      $this->showFormButtons($options);
      return true;
   }
}

?>