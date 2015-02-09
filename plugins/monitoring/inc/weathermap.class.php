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

class PluginMonitoringWeathermap extends CommonDBTM {

   static $rightname = 'plugin_monitoring_weathermap';

   static function getTypeName($nb=0) {
      return __('Weathermap', 'monitoring');
   }



   function defineTabs($options=array()){
      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(__CLASS__, $ong, $options);
      return $ong;
   }



   /**
    * Display tab
    *
    * @param CommonGLPI $item
    * @param integer $withtemplate
    *
    * @return varchar name of the tab(s) to display
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      $ong = array();
      if ($item->getID() > 0) {
         $ong[2] = __('Nodes and links', 'monitoring');
      }
      return $ong;
   }



   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($tabnum == 2) {
         echo $item->configureNodesLinks($item->getID());
      }
      return TRUE;
   }



   function showForm($items_id, $options=array()) {
      global $DB,$CFG_GLPI;

      $this->initForm($items_id, $options);
      $options['formoptions'] = " enctype='multipart/form-data'";
      $this->showFormHeader($options);

      echo "<tr>";
      echo "<td>";
      echo __('Name')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      $objectName = autoName($this->fields["name"], "name", 1,
                             $this->getType());
      Html::autocompletionTextField($this, 'name', array('value' => $objectName));
      echo "</td>";
      echo "<td>".__('Width', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      Dropdown::showNumber("width", array(
                'value' => $this->fields['width'],
                'min'   => 100,
                'max'   => 3000,
                'step'  => 20)
      );
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>";
      echo __('Background image', 'monitoring')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      if ($this->fields['background'] == '') {
         echo "<input type='file' size='25' value='' name='background'/>";
      } else {
         echo $this->fields['background'];
         echo "&nbsp;";
         echo "<input type='image' name='deletepic' value='deletepic' class='submit' src='".$CFG_GLPI["root_doc"]."/pics/delete.png' >";

      }
      echo "</td>";
      echo "<td>".__('Height', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      Dropdown::showNumber("height", array(
                'value' => $this->fields['height'],
                'min'   => 100,
                'max'   => 3000,
                'step'  => 20)
      );
      echo "</td>";
      echo "</tr>";


      $this->showFormButtons($options);

      PluginMonitoringToolbox::loadLib();

      return true;
   }



   function configureNodesLinks($weathermaps_id) {
      global $DB,$CFG_GLPI;

      $networkPort = new NetworkPort();

      $this->getFromDB($weathermaps_id);

      $style = '';
      if ($this->fields['width'] > 950) {
         $style = ";position:relative;left:-".(($this->fields['width'] - 950) / 2)."px";
      }


      echo "<table class='tab_cadre' style='width:".
              $this->fields['width']."px;height:".$this->fields['height']."px".
              $style."'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'>";
      echo __('Nodes and links', 'monitoring');
      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td valign='top'>";
      echo "<div>";
      PluginMonitoringToolbox::loadLib();
      $this->drawMap($weathermaps_id, 100, 1);
      echo "</div>";
      echo "</td>";
      echo "<td valign='top'>";

      echo "<div style='position: fixed;top: 30px;right: 0;z-index:999;' >";
      echo "<table class='tab_cadre' width='100%'>";
      echo "<tr>";
      echo "<td>";
      echo "<a onClick='Ext.get(\"weathermapform\").toggle();'>
      <img src='".$CFG_GLPI["root_doc"]."/pics/deplier_down.png' />&nbsp;
         ".__('Display weathermap form', 'monitoring')."
      &nbsp;<img src='".$CFG_GLPI["root_doc"]."/pics/deplier_down.png' /></a>";
      echo "</td>";
      echo "</tr>";
      echo"</table>";
      echo "</div>";

      echo "<div style='position: fixed;top: 50px;right: 0;z-index:1000;' id='weathermapform' >";
      echo '<form name="pointform" method="post" action="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/front/weathermapnode.form.php">';
      echo "<table>";
      echo "<tr>";
      echo "<td>";

         echo "<table class='tab_cadre' width='100%'>";
         echo "<tr>";
         echo "<th colspan='2'>";
         echo "x : ";
         echo '<input type="text" name="x" size="4" value="50" />';
         echo " ";
         echo "y : ";
         echo '<input type="text" name="y" size="4" value="50"/>';
         echo "</th>";
         echo "</tr>";

         // * Add node
         echo "<tr>";
         echo "<th colspan='2'>";
         echo "<input type='hidden' name='plugin_monitoring_weathermaps_id' value='".$weathermaps_id."' />";
         echo __('Add a node', 'monitoring');
         echo "</th>";
         echo "</tr>";

         echo "<tr>";
         echo "<td>";
         echo __('Node', 'monitoring')."&nbsp;:";
         echo "</td>";
         echo "<td>";
         Dropdown::showAllItems("items_id");
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td>";
         echo __('Name')."&nbsp;:";
         echo "</td>";
         echo "<td>";
         echo "<input type='text' name='name' value='' />";
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td>";
         echo __('Position of label', 'monitoring')." :";
         echo "</td>";
         echo "<td>";
         $positions = array(
             'middle' => __('Center', 'monitoring'),
             'start' => __('Right', 'monitoring'),
             'end' => __('Left', 'monitoring')
         );
         Dropdown::showFromArray('position', $positions);
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td align='center' colspan='2'>";
         echo "<input type='submit' name='add' value=\"".__('Add')."\" class='submit'>";
         echo "</td>";
         echo "</tr>";


         // * Change node position
         echo "<tr>";
         echo "<th colspan='2'>";
         echo __('Edit a node', 'monitoring');
         echo "</th>";
         echo "</tr>";

         echo "<tr>";
         echo "<td colspan='2' align='center'>";

         $query = "SELECT * FROM `".getTableForItemType("PluginMonitoringWeathermapnode")."`
            WHERE `plugin_monitoring_weathermaps_id`='".$weathermaps_id."'
            ORDER BY `name`";
         $result = $DB->query($query);
         $elements = array();
         $elements[0] = Dropdown::EMPTY_VALUE;
         $result = $DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $itemtype = $data['itemtype'];
            if ($itemtype == '0') {
               $pmWeathermapnode->delete($data);
            } else {
               $item = new $itemtype();
               $item->getFromDB($data['items_id']);
               $name = $data['name'];
               if ($name == '') {
                  $name = $item->getName();
               }
               $elements[$data['id']] = $name;
            }
         }
         $rand = Dropdown::showFromArray('id_update', $elements);

         $params = array('items_id'        => '__VALUE__',
                         'rand'            => $rand);

         Ajax::updateItemOnSelectEvent("dropdown_id_update$rand", "show_updatenode$rand",
                                     $CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/dropdownWnode.php",
                                     $params, TRUE);

         echo "<span id='show_updatenode$rand'></span>\n";

         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td align='center' colspan='2'>";
         echo "<input type='submit' name='update' value=\"".__('Save')."\" class='submit'>";
         echo "</td>";
         echo "</tr>";


         // * Delete node
         echo "<tr>";
         echo "<th colspan='2'>";
         echo __('Delete a node', 'monitoring');
         echo "</th>";
         echo "</tr>";

         echo "<tr>";
         echo "<td>";
         echo "</td>";
         echo "<td>";
         $query = "SELECT * FROM `".getTableForItemType("PluginMonitoringWeathermapnode")."`
            WHERE `plugin_monitoring_weathermaps_id`='".$weathermaps_id."'
            ORDER BY `name`";
         $result = $DB->query($query);
         $elements = array();
         $elements[0] = Dropdown::EMPTY_VALUE;
         $result = $DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $itemtype = $data['itemtype'];
            $item = new $itemtype();
            $item->getFromDB($data['items_id']);
            $name = $data['name'];
            if ($name == '') {
               $name = $item->getName();
            }
            $elements[$data['id']] = $name;
         }
         Dropdown::showFromArray('id', $elements);
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td align='center' colspan='2'>";
         echo "<input type='submit' name='purge' value=\"".__('Delete permanently')."\" class='submit'>";
         echo "</td>";
         echo "</tr>";

         echo "</table>";
         Html::closeForm();

      echo "</td>";
      echo "</tr>";
      echo "<tr>";
      echo "<td>";

         echo '<form name="formlink" method="post" action="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/front/weathermaplink.form.php">';
         echo "<table class='tab_cadre' width='100%'>";
         // *Add Link
         echo "<tr>";
         echo "<th colspan='2'>";
         echo __('Add a link', 'monitoring');
         echo "</th>";
         echo "</tr>";

         echo "<tr>";
         echo "<td>";
         echo __('Source', 'monitoring')."*&nbsp;:";
         echo "</td>";
         echo "<td>";

         $query = "SELECT `glpi_plugin_monitoring_weathermapnodes`.`id` as `id`,
               `glpi_plugin_monitoring_weathermapnodes`.`name` as `name`,
               `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`,
               `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id`,
               `glpi_plugin_monitoring_services`.`id` as `services_id`,
               `glpi_plugin_monitoring_components`.`name` as `components_name`,
               `plugin_monitoring_commands_id`, `glpi_plugin_monitoring_components`.`arguments`,
               `glpi_plugin_monitoring_services`.`networkports_id`
            FROM `glpi_plugin_monitoring_weathermapnodes`

            LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
               ON (`glpi_plugin_monitoring_weathermapnodes`.`items_id`=`glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id`
                  AND `glpi_plugin_monitoring_weathermapnodes`.`itemtype`=`glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`)

            LEFT JOIN `glpi_plugin_monitoring_services`
               ON `plugin_monitoring_componentscatalogs_hosts_id`= `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`

            LEFT JOIN `glpi_plugin_monitoring_components`
               ON `plugin_monitoring_components_id` = `glpi_plugin_monitoring_components`.`id`


            WHERE `is_weathermap` = '1'
               AND `plugin_monitoring_weathermaps_id`='".$weathermaps_id."'
            ORDER BY `itemtype`,`items_id`,`glpi_plugin_monitoring_components`.`name`";
         $elements = array();
         $elements[0] = Dropdown::EMPTY_VALUE;
         $elements2 = array();
         $result = $DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $itemtype = $data['itemtype'];
            $item = new $itemtype();
            $item->getFromDB($data['items_id']);
            $name = $data['name'];
            if ($name == '') {
               $name = $item->getName();
            }
            // Try to get device/node connected on this port
            $device_connected = '';
            if ($data['arguments'] != '') {
               $arguments = importArrayFromDB($data['arguments']);
               foreach ($arguments as $argument) {
                  if (!is_numeric($argument)) {
                     if (strstr($argument, "[[NETWORKPORTDESCR]]")){
                        if (class_exists("PluginFusinvsnmpNetworkPort")) {
                           $pfNetworkPort = new PluginFusinvsnmpNetworkPort();
                           $pfNetworkPort->loadNetworkport($data['networkports_id']);
                           $argument = $pfNetworkPort->getValue("ifdescr");
                        }
                     } elseif (strstr($argument, "[[NETWORKPORTNUM]]")){
                        $networkPort = new NetworkPort();
                        $networkPort->getFromDB($data['networkports_id']);
                        $argument = $pfNetworkPort->fields['logical_number'];
                     } elseif (strstr($argument, "[[NETWORKPORTNAME]]")){
                        $networkPort = new NetworkPort();
                        $networkPort->getFromDB($data['networkports_id']);
                        $argument = $pfNetworkPort->fields['name'];
                     }


                     // Search networkport have this name or description
                     $a_ports = $networkPort->find("`itemtype`='".$itemtype."'
                        AND `items_id`='".$data['items_id']."'
                        AND `name`='".$argument."'");
                     foreach ($a_ports as $pdata) {
                        if ($device_connected == '') {
                           $oppositeports_id = $networkPort->getContact($pdata['id']);
                           if ($oppositeports_id) {
                              $networkPort->getFromDB($oppositeports_id);
                              $a_nodes = $pmWeathermapnode->find("
                                 `plugin_monitoring_weathermaps_id`='".$weathermaps_id."'
                                 AND `itemtype`='".$networkPort->fields['itemtype']."'
                                 AND `items_id`='".$networkPort->fields['items_id']."'", "", 1);
                              if (count($a_nodes) > 0) {
                                 $a_node = current($a_nodes);
                                 $device_connected = $pmWeathermapnode->getNodeName($a_node['id']);
                              }
                           }
                        }
                     }
                     if ($device_connected == ''
                             AND class_exists("PluginFusinvsnmpNetworkPort")) {
                        $queryn = "SELECT `glpi_networkports`.`id` FROM `glpi_plugin_fusinvsnmp_networkports`

                           LEFT JOIN `glpi_networkports`
                              ON `glpi_networkports`.`id`=`networkports_id`

                           WHERE `itemtype`='".$itemtype."'
                           AND `items_id`='".$data['items_id']."'
                           AND `ifdescr`='".$argument."'";
                        $resultn = $DB->query($queryn);
                        while ($pdata=$DB->fetch_array($resultn)) {
                           if ($device_connected == '') {
                              $oppositeports_id = $networkPort->getContact($pdata['id']);
                              if ($oppositeports_id) {
                                 $networkPort->getFromDB($oppositeports_id);
                                 $a_nodes = $pmWeathermapnode->find("
                                    `plugin_monitoring_weathermaps_id`='".$weathermaps_id."'
                                    AND `itemtype`='".$networkPort->fields['itemtype']."'
                                    AND `items_id`='".$networkPort->fields['items_id']."'", "", 1);
                                 if (count($a_nodes) > 0) {
                                    $a_node = current($a_nodes);

                                    $queryl = "SELECT `plugin_monitoring_weathermapnodes_id_1`
                                       FROM `glpi_plugin_monitoring_weathermaplinks`

                                       LEFT JOIN `glpi_plugin_monitoring_weathermapnodes`
                                          ON `glpi_plugin_monitoring_weathermapnodes`.`id` = `plugin_monitoring_weathermapnodes_id_1`

                                       WHERE ((`plugin_monitoring_weathermapnodes_id_1`='".$data['id']."'
                                                   AND `plugin_monitoring_weathermapnodes_id_2`='".$a_node['id']."')
                                                OR (`plugin_monitoring_weathermapnodes_id_1`='".$a_node['id']."'
                                                   AND `plugin_monitoring_weathermapnodes_id_2`='".$data['id']."'))
                                          AND `plugin_monitoring_weathermaps_id` = '".$weathermaps_id."'";
                                    $resultl = $DB->query($queryl);
                                    if ($DB->numrows($resultl) == '0') {
                                       $device_connected = $pmWeathermapnode->getNodeName($a_node['id']);
                                    }
                                 }
                              }
                           }
                        }
                     }
                  }
               }
            }
            if ($device_connected == '') {
               $networkPort->getFromDB($data['networkports_id']);
               $elements2[$data['id']."-".$data['services_id']] = $name." [".$networkPort->getfield('name')."] (".$data['components_name'].")";
            } else {
               $networkPort->getFromDB($data['networkports_id']);
               $elements[$data['id']."-".$data['services_id']] = $name." [".$networkPort->getfield('name')."] (".$data['components_name'].") > ".$device_connected;
            }
         }
         if (count($elements) > 1
                 AND count($elements2) > 0) {

            $elements = array_merge($elements,array('0'=>Dropdown::EMPTY_VALUE));
            $elements = array_merge($elements, $elements2);

         } else {
            $elements = array_merge($elements, $elements2);
         }

         Dropdown::showFromArray('linksource', $elements);

         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td>";
         echo __('Destination', 'monitoring')."&nbsp;:";
         echo "</td>";
         echo "<td>";

         echo "<div id='nodedestination'>";

         $query = "SELECT * FROM `".getTableForItemType("PluginMonitoringWeathermapnode")."`
            WHERE `plugin_monitoring_weathermaps_id`='".$weathermaps_id."'
            ORDER BY `name`";
         $result = $DB->query($query);
         $elements = array();
         $elements[0] = Dropdown::EMPTY_VALUE;
         $result = $DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $itemtype = $data['itemtype'];
            $item = new $itemtype();
            $item->getFromDB($data['items_id']);
            $name = $data['name'];
            if ($name == '') {
               $name = $item->getName();
            }
            $elements[$data['id']] = $name;
         }
         Dropdown::showFromArray('plugin_monitoring_weathermapnodes_id_2', $elements);
         echo "</div>";
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td>";
         echo __('Max bandwidth input', 'monitoring')."&nbsp;:";
         echo "</td>";
         echo "<td>";
         echo "<input type='text' name='bandwidth_in' value=''/>";
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td>";
         echo __('Max bandwidth output', 'monitoring')."&nbsp;:";
         echo "</td>";
         echo "<td>";
         echo "<input type='text' name='bandwidth_out' value=''/>";
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td align='center' colspan='2'>";
         echo "<input type='submit' name='add' value=\"".__('Add')."\" class='submit'>";
         echo "</td>";
         echo "</tr>";

         // * Edit link
         echo "<tr>";
         echo "<th colspan='2'>";
         echo __('Edit a link', 'monitoring');
         echo "</th>";
         echo "</tr>";
         echo "<tr>";
         echo "<td colspan='2' align='center'>";
         $pmWeathermapnode = new PluginMonitoringWeathermapnode();
         $query = "SELECT `glpi_plugin_monitoring_weathermaplinks`.`id` as `id`,
               `itemtype`, `items_id`, `name`, `plugin_monitoring_weathermapnodes_id_2`
            FROM `glpi_plugin_monitoring_weathermaplinks`

            LEFT JOIN `glpi_plugin_monitoring_weathermapnodes`
               ON `glpi_plugin_monitoring_weathermapnodes`.`id` = `plugin_monitoring_weathermapnodes_id_1`

            WHERE `plugin_monitoring_weathermaps_id` = '".$weathermaps_id."'";
         $elements = array();
         $elements[0] = Dropdown::EMPTY_VALUE;
         $result = $DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $itemtype = $data['itemtype'];
            $item = new $itemtype();
            $item->getFromDB($data['items_id']);
            $name1 = $data['name'];
            if ($name1 == '') {
               $name1 = $item->getName();
            }
            $pmWeathermapnode->getFromDB($data['plugin_monitoring_weathermapnodes_id_2']);
            $itemtype = $pmWeathermapnode->fields['itemtype'];
            $item = new $itemtype();
            $item->getFromDB($pmWeathermapnode->fields['items_id']);
            $name2 = $pmWeathermapnode->fields['name'];
            if ($name2 == '') {
               $name2 = $item->getName();
            }

            $elements[$data['id']] = $name1." - ".$name2;
         }
         $rand = Dropdown::showFromArray('id_update', $elements);

         $params = array('items_id'        => '__VALUE__',
                         'rand'            => $rand);

         Ajax::updateItemOnSelectEvent("dropdown_id_update$rand", "show_updatelink$rand",
                                     $CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/dropdownWlink.php",
                                     $params, TRUE);
         echo "<span id='show_updatelink$rand'></span>\n";
         echo "</td>";
         echo "</tr>";


         // * Delete link
         echo "<tr>";
         echo "<th colspan='2'>";
         echo __('Delete a link', 'monitoring');
         echo "</th>";
         echo "</tr>";

         echo "<tr>";
         echo "<td>";
         echo __('Link', 'monitoring')." :";
         echo "</td>";
         echo "<td>";
         Dropdown::showFromArray('id', $elements);
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td align='center' colspan='2'>";
         echo "<input type='submit' name='purge' value=\"".__('Delete permanently')."\" class='submit'>";
         echo "</td>";
         echo "</tr>";

         echo "</table>";
         Html::closeForm();

      echo "</td>";
      echo "</tr>";
      echo "</table>";
      echo "</div>";

      echo "</td>";
      echo "</tr>";

      echo "</table>";
   }



   function prepareInputForUpdate($input) {

      $mime = '';
      if (isset($_FILES['background']['type']) && !empty($_FILES['background']['type'])) {
         $mime = $_FILES['background']['type'];
      }
      if (isset($mime) AND !empty($mime)) {
         if ($mime == 'image/png'
                 OR $mime == 'image/x-png'
                 OR $mime == 'image/jpg'
                 OR $mime == 'image/jpeg') {

            // Upload file
            copy($_FILES['background']['tmp_name'], GLPI_PLUGIN_DOC_DIR."/monitoring/weathermapbg/".$_FILES['background']['name']);
            $input['background'] = $_FILES['background']['name'];
            unlink($_FILES['background']['tmp_name']);
         } else if (isset($input['background'])){
            unset($input['background']);
         }
      }

      return $input;
   }



   function showWidget($id, $pourcentage) {
      global $DB, $CFG_GLPI;

      $this->generateWeathermap($id);
      $imgdisplay = $CFG_GLPI['root_doc'].'/plugins/monitoring/front/send.php?file=weathermap-'.$id.'.png&date='.date('U');
      $img = GLPI_PLUGIN_DOC_DIR."/monitoring/weathermap-".$id.".png";
      if (file_exists($img)) {
         list($width, $height, $type, $attr) = getimagesize($img);
         $table_width = 950;
         $withreduced = $width;
         if ((($table_width * $pourcentage) / 100) < $width) {
            $withreduced = ceil(($table_width * $pourcentage) / 100);
         }
         return '<img src="'.$imgdisplay.'" width="'.$withreduced.'" />';
      }
   }



   function widgetEvent($id) {
      global $CFG_GLPI;

      $img = GLPI_PLUGIN_DOC_DIR."/monitoring/weathermap-".$id.".png";
      if (file_exists($img)) {
         list($width, $height, $type, $attr) = getimagesize($img);
         return "listeners: {render: function(c) {c.body.on('click', function() { window.open('".$CFG_GLPI["root_doc"]."/plugins/monitoring/front/weathermap_full.php?id=".
                                         $id."', 'weathermap', 'height=".($height + 100).", ".
                                         "width=".($width + 50).", top=100, left=100, scrollbars=yes') });}}";
      }
   }



   /**
    *
    * @param type $type ("in" or "out")
    */
   function checkBandwidth($type, $bandwidth, $bandwidthmax) {

      if ($bandwidth == '') {
         return 0;
      }

      $bdmax = $bandwidthmax;
      if (strstr($bandwidthmax, ":")) {
         $split = explode(":", $bandwidthmax);
         if ($type == 'in') {
            $bdmax= $split[0];
         } else if ($type == 'out') {
            $bdmax= $split[1];
         }
      }

      if (strstr($bdmax, "G")) {
         $bdmax = $bdmax * 1000 * 1000 * 1000;
      } else if (strstr($bdmax, "M")) {
         $bdmax = $bdmax * 1000 * 1000;
      } else if (strstr($bdmax, "K")) {
         $bdmax = $bdmax * 1000;
      }

      if ($bandwidth > ($bdmax * 1000)) {
         return "0";
      } else {
         return $bandwidth;
      }
   }


