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

class PluginMonitoringSlider_item extends CommonDBTM {


   static $rightname = 'plugin_monitoring_slider';

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Carrousel / slider', 'monitoring');
   }


   
   function view($id, $config=0) {
      global $DB, $CFG_GLPI;

      $pmSlider = new PluginMonitoringSlider();

      $pmSlider->getFromDB($id);

      $style = '';
      if ($config == '1') {
         $this->addItem($id);
      }

      // Display items
   }



   function reloadView($id, $config) {
      global $DB;

      $pmSlider = new PluginMonitoringSlider();
      $pmSlider->getFromDB($id);

      $query = "SELECT * FROM `glpi_plugin_monitoring_sliders_items`
         WHERE `plugin_monitoring_sliders_id`='".$id."'";
      $result = $DB->query($query);
      $a_items = array();
      while ($data=$DB->fetch_array($result)) {
         if ($this->displayItem($data, $config)) {
            $a_items[] = "item".$data['id'];
         }
      }
   }



   function displayItem($data, $config) {
      global $CFG_GLPI;

      return true;
   }



   function addItem($sliders_id) {
      global $DB,$CFG_GLPI;

      $this->getEmpty();

      $pmSlider = new PluginMonitoringSlider();
      $pmSlider->getFromDB($sliders_id);

      // Manage entity_sons
      $a_entities = array();
      if (!($pmSlider->fields['entities_id']<0)) {
         if ($pmSlider->fields['is_recursive'] == '0') {
            $a_entities[$pmSlider->fields['entities_id']] = $pmSlider->fields['entities_id'];
         } else {
            $a_entities = getSonsOf('glpi_entities', $pmSlider->fields['entities_id']);
         }
      }

      $options = array();
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo "<input type='hidden' name='plugin_monitoring_sliders_id' value='".$sliders_id."' />";
      echo __('Element to display', 'monitoring')." :</td>";
      echo "<td>";
      $elements = array();
      $elements['NULL'] = Dropdown::EMPTY_VALUE;
      $elements['PluginMonitoringDisplayview']        = __('Views', 'monitoring');
      $elements['PluginMonitoringServicescatalog']    = PluginMonitoringServicescatalog::getTypeName();
      $elements['PluginMonitoringService']            = __('Resources (graph)', 'monitoring');
      $elements['PluginMonitoringComponentscatalog']  = __('Components catalog', 'monitoring');
      $elements['PluginMonitoringWeathermap']         = __('Weathermap', 'monitoring');
      $elements['PluginMonitoringCustomitem_Gauge']   = PluginMonitoringCustomitem_Gauge::getTypeName();
      $elements['PluginMonitoringCustomitem_Counter'] = PluginMonitoringCustomitem_Counter::getTypeName();
      if (in_array('maps', $_SESSION['glpi_plugins'])) {
         $elements['PluginMapsMap']                      = 'Maps';
      }
      $rand = Dropdown::showFromArray('itemtype', $elements, array('value'=>$this->fields['itemtype']));

      $params = array('itemtype'        => '__VALUE__',
                'sliders_id' => $sliders_id,
                'myname'          => "items_id",
                'a_entities' => $a_entities);

      Ajax::updateItemOnSelectEvent("dropdown_itemtype".$rand,"items_id",
                                  $CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/dropdownDisplayviewItemtype.php",
                                  $params);
      echo "<span id='items_id'></span>";
      echo "<input type='hidden' name='x' value='1' />";
      echo "<input type='hidden' name='y' value='1' />";
      echo "</td>";

      echo "<td colspan='2'></td>";
      echo "</tr>";

      $this->showFormButtons($options);

      // Show items
      $query = "SELECT * FROM `glpi_plugin_monitoring_sliders_items`
              WHERE `plugin_monitoring_sliders_id`='".$sliders_id."'";

      $result = $DB->query($query);
      echo "<table class='tab_cadre' width='600'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='3'>";
      echo _n('Associated item', 'Associated items', 2);
      echo "</th>";
      echo "</tr>";
      while ($data=$DB->fetch_array($result)) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>";
         $itemtype = $data['itemtype'];
         $item = new $itemtype();
         echo $item->getTypeName();
         echo "</td>";
         echo "<td>";
         $item->getFromDB($data['items_id']);
         echo $item->getLink();
         echo "</td>";
         echo "<td>";
         echo "<form name='form' method='post' action='".$this->getFormURL()."' >";
         echo "<input type='hidden' name='id' value='".$data['id']."'>";
         echo "<input type='submit' name='delete' value=\""._sx('button',
                                                                'Delete permanently')."\"
                class='submit' ".
                Html::addConfirmationOnAction(__('Confirm the final deletion?')).">";
         Html::closeForm();
         echo "</td>";
         echo "</tr>";
      }
      echo "</table>";

      return true;
   }
}

?>
