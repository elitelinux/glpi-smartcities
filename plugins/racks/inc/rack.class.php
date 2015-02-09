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

class PluginRacksRack extends CommonDBTM {

   static $rightname = "plugin_racks";
   
   static $types     = array('Computer', 
                             'NetworkEquipment',
                             'Peripheral');
   public $dohistory = true;
   protected $usenotepadrights         = true;

   const FRONT_FACE = 1;
   const BACK_FACE  = 2;

   static function getTypeName($nb=0) {
      return _n('Rack enclosure management', 
                'Rack enclosures management', 
                $nb, 'racks');
   }

   function cleanDBonPurge() {
      $temp = new PluginRacksRack_Item();
      $temp->deleteByCriteria(array('plugin_racks_racks_id' => $this->fields['id']));
   }

   function getSearchOptions() {
      $tab                       = array();
      $tab['common']             = self::getTypeName(2);

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();

      $tab[3]['table']           = 'glpi_locations';
      $tab[3]['field']           = 'completename';
      $tab[3]['name']            = __('Location');
      $tab[3]['datatype']        = 'dropdown';

      $tab[2]['table']           = 'glpi_plugin_racks_roomlocations';
      $tab[2]['field']           = 'completename';
      $tab[2]['name']            = __('Place', 'racks');
      $tab[2]['datatype']        = 'dropdown';
      
      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'rack_size';
      $tab[4]['name']            = __('Size');
      $tab[4]['datatype']        = 'number';

      $tab[5]['table']           = 'glpi_manufacturers';
      $tab[5]['field']           = 'name';
      $tab[5]['name']            = __('Manufacturer');
      $tab[5]['datatype']        = 'dropdown';

      $tab[6]['table']           = 'glpi_groups';
      $tab[6]['field']           = 'completename';
      $tab[6]['linkfield']       = 'groups_id_tech';
      $tab[6]['name']            = __('Group in charge of the hardware');
      $tab[6]['condition']       = '`is_assign`';
      $tab[6]['datatype']        = 'dropdown';

      $tab[7]['table']           = 'glpi_users';
      $tab[7]['field']           = 'name';
      $tab[7]['linkfield']       = 'users_id_tech';
      $tab[7]['name']            = __('Technician in charge of the hardware');
      $tab[7]['datatype']        = 'dropdown';
      $tab[7]['right']           = 'interface';

      $tab[8]['table']           = $this->getTable();
      $tab[8]['field']           = 'height';
      $tab[8]['name']            = __('Height', 'racks');
      $tab[8]['datatype']        = 'decimal';

      $tab[9]['table']           = $this->getTable();
      $tab[9]['field']           = 'width';
      $tab[9]['name']            = __('Width', 'racks');
      $tab[9]['datatype']        = 'decimal';

      $tab[10]['table']          = $this->getTable();
      $tab[10]['field']          = 'depth';
      $tab[10]['name']           = __('Depth', 'racks');
      $tab[10]['datatype']       = 'decimal';

      $tab[11]['table']          = $this->getTable();
      $tab[11]['field']          = 'is_recursive';
      $tab[11]['name']           = __('Child entities');
      $tab[11]['datatype']       = 'bool';

      $tab[12]['table']          = $this->getTable();
      $tab[12]['field']          = 'serial';
      $tab[12]['name']           = __('Serial number');

      $tab[13]['table']          = $this->getTable();
      $tab[13]['field']          = 'otherserial';
      $tab[13]['name']           = __('Inventory number');
      $tab[13]['datatype']       = 'string';

      $tab[14]['table']          = 'glpi_plugin_racks_racktypes';
      $tab[14]['field']          = 'name';
      $tab[14]['name']           = __('Type');
      $tab[14]['datatype']       = 'dropdown';

      $tab[15]['table']          = 'glpi_plugin_racks_rackstates';
      $tab[15]['field']          = 'name';
      $tab[15]['name']           = __('State');
      $tab[15]['datatype']       = 'dropdown';
      
      $tab[30]['table']          = $this->getTable();
      $tab[30]['field']          = 'id';
      $tab[30]['name']           = __('ID');
      $tab[30]['datatype']       = 'number';

      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['datatype']       = 'dropdown';

      return $tab;
   }
/*
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if (!$withtemplate) {
         if ($item->getType() == 'PluginRacksRack') {
            return self::getTypeName(1);
         }
      }
      return '';
   }
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      $rack = new self();
      $rack->showForm($)
      $self = new self();
      if ($item->getType() == 'PluginRacksRack' && $tabnum == 0) {
         $self->showTotal($item->getField('id'));
      }
      return true;
   } */