   function showToolTip($content, $options=array()) {
      global $CFG_GLPI;

      $param['applyto']    = '';
      $param['title']      = '';
      $param['contentid']  = '';
      $param['link']       = '';
      $param['linkid']     = '';
      $param['linktarget'] = '';
      $param['img']        = $CFG_GLPI["root_doc"]."/pics/aide.png";
      $param['popup']      = '';
      $param['ajax']       = '';
      $param['display']    = true;
      $param['autoclose']  = true;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $param[$key] = $val;
         }
      }

      // No empty content to have a clean display
      if (empty($content)) {
         $content = "&nbsp;";
      }
      $rand = mt_rand();
      $out  = '';

      // Force link for popup
      if (!empty($param['popup'])) {
         $param['link'] = '#';
      }

      if (empty($param['applyto'])) {
         if (!empty($param['link'])) {
            $out .= "<a id='".(!empty($param['linkid'])?$param['linkid']:"tooltiplink$rand")."'";

            if (!empty($param['linktarget'])) {
               $out .= " target='".$param['linktarget']."' ";
            }
            $out .= " href='".$param['link']."'";

            if (!empty($param['popup'])) {
               $out .= " onClick=\"var w=window.open('".$CFG_GLPI["root_doc"]."/front/popup.php?popup=".
                                                     $param['popup']."', 'glpibookmarks', 'height=400, ".
                                                     "width=600, top=100, left=100, scrollbars=yes' ); ".
                       "w.focus();\" ";
            }
            $out .= '>';
         }
         $out .= "<img id='tooltip$rand' alt='' src='".$param['img']."'>";

         if (!empty($param['link'])) {
            $out .= "</a>";
         }
         $param['applyto'] = "tooltip$rand";
      }

      if (empty($param['contentid'])) {
         $param['contentid'] = "content".$param['applyto'];
      }

      $out .= "<span id='".$param['contentid']."' class='x-hidden'>$content</span>";

      $out .= "<script type='text/javascript' >\n";

      $out .= "new Ext.ToolTip({
               target: '".$param['applyto']."',
               anchor: 'left',
               autoShow: true,
               trackMouse: true,
               ";

      if ($param['autoclose']) {
         $out .= "autoHide: true,

                  dismissDelay: 0";
      } else {
         $out .= "autoHide: false,
                  closable: true,
                  autoScroll: true";
      }

      if (!empty($param['title'])) {
         $out .= ",title: \"".$param['title']."\"";
      }
      $out .= ",contentEl: '".$param['contentid']."'";
      $out .= "});";
      $out .= "</script>";

      if ($param['display']) {
         echo $out;
      } else {
         return $out;
      }
   }



   function generateAllGraphs($weathermaps_id) {
      global $DB;

      $pmServicegraph = new PluginMonitoringServicegraph();
      $pmComponent = new PluginMonitoringComponent();

      $cache = array();

      $query = "SELECT * FROM `glpi_plugin_monitoring_weathermaplinks`
         LEFT JOIN `glpi_plugin_monitoring_weathermapnodes`
            ON `glpi_plugin_monitoring_weathermapnodes`.`id`=`plugin_monitoring_weathermapnodes_id_1`
         LEFT JOIN `glpi_plugin_monitoring_services`
            ON `glpi_plugin_monitoring_services`.`id`=`plugin_monitoring_services_id`
         WHERE `plugin_monitoring_weathermaps_id`='".$weathermaps_id."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {

         $graph_template = 0;
         if (isset($cache[$data['plugin_monitoring_components_id']])) {
            $graph_template = $cache[$data['plugin_monitoring_components_id']];
         } else {
            $pmComponent->getFromDB($data['plugin_monitoring_components_id']);
            $cache[$data['plugin_monitoring_components_id']] = $pmComponent->fields['graph_template'];
            $graph_template = $pmComponent->fields['graph_template'];
         }

         $pmServicegraph->displayGraph($graph_template,
                                       "PluginMonitoringService",
                                       $data['plugin_monitoring_services_id'],
                                       0,
                                       '2h');

      }
   }



   // functions for d3 and draw net weathermap

   function drawMap($weathermaps_id, $widthw=100, $config=0) {
      global $DB, $CFG_GLPI;

      $this->getFromDB($weathermaps_id);

      PluginMonitoringSecurity::updateSession();

      if (countElementsInTable('glpi_plugin_monitoring_weathermapnodes', "`plugin_monitoring_weathermaps_id`='".$weathermaps_id."'") == 0) {
         return;
      }

      $rand = mt_rand();
      echo '<svg id="cloud'.$rand.'" width="'.$this->fields['width'].'" '
              . 'height="'.$this->fields['height'].'">
  <defs>
    <marker id="arrowhead'.$rand.'" orient="auto" markerWidth="2" markerHeight="4"
            refX="0.3" refY="0.8">
      <path d="M0,0 V1.6 L0.8,0.8 Z" fill="#d0d0d0" />
    </marker>
    <marker id="arrowheadred'.$rand.'" orient="auto" markerWidth="2" markerHeight="4"
            refX="0.3" refY="0.8">
      <path d="M0,0 V1.6 L0.8,0.8 Z" fill="red" />
    </marker>
    <marker id="arrowheadblack'.$rand.'" orient="auto" markerWidth="2" markerHeight="4"
            refX="0.3" refY="0.8">
      <path d="M0,0 V1.6 L0.8,0.8 Z" fill="black" />
    </marker>
  </defs>
        </svg>';

      echo '<script>
      var width = '.$this->fields['width'].';
      var height = '.$this->fields['height'].';

      var color = d3.scale.category10();

      var force'.$rand.' = d3.layout.force()
          .charge(-180)
          .linkDistance(20)
          .size([width, height]);

      var svg'.$rand.' = d3.select("#cloud'.$rand.'");

      var drag_node = d3.behavior.drag()
        .on("drag", dragmove)';
      if ($config) {
         echo '
        .on("dragend", dragendconfig)';
      }

              echo ';


      function dragmove(d, i) {
        d.px += d3.event.dx;
        d.py += d3.event.dy;
        d.x += d3.event.dx;
        d.y += d3.event.dy;
        tick'.$rand.'(); // this is the key to make it work together with updating both px,py,x,y on d !
     }

     function dragendconfig(d, i) {
        $.ajax({type: "POST",url: "'.$CFG_GLPI['root_doc'].'/plugins/monitoring/ajax/updateWeathermap.php",data: {id: d.id, x: d.x, y: d.y},success: function(msg) {}});
     }
              ';


      $a_data = array();
      $a_mapping = array();
      $i = 0;
      $query = "SELECT * FROM `".getTableForItemType("PluginMonitoringWeathermapnode")."`
         WHERE `plugin_monitoring_weathermaps_id`='".$weathermaps_id."'
         ORDER BY `name`";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $name = $data['name'];
         $url = '';
         if ($name == '') {
            $itemtype = $data['itemtype'];
            $item = new $itemtype();
            $item->getFromDB($data['items_id']);
            $name = $item->getName();
            $url = $item->getLinkURL();
         }
         $a_mapping[$data['id']] = $i;
         $i++;
         $a_textx = array(
             'middle' => 0,
             'start'  => '12',
             'end'    => '-12');
         $texty = 0;
         if ($data['position'] == 'middle') {
            $texty = -13;
         }
         $a_data['nodes'][] = array(
             'name'  => $name,
             'id'    => (int)$data['id'],
             'x'     => ($widthw * $data['x']) / 100,
             'y'     => ($widthw * $data['y']) / 100,
             'fixed' => TRUE,
             "group" => 3,
             "url"   => $url,
             "textposition" => $data['position'],
             "textx" => $a_textx[$data['position']],
             "texty" => $texty,
             "nodeusage" => 'grey'
         );
      }
      $nodes_upusage = array();

      $pmWeathermapnode = new PluginMonitoringWeathermapnode();
      $pmWeathermaplink = new PluginMonitoringWeathermaplink();
      $pmService = new PluginMonitoringService();
      $pmComponent = new PluginMonitoringComponent();
      $a_data['links'] = array();
      $query = "SELECT `glpi_plugin_monitoring_weathermaplinks`.*
            FROM `glpi_plugin_monitoring_weathermaplinks`
         LEFT JOIN `glpi_plugin_monitoring_weathermapnodes`
            ON `plugin_monitoring_weathermapnodes_id_1` = `glpi_plugin_monitoring_weathermapnodes`.`id`
         WHERE `plugin_monitoring_weathermaps_id`='".$weathermaps_id."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $pmWeathermapnode->getFromDB($data['plugin_monitoring_weathermapnodes_id_2']);

         if (!$pmService->getFromDB($data['plugin_monitoring_services_id'])) {
            $pmWeathermapnode = new PluginMonitoringWeathermapnode();
            $pmWeathermapnode->getFromDB($data['plugin_monitoring_weathermapnodes_id_1']);
            $querytt = "SELECT glpi_plugin_monitoring_services.id FROM `glpi_plugin_monitoring_services`
               LEFT JOIN glpi_plugin_monitoring_componentscatalogs_hosts
                  ON plugin_monitoring_componentscatalogs_hosts_id=glpi_plugin_monitoring_componentscatalogs_hosts.id
               WHERE  networkports_id>0
                  AND itemtype='".$pmWeathermapnode->fields['itemtype']."'
                  AND items_id='".$pmWeathermapnode->fields['items_id']."'";
            $resulttt = $DB->query($querytt);
            $s_id = 0;
            if ($DB->numrows($resulttt) == 1) {
               $datatt = $DB->fetch_assoc($resulttt);
               $input = array(
                   'id'                            => $data['id'],
                   'plugin_monitoring_services_id' => $datatt['id']
               );
               $pmWeathermaplink->update($input);
               $pmWeathermaplink->getFromDB($data['id']);
               $data = $pmWeathermaplink->fields;
            }
         }

         $queryevent = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
            WHERE `plugin_monitoring_services_id`='".$data['plugin_monitoring_services_id']."'
               ORDER BY `date` DESC
               LIMIT 1";
         $resultevent = $DB->query($queryevent);
         $in = '';
         $out = '';
         $service_exist = 0;
         while ($dataevent=$DB->fetch_array($resultevent)) {
            if ($pmService->getFromDB($data['plugin_monitoring_services_id'])) {
               $pmComponent->getFromDB($pmService->fields['plugin_monitoring_components_id']);

               $matches1 = array();
               preg_match("/".$pmComponent->fields['weathermap_regex_in']."/m", $dataevent['perf_data'], $matches1);
               if (isset($matches1[1])) {
                  $in = $matches1[1];
               }
               $matches1 = array();
               preg_match("/".$pmComponent->fields['weathermap_regex_out']."/m", $dataevent['perf_data'], $matches1);
               if (isset($matches1[1])) {
                  $out = $matches1[1];
               }
               $service_exist = 1;
            } else {
               $pmService->getEmpty();
               $pmComponent->getEmpty();
            }
         }
         if ($service_exist) {
            list($downusage, $downcolor) = $this->getWBandwidth($in, $data['bandwidth_in']);
            list($upusage, $upcolor) = $this->getWBandwidth($out, $data['bandwidth_out']);
         } else {
            $upusage = 100;
            $downusage = 100;
            $upcolor = 'black';
            $downcolor = 'black';
         }
         $a_data['links'][] = array(
             'source'    => $a_mapping[$data['plugin_monitoring_weathermapnodes_id_1']],
             'target'    => $a_mapping[$data['plugin_monitoring_weathermapnodes_id_2']],
             'up'        => $upcolor,
             'down'      => $downcolor,
             'upusage'   => $upusage,
             'downusage' => $downusage,
             'info'      => '',
             'value'     => 1,
             'services_id' => $data['plugin_monitoring_services_id'],
             'components_id' => $pmService->fields['plugin_monitoring_components_id'],
             'rrdtool_template' => $pmComponent->fields['graph_template']
         );
         if (!isset($nodes_upusage[$a_mapping[$data['plugin_monitoring_weathermapnodes_id_1']]])) {
            $nodes_upusage[$a_mapping[$data['plugin_monitoring_weathermapnodes_id_1']]] = array();
         }
         if (!isset($nodes_upusage[$a_mapping[$data['plugin_monitoring_weathermapnodes_id_2']]])) {
            $nodes_upusage[$a_mapping[$data['plugin_monitoring_weathermapnodes_id_2']]] = array();
         }
         array_push($nodes_upusage[$a_mapping[$data['plugin_monitoring_weathermapnodes_id_1']]], $upusage);
         array_push($nodes_upusage[$a_mapping[$data['plugin_monitoring_weathermapnodes_id_2']]], $downusage);
      }

      foreach ($nodes_upusage as $nodes_num=>$datausage) {
         $moyusage = array_sum($datausage)/count($datausage);
         list($usage, $color) = $this->getWBandwidth(array_sum($datausage)/count($datausage), 100);
         if ($moyusage == 0) {
            $color = 'grey';
         }
         $a_data['nodes'][$nodes_num]['nodeusage'] = $color;
      }

      echo 'var jsonstr'.$rand.' = \''.json_encode($a_data).'\';';
      echo 'var json'.$rand.' = JSON.parse(jsonstr'.$rand.');
      force'.$rand.'
        .nodes(json'.$rand.'.nodes)
        .links(json'.$rand.'.links)
        .start();

     ';

      $this->d3jsLink('up', 'usage', $rand);
      $this->d3jsLink('up', 'notusage', $rand);
      $this->d3jsLink('down', 'usage', $rand);
      $this->d3jsLink('down', 'notusage', $rand);

      echo '    var nodes'.$rand.' = svg'.$rand.'.selectAll(".node")
        .data(force'.$rand.'.nodes())
      .enter().append("g")
        .attr("class", "node")
        .call(drag_node)
      .append("a")
        .attr("xlink:href", function (d) { return d.url; })
        .attr("target", "_blank");

