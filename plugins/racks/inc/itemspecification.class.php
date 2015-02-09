<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Racks plugin for GLPI
 Copyright (C) 2003-2011 by the Racks Development Team.

 https://forge.indepnet.net/projects/racks
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Racks.

 Racks is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Racks is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Racks. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginRacksItemSpecification extends CommonDBTM {

   static $rightname = "plugin_racks_model";
   
   static function getTypeName($nb=0) {
      return __('Equipments models specifications', 'racks');
   }

   function defineTabs($options=array()) {
      $tabs = array();
      $this->addStandardTab(__CLASS__, $tabs, $options);
      return $tabs;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if (!$withtemplate) {
         if (in_array($item->getType(), self::getModelClasses(true))
                    && $this->canView()) {
            return __('Specifications', 'racks');
         }
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;
      $self = new self();
      if (in_array($item->getType(), self::getModelClasses(true))) {
         $self->showForm("", array('items_id' => $item->getID(),
                                   'itemtype' => get_class($item)));
      }
      return true;
   }

   function checkAlimNumber($id) {
      global $DB;

      foreach ($DB->request('glpi_plugin_racks_racks_items', array('id' => $id)) as $model) { 
         $result = $DB->query("SELECT nb_alim 
                               FROM `".$this->getTable()."`
                               WHERE `id` = '" . $model['plugin_racks_itemspecifications_id'] . "' ");
         if ($DB->numrows($result) > 0) {
           return $DB->result($result, 0, "nb_alim");
         } else {
           return 0;
         }
      }
   }

   function checkIfSpecUsedByRacks($id) {
      return (countElementsInTable('glpi_plugin_racks_racks_items', 
                                   "`plugin_racks_itemspecifications_id` = '" . $id . "'") > 0);
   }

   static function getModelClasses () {
      static $types = array('ComputerModel', 
                            'NetworkEquipmentModel', 
                            'PeripheralModel', 
                            'PluginRacksOtherModel');
      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }
         if (!$type::canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

   function UpdateItemSpecification($input) {
      global $DB;

      $modelfield = getForeignKeyFieldForTable(getTableForItemType($input['itemtype']));
      $itemtype   = substr($input['itemtype'], 0, -5);
      $table      = getTableForItemType($itemtype);

      //selection de tous les materiels lies au modele
      $query_spec = "SELECT *
                     FROM `".$this->getTable()."`
                     WHERE `id` = '" . $input["id"] . "' ";

      foreach ($DB->request($this->getTable(), array ('id' => $input['id'])) as $device) {
         $query_device = "SELECT `" . $table . "`.`id` 
                          FROM `" . $table . "`, `".$this->getTable()."` 
                          WHERE `".$this->getTable()."`.`model_id` = `" . $table . "`.`".$modelfield."`
                            AND `".$this->getTable()."`.`id` = '" . $input["id"] . "'";

         foreach ($DB->request($query_device) as $mode) {

            //detail de chaque materiel dans la baie
            $query_content = "SELECT * 
                              FROM `glpi_plugin_racks_racks_items`
                              WHERE `itemtype` = '" . $input['itemtype'] . "'
                                 AND `items_id` = '" . $model['id'] . "' ";
            foreach ($DB->request($query_content) as $content) {
               if ($device["amps"] == $content["amps"]
                  && $device["flow_rate"] == $content["flow_rate"]
                     && $device["dissipation"] == $content["dissipation"]
                        && $device["weight"] == $content["weight"]) {

                  //si les params du materiel sont les memes que le modele alors update
                  $PluginRacksRack_Item  = new PluginRacksRack_Item();
                  $values["id"]          = $content['id'];
                  $values["amps"]        = $input["amps"];
                  $values["flow_rate"]   = $input["flow_rate"];
                  $values["dissipation"] = $input["dissipation"];
                  $values["weight"]      = $input["weight"];
                  $PluginRacksRack_Item->update($values);

               }
            }
         }
      }
      $this->update($input);
   }

   function deleteItemSpecification($ID) {
      global $DB;

      $query_spec = "SELECT *
            FROM `".$this->getTable()."`
            WHERE `id` = '" . $ID . "' ";
      $result_spec = $DB->query($query_spec);

      while($device=$DB->fetch_array($result_spec)) {
         $itemtype=$device['itemtype'];

         $modelfield = getForeignKeyFieldForTable(getTableForItemType($itemtype));
         $table = getTableForItemType(substr($itemtype, 0, -5));

         //delete items from racks
         $query_device = "SELECT `" . $table . "`.`id` FROM `" . $table . "`, `".$this->getTable()."` " .
                "WHERE `".$this->getTable()."`.`model_id` = `" . $table . "`.`".$modelfield."`
                AND `".$this->getTable()."`.`id` = '" . $ID . "'";
         $result_device = $DB->query($query_device);
         while($model=$DB->fetch_array($result_device)) {
            $query = "DELETE
                FROM `glpi_plugin_racks_racks_items`
                WHERE `itemtype` = '" . $itemtype . "'
                AND `items_id` ='" . $model['id'] . "';";
            $result = $DB->query($query);
         }
      }
      $this->delete(array("id"=>$ID));
   }

   function getFromDBByModel($itemtype,$id) {
                global $DB;

                $query = "SELECT * FROM `".$this->getTable()."`
                                        WHERE `itemtype` = '$itemtype'
                                        AND `model_id` = '$id' ";
                if ($result = $DB->query($query)) {
                        if ($DB->numrows($result) != 1) {
                                return false;
                        }
                        $this->fields = $DB->fetch_assoc($result);
                        if (is_array($this->fields) && count($this->fields)) {
                                return true;
                        } else {
                                return false;
                        }
                }
                return false;
        }


   function showForm ($ID, $options=array()) {

      if (!$this->canView()) {
         return false;
      }

      $itemtype = -1;
      if (isset($options['itemtype'])) {
         $itemtype = $options['itemtype'];
      }

      $items_id = -1;
      if (isset($options['items_id'])) {
         $items_id = $options['items_id'];
      }

      if($this->getFromDBByModel($itemtype,$items_id))
         $ID = $this->fields["id"];

                if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         // Create item
         $this->check(-1, UPDATE ,$input);
      }
      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      if ($ID > 0) {
         echo "<input type='hidden' name='itemtype' value='".$this->fields["itemtype"]."'>";
         echo "<input type='hidden' name='model_id' value='".$this->fields["model_id"]."'>";
      } else {
         echo "<input type='hidden' name='itemtype' value='$itemtype'>";
         echo "<input type='hidden' name='model_id' value='$items_id'>";
      }
      $PluginRacksConfig = new PluginRacksConfig();

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Size') . "</td>";
      echo "<td>";
      if ($this->checkIfSpecUsedByRacks($ID))
         echo $this->fields["size"];
      else
         Dropdown::showInteger("size", $this->fields["size"], 1, 100, 1);
      echo " U</td>";

      echo "<td>" . __('Full-depth item', 'racks') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("length",$this->fields["length"]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Power supplies number', 'racks') . "</td>";
      echo "<td>";
      Dropdown::showInteger("nb_alim", $this->fields["nb_alim"], 0, 2, 1);
      echo "</td>";

      echo "<td>" . __('Total Current', 'racks') . "</td>";
      echo "<td>";
      echo "<input type='text' name='amps' value=\"".Html::formatNumber($this->fields["amps"],true)."\" size='10'>  (".__('amps');
      echo ")</td>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Calorific waste', 'racks'); // Dissipation calorifique
      echo "</td>";
      echo "<td>";
      echo "<input type='text' name='dissipation' value=\"".Html::formatNumber($this->fields["dissipation"],true)."\" size='10'> (";
      $PluginRacksConfig->getUnit("dissipation");
      echo ")</td>";

      echo "<td>".__('Flow Rate', 'racks'); // Débit d'air frais
      echo "</td>";
      echo "<td>";
      echo "<input type='text' name='flow_rate' value=\"".Html::formatNumber($this->fields["flow_rate"],true)."\" size='10'> (";
      $PluginRacksConfig->getUnit("rate");
      echo ")</td>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Weight', 'racks'); // poids
      echo "</td>";
      echo "<td>";
      echo "<input type='text' name='weight' value=\"".Html::formatNumber($this->fields["weight"],true)."\" size='10'> (";
      $PluginRacksConfig->getUnit("weight");
      echo ")</td>";

      echo "<td></td>";
      echo "<td></td>";

      echo "</tr>";

      $this->showFormButtons($options);
   }

   function showList($target,$id,$itemtype,$withtemplate='') {
      $rand = mt_rand();
      echo "<div align='center'>";
      echo "<form method='post' 
                  name='massiveaction_form$rand' 
                  id='massiveaction_form$rand'  
                  action=\"$target\">";
      $this->showModels($itemtype,$id,$rand);
   }

        function showModels($itemtype,$id,$rand) {
                global $DB;

      $PluginRacksConfig = new PluginRacksConfig();

      $link = Toolbox::getItemTypeFormURL($itemtype);
      $table = getTableForItemType($itemtype);
      $search = Toolbox::getItemTypeSearchURL($itemtype);
      echo "<table class='tab_cadre_fixe' cellpadding='5'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th>&nbsp;</th>";
      echo "<th>" . __('Equipment', 'racks') . "</th>";
      echo "<th>" . __('Total Current', 'racks') . "<br>(".__('amps', 'racks').")</th>";
      echo "<th>" . __('Power supplies number', 'racks') . "</th>";
      echo "<th>".__('Calorific waste', 'racks')."<br> ("; // Dissipation calorifique
      $PluginRacksConfig->getUnit("dissipation");
      echo ")</th>";
      echo "<th>".__('Flow Rate', 'racks')."<br> ("; // Débit d'air frais
      $PluginRacksConfig->getUnit("rate");
      echo ")</th>";
      echo "<th>" . __('Size') . " (".__('U', 'racks').")</th>";
      echo "<th>".__('Weight', 'racks')."<br> ("; // poids
      $PluginRacksConfig->getUnit("weight");
      echo ")</th>";
      echo "<th>" . __('Full-depth item', 'racks') . "</th>";
      echo "</tr>";
      $modelid=-1;
      $result = $DB->query("SELECT *
                        FROM `".$this->getTable()."` ".($itemtype != -1?"WHERE `itemtype` = '$itemtype'":"")." ");
      while ($data = $DB->fetch_assoc($result)) {
         $modelid = $data['model_id'];
         $id=$data['id'];
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center'>";
         echo "<input type='checkbox' name='item[$id]' value='1'>";
         echo "</td>";
                        echo "<td>";
         echo "<a href=\"".$link."?id=".$modelid."\">";
         echo Dropdown::getDropdownName($table,$modelid);
         echo "</a>";
                        echo "</td>";
         echo "<td>" . Html::formatNumber($data['amps'],true) . "</td>";
         echo "<td>" . $data['nb_alim'] . "</td>";
         echo "<td>" . Html::formatNumber($data['dissipation'],true) . "</td>";
         echo "<td>" . Html::formatNumber($data['flow_rate'],true) . "</td>";
         echo "<td>" . $data['size'] . "</td>";
         echo "<td>" . Html::formatNumber($data['weight'],true) . "</td>";
         echo "<td>" . Dropdown::getYesNo($data['length']) . "</td>";
      }

      echo "<tr class='tab_bg_1'><td colspan='10'>";
      if ($this->canCreate()) {
         echo "<div align='center'><a onclick= \"if ( markCheckboxes('massiveaction_form$rand') ) return false;\" href='#'>" . __('Check all') . "</a>";
         echo " - <a onclick= \"if ( unMarkCheckboxes('massiveaction_form$rand') ) return false;\" href='#'>" . __('Uncheck all') . "</a> ";
         echo "<input type='submit' name='deleteSpec' value=\"" . __s('Delete permanently') . "\" class='submit' ></div></td></tr>";

         echo "<tr class='tab_bg_1 right'><td colspan='10'>";
         echo "<a href=\"".$search."\">";
         echo __('Add specifications for servers models', 'racks');
         echo "</a>";
         echo "</td></tr>";
      }
      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }
   
   function getRights($interface='central') {

      $values = parent::getRights();

      unset($values[READNOTE], $values[UPDATENOTE], $values[DELETE]);
      return $values;
   }
}
?>