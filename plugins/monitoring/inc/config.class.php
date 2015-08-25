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

class PluginMonitoringConfig extends CommonDBTM {


   static $rightname = 'config';

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Configuration', 'monitoring');
   }



   function initConfig() {
      global $DB;

      $query = "SELECT * FROM `".$this->getTable()."`
         LIMIT 1";

      $result = $DB->query($query);
      if ($DB->numrows($result) == '0') {
         $input = array();
         $input['timezones']    = '["0"]';
         $input['logretention'] = 30;
         $input['extradebug']   = 0;
         $this->add($input);
      }
   }



   /**
   * Display form for configuration
   *
   * @param $items_id integer ID
   * @param $options array
   *
   *@return bool true if form is ok
   *
   **/
   function showForm($items_id, $options=array()) {
      global $DB,$CFG_GLPI;

      $options['candel'] = false;

      if ($this->getFromDB("1")) {

      } else {
         $input = array();
         $this->add($input);
         $this->getFromDB("1");
      }

      $this->showFormHeader($options);

      $this->getFromDB($items_id);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Logs retention (in days)', 'monitoring')."&nbsp;:</td>";
      echo "<td width='100'>";
      Dropdown::showNumber("logretention", array(
                'value' => $this->fields['logretention'],
                'min'   => 0,
                'max'   => 1000)
      );
      echo "</td>";
      echo "<td rowspan='4'>";
      echo __('Timezones (for graph)', 'monitoring')."&nbsp:";
      echo "</td>";
      echo "<td rowspan='4'>";
         $a_timezones = $this->getTimezones();

         $a_timezones_selected = importArrayFromDB($this->fields['timezones']);
         $a_timezones_selected2 = array();
         foreach ($a_timezones_selected as $timezone) {
            $a_timezones_selected2[$timezone] = $a_timezones[$timezone];
            unset($a_timezones[$timezone]);
         }
         ksort($a_timezones_selected2);

            echo "<table>";
            echo "<tr>";
            echo "<td class='right'>";

            if (count($a_timezones)) {
               echo "<select name='timezones_to_add[]' multiple size='5'>";

               foreach ($a_timezones as $key => $val) {
                  echo "<option value='$key'>".$val."</option>";
               }

               echo "</select>";
            }

            echo "</td><td class='center'>";

            if (count($a_timezones)) {
               echo "<input type='submit' class='submit' name='timezones_add' value='".
                     __('Add')." >>'>";
            }
            echo "<br><br>";

            if (count($a_timezones_selected2)) {
               echo "<input type='submit' class='submit' name='timezones_delete' value='<< ".
                     _sx('button', 'Delete permanently')."'>";
            }
            echo "</td><td>";

         if (count($a_timezones_selected2)) {
            echo "<select name='timezones_to_delete[]' multiple size='5'>";
            foreach ($a_timezones_selected2 as $key => $val) {
               echo "<option value='$key'>".$val."</option>";
            }
            echo "</select>";
         } else {
            echo "&nbsp;";
         }
         echo "</td>";
         echo "</tr>";
         echo "</table>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Extra-debug', 'monitoring')." :</td>";
      echo "<td>";
      Dropdown::showYesNo("extradebug", $this->fields['extradebug']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Use container/VM name as prefix of NRPE command + use IP address of host', 'monitoring')." :</td>";
      echo "<td>";
      Dropdown::showYesNo("nrpe_prefix_contener", $this->fields['nrpe_prefix_contener']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Append id to hostname when generate conf', 'monitoring')." :</td>";
      echo "<td>";
      Dropdown::showYesNo("append_id_hostname", $this->fields['append_id_hostname']);
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }



   static function getPHPPath() {

      $pmConfig = new PluginMonitoringConfig();
      $pmConfig->getFromDB("1");
      return $pmConfig->getField("phppath");
   }



   static function getTimezones() {
      $a_timezones = array();
      $a_timezones['0'] = "GMT";
      $a_timezones['+1'] = "GMT+1";
      $a_timezones['+2'] = "GMT+2";
      $a_timezones['+3'] = "GMT+3";
      $a_timezones['+4'] = "GMT+4";
      $a_timezones['+5'] = "GMT+5";
      $a_timezones['+6'] = "GMT+6";
      $a_timezones['+7'] = "GMT+7";
      $a_timezones['+8'] = "GMT+8";
      $a_timezones['+9'] = "GMT+9";
      $a_timezones['+10'] = "GMT+10";
      $a_timezones['+11'] = "GMT+11";
      $a_timezones['+12'] = "GMT+12";
      $a_timezones['-1'] = "GMT-1";
      $a_timezones['-2'] = "GMT-2";
      $a_timezones['-3'] = "GMT-3";
      $a_timezones['-4'] = "GMT-4";
      $a_timezones['-5'] = "GMT-5";
      $a_timezones['-6'] = "GMT-6";
      $a_timezones['-7'] = "GMT-7";
      $a_timezones['-8'] = "GMT-8";
      $a_timezones['-9'] = "GMT-9";
      $a_timezones['-10'] = "GMT-10";
      $a_timezones['-11'] = "GMT-11";

      ksort($a_timezones);
      return $a_timezones;

   }


   function rrmdir($dir) {

      if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
          if ($object != "." && $object != "..") {
            if (filetype($dir."/".$object) == "dir") {
               $this->rrmdir($dir."/".$object);
            } else {
               unlink($dir."/".$object);
            }
          }
        }
        reset($objects);
        rmdir($dir);
      }
   }

}

?>