//      .on("dragend", function(d){$.ajax({url: "toto",success: function(data) {}})});
//d3.select(this).style("fill", "white");}


    nodes'.$rand.'.append("circle")
       .attr("r", 5)
       .attr("class", function(d) { return "circle" + d.nodeusage; });


    nodes'.$rand.'.append("text")
       .attr("text-anchor", function(d) { return d.textposition; })
       .attr("x", function(d) { return d.textx; })
       .attr("y", function(d) { return d.texty; })
       .attr("dy", ".35em")
       .attr("class", "linklabel")
      .text(function(d) { return d.name; });

   var textdown'.$rand.' = svg'.$rand.'.selectAll("line.link")
       .data(force'.$rand.'.links())
    .enter().append("text")
       .attr("dy", ".25em")
       .attr("text-anchor", "middle")
       .style("pointer-events", "none")
       .attr("class", function(d) { return "linklabel" + d.down;})
       .text(function(d) { return d.downusage + "%";})

   var textup'.$rand.' = svg'.$rand.'.selectAll("line.link")
       .data(force'.$rand.'.links())
    .enter().append("text")
       .attr("dy", ".25em")
       .attr("text-anchor", "middle")
       .style("pointer-events", "none")
       .attr("class", function(d) { return "linklabel" + d.up;})
       .text(function(d) { return d.upusage + "%";})

    force'.$rand.'.on("tick", tick'.$rand.');

    function tick'.$rand.'() {
        linksupusage'.$rand.'.attr("x1", function(d) { return d.source.x; })
            .attr("y1", function(d) { return d.source.y; })
            .attr("x2", function(d) { return d.source.x + (((d.target.x - d.source.x) / 200) * ((d.upusage * 97) / 100)); })
            .attr("y2", function(d) { return d.source.y + (((d.target.y - d.source.y) / 200) * ((d.upusage * 97) / 100)); });

        linksupnotusage'.$rand.'.attr("x1", function(d) { return d.source.x + (((d.target.x - d.source.x) / 200) * ((d.upusage * 97) / 100)); })
            .attr("y1", function(d) { return d.source.y + (((d.target.y - d.source.y) / 200) * ((d.upusage * 97) / 100)); })
            .attr("x2", function(d) { return d.source.x + (((d.target.x - d.source.x) / 200) * 97); })
            .attr("y2", function(d) { return d.source.y + (((d.target.y - d.source.y) / 200) * 97); });

        linksdownusage'.$rand.'.attr("x1", function(d) { return d.target.x; })
            .attr("y1", function(d) { return d.target.y; })
            .attr("x2", function(d) { return d.source.x + Math.round(((d.target.x - d.source.x) / 200) * (200 - ((d.downusage * 97) / 100))); })
            .attr("y2", function(d) { return d.source.y + Math.round(((d.target.y - d.source.y) / 200) * (200 - ((d.downusage * 97) / 100))); });

        linksdownnotusage'.$rand.'.attr("x1", function(d) { return d.source.x + Math.round(((d.target.x - d.source.x) / 200) * (200 - ((d.downusage * 97) / 100))); })
            .attr("y1", function(d) { return d.source.y + Math.round(((d.target.y - d.source.y) / 200) * (200 - ((d.downusage * 97) / 100))); })
            .attr("x2", function(d) { return d.source.x + Math.round(((d.target.x - d.source.x) / 200) * 103); })
            .attr("y2", function(d) { return d.source.y + Math.round(((d.target.y - d.source.y) / 200) * 103); });

        nodes'.$rand.'
            .attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
';

         echo '
   textdown'.$rand.'.attr("x", function(d) { return d.source.x + (((d.target.x - d.source.x) / 200) * 130); })
      .attr("y", function(d) { return d.source.y + (((d.target.y - d.source.y) / 200) * 130); });


   textup'.$rand.'.attr("x", function(d) { return d.source.x + (((d.target.x - d.source.x) / 200) * 70); })
      .attr("y", function(d) { return d.source.y + (((d.target.y - d.source.y) / 200) * 70); });

     };
