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
   @since     2013

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringPerfdataDetail extends CommonDBTM {

   static $rightname = 'plugin_monitoring_perfdata';



   function showDetails($perfdatas_id) {

      $a_details = $this->find("`plugin_monitoring_perfdatas_id`='".$perfdatas_id."'", "position");
      foreach ($a_details as $a_detail) {
         $this->showForm($a_detail['id']);
      }
   }



   static function updateDetailForPerfdata($perfdata, $perfdatas_id) {

      $a_lines = array();

      $a_perfdata = PluginMonitoringPerfdata::splitPerfdata($perfdata);

      $i = 1;
      foreach ($a_perfdata as $data) {
         $data = trim($data, ", ");
         $a_a_perfdata = explode("=", $data);
         $a_a_perfdata[0] = trim($a_a_perfdata[0], "'");
         if (!isset($a_a_perfdata[1])) {
            return;
         }
         //$a_a_perfdata[1] = trim($a_a_perfdata[1], ";");
         $a_lines[$i] = array('name' => $a_a_perfdata[0]);
         $a_perfdata_final = explode(";", $a_a_perfdata[1]);
         $num = 1;
         foreach ($a_perfdata_final as $nb_val=>$val) {
            if ($val == '') {
               if ($nb_val <(count($a_perfdata_final) - 1)) {
                  $a_lines[$i]['values'][$num] = '';
               }
            } else {
               $a_lines[$i]['values'][$num] = '';
            }
            $num++;
         }
         $i++;
      }

      // Add/update perfdatadetails in DB
      $pmPerfdataDetail = new PluginMonitoringPerfdataDetail();
      $a_perfdatadetails = $pmPerfdataDetail->find("`plugin_monitoring_perfdatas_id`='".$perfdatas_id."'", "position");

      foreach ($a_perfdatadetails as $data) {
         $find = 0;
         foreach ($a_lines as $key=>$a_line) {
            if ($a_line['name'] == $data['name']
                    && $data['position'] == $key
                    && !$find) {
               $find = 1;
               $countfind = count($a_line['values']);
               $input = array();
               $input['id'] = $data['id'];
               $input['dsname_num'] = $countfind;
               for ($i=1; $i<=$countfind; $i++) {
                  if ($data['dsname'.$i] == '') {
                     $input['dsname'.$i] = 'value'.$data['position'].'.'.$i;
                  }
               }

               for ($i=($countfind+1); $i<9; $i++) {
                  $input['dsname'.$i] = '';
               }
               $pmPerfdataDetail->update($input);
               unset($a_lines[$key]);
            }
         }
         if (!$find) {
            $pmPerfdataDetail->delete($data);
         }
      }

      foreach ($a_lines as $position=>$data) {
         $input = array();
         $input['name'] = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($data['name']));
         $input['plugin_monitoring_perfdatas_id'] = $perfdatas_id;
         $input['position'] = $position;
         $input['dsname_num'] = count($data['values']);
         for ($i=1; $i<=$input['dsname_num']; $i++) {
            $input['dsname'.$i] = 'value'.$position.'.'.$i;
         }
         $pmPerfdataDetail->add($input);
      }
   }



   function showForm($id, $options=array()) {

      $options['candel'] = false;
      $this->initForm($id, $options);
      $this->showFormHeader($options);

      echo "<tr>";
      echo "<td>";
      echo __('Name')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      echo $this->fields['name'];
      echo "</td>";
      echo "<td>";
      echo __('Value', 'monitoring').' 1';
      echo "</td>";
      echo "<td>";
      $this->showFormValue(1);
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>";
      echo __('Is name dynamic', 'monitoring');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('dynamic_name', $this->fields['dynamic_name']);
      echo "</td>";
      echo "<td>";
      echo __('Value', 'monitoring').' 2';
      echo "</td>";
      echo "<td>";
      $this->showFormValue(2);
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>";
      echo __('Position', 'monitoring');
      echo "</td>";
      echo "<td>";
      echo $this->fields['position'];
      echo "</td>";
      echo "<td>";
      echo __('Value', 'monitoring').' 3';
      echo "</td>";
      echo "<td>";
      $this->showFormValue(3);
      echo "</td>";
      echo "</tr>";

      for ($i=4; $i<9; $i++) {
         if ($i <= $this->fields['dsname_num']) {
            echo "<tr>";
            echo "<td colspan='2'>";
            echo "</td>";
            echo "<td>";
            echo __('Value', 'monitoring').' '.$i;
            echo "</td>";
            echo "<td>";
            $this->showFormValue($i);
            echo "</td>";
            echo "</tr>";
         }
      }

      $this->showFormButtons($options);

      return true;
   }



   function showFormValue($i) {
      if ($i <= $this->fields['dsname_num']) {
         echo "<input type='text' name='dsname".$i."' value='".$this->fields['dsname'.$i]."' />";
         $checked = '';
         if ($this->fields['dsnameincr'.$i] == 1) {
            $checked = 'checked';
         }
         echo " <input type='checkbox' name='dsnameincr".$i."' title='".__('Incremental', 'monitoring')."' $checked />";
         echo __('Incremental', 'monitoring');
      }
   }
}
?>