   function defineTabs($options=array()) {
      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab('PluginRacksRack_Item', $ong, $options);
      $this->addStandardTab('Infocom', $ong, $options);
      $this->addStandardTab('Document_Item',$ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Notepad',$ong, $options);
      $this->addStandardTab('Log',$ong, $options);

      return $ong;
   }

   function prepareInputForAdd($input) {
      if (isset($input["id"]) 
         && $input["id"] > 0) {
         $input["_oldID"] = $input["id"];
      }
      unset($input['withtemplate']);
      unset($input['id']);

      return $input;
   }

   function post_addItem() {
      // Manage add from template
      if (isset($this->input["_oldID"])) {
         // ADD Documents
         Document_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);
         // ADD Infocoms
         Infocom::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);
      }
   }

   function showForm($ID, $options = array()) {
      $PluginRacksConfig = new PluginRacksConfig();
      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      //ligne 1
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Name') . "</td><td>";
      $objectName = autoName($this->fields["name"], "name",
                             (isset($options['withtemplate']) 
                                && ( $options['withtemplate']== 2)),
                              $this->getType(), 
                              $this->fields["entities_id"]);
      Html::autocompletionTextField($this, 'name', array('value' => $objectName));
      echo "</td>";

      echo "<td>" . __('Size') . "</td><td>";
      Dropdown::showInteger("rack_size", $this->fields["rack_size"], 
                             1, 100, 1);
      echo " ".__('U', 'racks')."</td>";
      echo "</tr>";

      //ligne 2
      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Manufacturer') . "</td><td>";
      Manufacturer::dropdown(array('value' => $this->fields["manufacturers_id"]));
      echo "</td>";

      echo "<td >" . __('Location') . "</td>";
      echo "<td>";
      Location::dropdown(array('value'  => $this->fields["locations_id"],
                               'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__('Technician in charge of the hardware')."</td>";
      echo "<td >";
      User::dropdown(array('name'   => 'users_id_tech',
                           'value'  => $this->fields["users_id_tech"],
                           'right'  => 'interface',
                           'entity' => $this->fields["entities_id"]));
      echo "</td>";

      echo "<td>" . __('Place', 'racks');
      echo "</td><td>";
      $PluginRacksRoomLocation = new PluginRacksRoomLocation();
      $PluginRacksRoomLocation->dropdownRoomLocations("plugin_racks_roomlocations_id",
                                                      $this->fields["plugin_racks_roomlocations_id"],
                                                      $this->fields["entities_id"]);
      echo "</td>";

      echo "</tr>";

      //ligne 4
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Group in charge of the hardware')."</td><td>";
      Group::dropdown(array('name'      => 'groups_id_tech',
                            'value'     => $this->fields['groups_id_tech'],
                            'entity'    => $this->fields['entities_id'],
                            'condition' => '`is_assign`'));
      echo "</td>";

      echo "<td>" . __('Width', 'racks') . "</td><td>";
      echo "<input type='text' name='width' 
                              value=\"".Html::formatNumber($this->fields["width"],true)."\" size='10'> ";
      $PluginRacksConfig->getUnit("size");
      echo "</td>";

      echo "</tr>";

      //ligne 5
      echo "<tr class='tab_bg_1'>";

      echo "</td>";
      echo "<td>".__('Serial number')."</td>";
      echo "<td >";
      Html::autocompletionTextField($this,'serial');
      echo "</td>";

      echo "<td>" . __('Height', 'racks') . "</td><td>";
      echo "<input type='text' name='height' 
                              value=\"".Html::formatNumber($this->fields["height"],true)."\" size='10'> ";
      $PluginRacksConfig->getUnit("size");
      echo "</td>";

      echo "</tr>";

      //ligne 6
      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Inventory number')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,'otherserial');
      echo "</td>";

      echo "<td>" . __('Weight', 'racks') . "</td><td>";
      echo "<input type='text' name='weight' 
                              value=\"".Html::formatNumber($this->fields["weight"],true)."\" size='10'> ";
      $PluginRacksConfig->getUnit("weight");
      echo "</td>";

      echo "</tr>";

      //ligne 7
      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Model')."</td>";
      echo "<td>";
      Dropdown::show('PluginRacksRackModel', 
                     array('name'  => "plugin_racks_rackmodels_id",
                           'value' => $this->fields["plugin_racks_rackmodels_id"]));
      echo "</td>";

      echo "<td>" . __('Depth', 'racks') . "</td><td>";
      echo "<input type='text' name='depth' 
                              value=\"".Html::formatNumber($this->fields["depth"],true)."\" size='10'> ";
      $PluginRacksConfig->getUnit("size");
      echo "</td>";

      echo "</tr>";

      //ligne 8
      echo "<tr class='tab_bg_1'>";

      echo "<td >" . __('Type') . "</td><td>";
      Dropdown::show('PluginRacksRackType',
                     array('value'  => $this->fields["plugin_racks_racktypes_id"]));
      echo "</td>";

      echo "<td >" . __('Status') . "</td><td>";
      Dropdown::show('PluginRacksRackState',
                     array('value'  => $this->fields["plugin_racks_rackstates_id"]));
      echo "</td>";

      echo "</tr>";
      //ligne 9

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2'>";
      if ((!isset($options['withtemplate']) 
         || ($options['withtemplate'] == 0))
            && !empty($this->fields['template_name'])) {
         echo "<span class='small_space'>";
         printf(__('Created from the template %s'), $this->fields['template_name']);
         echo "</span>";
      } else {
         echo "&nbsp;";
      }
      echo "</td><td colspan='2'>";
      if (isset($options['withtemplate']) && $options['withtemplate']) {
         //TRANS: %s is the datetime of insertion
         printf(__('Created on %s'), Html::convDateTime($_SESSION["glpi_currenttime"]));
      } else {
         //TRANS: %s is the datetime of update
         printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      }
      echo "</td></tr>\n";

      $this->showFormButtons($options);

      if (!$this->isNewID($this->getID())) {
         $this->showTotal();
      }
      return true;
   }

   function showTotal() {
      global $DB;

      $ID = $this->getID();

      $PluginRacksConfig = new PluginRacksConfig();

      $query = "SELECT SUM(`weight`) AS total_weight, SUM(`amps`) AS total_amps,
                     SUM(`flow_rate`) AS total_flow_rate,
                     SUM(`dissipation`) AS total_dissipation,
                     COUNT(`first_powersupply`) AS total_alim1,
                     COUNT(`second_powersupply`) AS total_alim2
               FROM `glpi_plugin_racks_racks_items`
               WHERE `plugin_racks_racks_id` = '$ID' " ;

      $query_alim1 = "SELECT COUNT(`first_powersupply`) AS total_alim1
                      FROM `glpi_plugin_racks_racks_items`
                      WHERE `plugin_racks_racks_id` = '$ID' 
                        AND `first_powersupply` > 0 ";
      $result_alim1 = $DB->query($query_alim1);

      $query_alim2 = "SELECT COUNT(`second_powersupply`) AS total_alim2
                      FROM `glpi_plugin_racks_racks_items`
                      WHERE `plugin_racks_racks_id` = '$ID' 
                        AND `second_powersupply` > 0 ";
      $result_alim2 = $DB->query($query_alim2);

      echo "<form><div class='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='6'>".__('Total')."</th></tr><tr>";
      //echo "<th colspan='3'>".__('Equipment')."</th>";
      echo "<th>".__('Power supplies number', 'racks')."</th>";
      echo "<th>".__('Total current', 'racks')."</th>"; // Courant consomme
      echo "<th>".__('Calorific waste', 'racks')."</th>";
      echo "<th>".__('Flow rate', 'racks')."</th>";
      echo "<th>".__('Weight', 'racks')."</th>";
      echo "</tr>";

      $total_cordons = 0;
      foreach ($DB->request($query_alim1) as $data_alim1) {
         $total_cordons+=$data_alim1["total_alim1"];      
      }
      foreach ($DB->request($query_alim2) as $data_alim2) {
         $total_cordons+=$data_alim2["total_alim2"];
      }

      foreach ($DB->request($query) as $data) {
         echo "<tr class='tab_bg_1'>";

         echo "<td class='center'>".$total_cordons."</td>";
         echo "<td class='center'><b>"; 
         echo Html::formatNumber($data["total_amps"], true); 
         echo " ".__('amps', 'racks')."</b></td>";
         echo "<td class='center'><b>"; 
         echo Html::formatNumber($data["total_dissipation"], true)."&nbsp;";
         $PluginRacksConfig->getUnit("dissipation");
         echo "</b></td>";
         echo "<td class='center'><b>"; 
         echo Html::formatNumber($data["total_flow_rate"], true)."&nbsp;";
         $PluginRacksConfig->getUnit("rate");
         echo "</b></td>";

         $total_weight=$data["total_weight"]+$this->fields['weight'];
         echo "<td class='center'><b>"; 
         echo Html::formatNumber($total_weight, true)."&nbsp";
         $PluginRacksConfig->getUnit("weight");
         echo "</b></td>";

         echo "</tr>";
      }
      echo "</table></div>";
      Html::closeForm();
   }

   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
   **/
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }


   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($all=false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }
   
   
   //Massive Action
   function getSpecificMassiveActions($checkitem=NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);
      
      if (Session::haveRight('transfer', READ)
            && Session::isMultiEntitiesMode()
            && $isadmin) {
         $actions['PluginRacksRack'.MassiveAction::CLASS_ACTION_SEPARATOR.'transfer'] = __('Transfer');
      }
      return $actions;
   }  


   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case "transfer" :
            Dropdown::show('Entity');
            echo Html::submit(_x('button','Post'), array('name' => 'massiveaction'));
            return true;
            break;
      }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      switch ($ma->getAction()) {
          case "transfer" :
            $input = $ma->getInput();

            if ($item->getType() == 'PluginRacksRack') {
            foreach ($ids as $key) {

                  $values["id"] = $key;
                  $values["entities_id"] = $input['entities_id'];

                  if ($item->update($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                      $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            break;
      }
   }
}

?>