';

echo '        </script>';


   }



   function d3jsLink($updown, $type, $rand) {
      global $CFG_GLPI;

      $linkcolor = '" + d.'.$updown;
      if ($type == 'notusage') {
         $linkcolor = 'grey"';
      }
      echo 'var links'.$updown.$type.$rand.' = svg'.$rand.'.append("g").selectAll("line.link")
        .data(force'.$rand.'.links())
        .enter().append("line")
        .attr("class", function(d) { return "link'.$linkcolor.'; })';
      if ($type == 'notusage') {
        echo '
         .attr("marker-end", function(d) { if (d.'.$updown.'usage < 100) {return "url(#arrowhead'.$rand.')";}})';
      } else {
        echo '
         .attr("marker-end", function(d) { if (d.'.$updown.'usage == 100) {return "url(#arrowhead" + d.'.$updown.' + "'.$rand.')";}})';
      }
         echo ';

         $("line").tipsy({
         gravity: "w",
         offset: 30,
         opacity: 0.97,
         delayIn: 1,
        delayOut: 3,
        fade: true,
         hoverlock: true,
         html: true,
         title: function () {
           var d = this.__data__;
           return "<div id=\'chart" + d.services_id + "2h'.$updown.$type.$rand.'\'>'.
                      '<svg style=\'height: 300px; width: 450px;\'></"+"svg>'.
                    '</"+"div><div id=\'updategraph" + d.services_id + "2h'.$updown.$type.$rand.'\'></"+"div>'.
            '<script>$.ajax({'.

                 'type: \'post\','.
                 'url: \''.$CFG_GLPI['root_doc'].'/plugins/monitoring/ajax/updateChart.php\','.
                 'data: { rrdtool_template:" + d.rrdtool_template + ",itemtype:\'PluginMonitoringService\',items_id:" + d.services_id + ",timezone:0,time:\'2h\',suffix:\''.$updown.$type.$rand.'\',customdate:\'\',customtime:\'\',components_id:" + d.components_id + ",sess_id:\''.session_id().'\',glpiID:\''.$_SESSION['glpiID'].'\',plugin_monitoring_securekey:\''.$_SESSION['plugin_monitoring_securekey'].'\' },'.
                 'success: function(data) {'.
                 '     $(\'#updategraph" + d.services_id + "2h'.$updown.$type.$rand.'\').html(data);'.
                 '}'.
               '});</"+"script>";
          },
      });
      ';
   }



   function getWBandwidth($bp_current, $bp_max) {

      if (strstr($bp_max, "G")) {
         $bp_max = $bp_max * 1000 * 1000 * 1000;
      } else if (strstr($bp_max, "M")) {
         $bp_max = $bp_max * 1000 * 1000;
      } else if (strstr($bp_max, "K")) {
         $bp_max = $bp_max * 1000;
      }

      $percent = 0;
      if ($bp_max != 0) {
         $percent = ceil(($bp_current * 100) / $bp_max);
      }
      $color = 'green';
      if ($percent > 80) {
      $color = 'red';
      } else if ($percent > 60) {
      $color = 'orange';
      }
      if ($percent > 100) {
         $percent = 100;
      }
      return array($percent, $color);
   }
}

?>