<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Webapplications plugin for GLPI
 Copyright (C) 2003-2011 by the Webapplications Development Team.

 https://forge.indepnet.net/projects/webapplications
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Webapplications.

 Webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginWebapplicationsWebapplication extends CommonDBTM {

   public $dohistory=true;
   static $rightname                   = "plugin_webapplications";
   protected $usenotepadrights         = true;
   
   static $types = array('Computer', 'Monitor', 'NetworkEquipment', 'Peripheral', 'Phone',
                            'Printer', 'Software', 'Entity');

   static function getTypeName($nb=0) {

      return _n('Web application', 'Web applications', $nb, 'webapplications');
   }

   //clean if webapplications are deleted
   function cleanDBonPurge() {

      $temp = new PluginWebapplicationsWebapplication_Item();
      $temp->deleteByCriteria(array('plugin_webapplications_webapplications_id' => $this->fields['id']));
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Supplier') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(2), self::countForItem($item));
         }
         return self::getTypeName(2);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='Supplier') {
         PluginWebapplicationsWebapplication_Item::showForSupplier($item);
      }
      return true;
   }
   
   static function countForItem(CommonDBTM $item) {

      return countElementsInTable('glpi_plugin_webapplications_webapplications',
                                  "`suppliers_id` = '".$item->getID()."'");
   }
   
   function getSearchOptions() {
      global $LANG;

      $tab                       = array();
    
      $tab['common']             = self::getTypeName(2);

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();

      $tab[2]['table']           = 'glpi_plugin_webapplications_webapplicationtypes';
      $tab[2]['field']           = 'name';
      $tab[2]['name']            = PluginWebapplicationsWebapplicationType::getTypeName(1);
      $tab[2]['datatype']        = 'dropdown';
      
      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'address';
      $tab[3]['name']            = __('URL');
      $tab[3]['datatype']        = 'weblink';

      $tab[4]['table']           = 'glpi_plugin_webapplications_webapplicationservertypes';
      $tab[4]['field']           = 'name';
      $tab[4]['name']            = PluginWebapplicationsWebapplicationServerType::getTypeName(1);
      $tab[4]['datatype']        = 'dropdown';
      
      $tab[5]['table']           = 'glpi_plugin_webapplications_webapplicationtechnics';
      $tab[5]['field']           = 'name';
      $tab[5]['name']            = PluginWebapplicationsWebapplicationTechnic::getTypeName(1);
      $tab[5]['datatype']        = 'dropdown';
      
      $tab[6]['table']           = 'glpi_locations';
      $tab[6]['field']           = 'completename';
      $tab[6]['name']            = __('Location');
      $tab[6]['datatype']        = 'dropdown';
      
      $tab[7]['table']           = 'glpi_suppliers';
      $tab[7]['field']           = 'name';
      $tab[7]['name']            = __('Supplier');
      $tab[7]['datatype']        = 'itemlink';

      $tab[8]['table']           = $this->getTable();
      $tab[8]['field']           = 'version';
      $tab[8]['name']            = __('Version');
      
      $tab[9]['table']           = 'glpi_users';
      $tab[9]['field']           = 'name';
      $tab[9]['linkfield']       = 'users_id_tech';
      $tab[9]['name']            = __('Technician in charge of the hardware');
      $tab[9]['datatype']        = 'dropdown';
      $tab[9]['right']           = 'interface';
      
      $tab[10]['table']          = 'glpi_groups';
      $tab[10]['field']          = 'name';
      $tab[10]['linkfield']      = 'groups_id_tech';
      $tab[10]['name']           = __('Group in charge of the hardware');
      $tab[10]['condition']      = '`is_assign`';
      $tab[10]['datatype']       = 'dropdown';

      $tab[11]['table']          = $this->getTable();
      $tab[11]['field']          = 'backoffice';
      $tab[11]['name']           = __('Backoffice URL', 'webapplications');
      $tab[11]['datatype']       = 'weblink';

      $tab[13]['table']          = 'glpi_plugin_webapplications_webapplications_items';
      $tab[13]['field']          = 'items_id';
      $tab[13]['nosearch']       = true;
      $tab[13]['massiveaction']  = false;
      $tab[13]['name']           = _n('Associated item' , 'Associated items', 2);
      $tab[13]['forcegroupby']   = true;
      $tab[13]['joinparams']     = array('jointype' => 'child');
      
      $tab[14]['table']          = 'glpi_manufacturers';
      $tab[14]['field']          = 'name';
      $tab[14]['name']           = __('Editor', 'webapplications');
      $tab[14]['datatype']       = 'dropdown';
      
      $tab[15]['table']           = $this->getTable();
      $tab[15]['field']           = 'is_recursive';
      $tab[15]['name']            = __('Child entities');
      $tab[15]['datatype']        = 'bool';

      $tab[16]['table']           = $this->getTable();
      $tab[16]['field']           = 'comment';
      $tab[16]['name']            = __('Comments');
      $tab[16]['datatype']        = 'text';

      $tab[17]['table']          = $this->getTable();
      $tab[17]['field']          = 'date_mod';
      $tab[17]['massiveaction']  = false;
      $tab[17]['name']           = __('Last update');
      $tab[17]['datatype']       = 'datetime';

      $tab[18]['table']          = $this->getTable();
      $tab[18]['field']          = 'is_helpdesk_visible';
      $tab[18]['name']           = __('Associable to a ticket');
      $tab[18]['datatype']       = 'bool';

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


   //define header form
   function defineTabs($options=array()) {

      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginWebapplicationsWebapplication_Item', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Contract_Item', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Notepad', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }


   /**
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
   **/
   function getSelectLinkedItem () {

      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_webapplications_webapplications_items`
              WHERE `plugin_webapplications_webapplications_id`='" . $this->fields['id']."'";
   }


   function showForm($ID, $options=array()) {
      global $CFG_GLPI;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      //name of webapplications
      echo "<td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";
      //version of webapplications
      echo "<td>".__('Version')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "version", array('size' => "15"));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //type of webapplications
      echo "<td>".PluginWebapplicationsWebapplicationType::getTypeName(1)."</td>";
      echo "<td>";
      Dropdown::show('PluginWebapplicationsWebapplicationType',
                  array('value'  => $this->fields["plugin_webapplications_webapplicationtypes_id"],
                           'entity' => $this->fields["entities_id"]));
      echo "</td>";
      //server type of webapplications
      echo "<td>".PluginWebapplicationsWebapplicationServerType::getTypeName(1)."</td>";
      echo "<td>";
      Dropdown::show('PluginWebapplicationsWebapplicationServerType',
            array('value' => $this->fields["plugin_webapplications_webapplicationservertypes_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //location of webapplications
      echo "<td>".__('Location')."</td>";
      echo "<td>";
      Dropdown::show('Location', array('value'  => $this->fields["locations_id"],
                                       'entity' => $this->fields["entities_id"]));
      echo "</td>";
      //language of webapplications
      echo "<td>".PluginWebapplicationsWebapplicationTechnic::getTypeName(1)."</td>";
      echo "<td>";
      Dropdown::show('PluginWebapplicationsWebapplicationTechnic',
               array('value' => $this->fields["plugin_webapplications_webapplicationtechnics_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //users
      echo "<td>".__('Technician in charge of the hardware')."</td><td>";
      User::dropdown(array('name' => "users_id_tech",
                           'value'  => $this->fields["users_id_tech"],
                           'entity' => $this->fields["entities_id"],
                           'right'  => 'interface'));
      echo "</td>";
      //supplier of webapplications
      echo "<td>".__('Supplier')."</td>";
      echo "<td>";
      Dropdown::show('Supplier', array('value'  => $this->fields["suppliers_id"],
                                       'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //groups
      echo "<td>".__('Group in charge of the hardware')."</td><td>";
      Dropdown::show('Group', array('name' => "groups_id_tech",
                                    'value'  => $this->fields["groups_id_tech"],
                                    'entity' => $this->fields["entities_id"],
                                    'condition' => '`is_assign`'));
      echo "</td>";

      //manufacturer of webapplications
      echo "<td>".__('Editor', 'webapplications')."</td>";
      echo "<td>";
      Dropdown::show('Manufacturer', array('value'  => $this->fields["manufacturers_id"],
                                           'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //url of webapplications
      echo "<td>".__('URL')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "address", array('size' => "65"));
      echo "</td>";
      //is_helpdesk_visible
      echo "<td>" . __('Associable to a ticket') . "</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible', $this->fields['is_helpdesk_visible']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //backoffice of webapplications
      echo "<td>".__('Backoffice URL', 'webapplications')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "backoffice", array('size' => "65"));
      echo "</td>";

      echo "<td class='center' colspan = '2'>";
      printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //comments of webapplications
      echo "<td class='top center' colspan='4'>".__('Comments')."</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td class='top center' colspan='4'><textarea cols='125' rows='3' name='comment' >".
            $this->fields["comment"]."</textarea>";
      echo "</tr>";

      $this->showFormButtons($options);
      
      return true;
   }

   
   /**
    * Make a select box for link webapplications
    *
    * Parameters which could be used in options array :
    *    - name : string / name of the select (default is documents_id)
    *    - entity : integer or array / restrict to a defined entity or array of entities
    *                   (default -1 : no restriction)
    *    - used : array / Already used items ID: not to display in dropdown (default empty)
    *
    * @param $options array of possible options
    *
    * @return nothing (print out an HTML select box)
   **/
   static function dropdown($options=array()) {
      global $DB, $CFG_GLPI;


      $p['name']    = 'plugin_webapplications_webapplications_id';
      $p['entity']  = '';
      $p['used']    = array();
      $p['display'] = true;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $where = " WHERE `glpi_plugin_webapplications_webapplications`.`is_deleted` = '0' ".
                       getEntitiesRestrictRequest("AND", "glpi_plugin_webapplications_webapplications", '', $p['entity'], true);

      if (count($p['used'])) {
         $where .= " AND `id` NOT IN (0, ".implode(",",$p['used']).")";
      }

      $query = "SELECT *
                FROM `glpi_plugin_webapplications_webapplicationtypes`
                WHERE `id` IN (SELECT DISTINCT `plugin_webapplications_webapplicationtypes_id`
                               FROM `glpi_plugin_webapplications_webapplications`
                             $where)
                ORDER BY `name`";
      $result = $DB->query($query);

      $values = array(0 => Dropdown::EMPTY_VALUE);

      while ($data = $DB->fetch_assoc($result)) {
         $values[$data['id']] = $data['name'];
      }
      $rand = mt_rand();
      $out  = Dropdown::showFromArray('_webapplicationtype', $values, array('width'   => '30%',
                                                                'rand'    => $rand,
                                                                'display' => false));
      $field_id = Html::cleanId("dropdown__webapplicationtype$rand");

      $params   = array('webapplicationtype' => '__VALUE__',
                        'entity' => $p['entity'],
                        'rand'   => $rand,
                        'myname' => $p['name'],
                        'used'   => $p['used']);

      $out .= Ajax::updateItemOnSelectEvent($field_id,"show_".$p['name'].$rand,
                                            $CFG_GLPI["root_doc"]."/plugins/webapplications/ajax/dropdownTypeWebApplications.php",
                                            $params, false);
      $out .= "<span id='show_".$p['name']."$rand'>";
      $out .= "</span>\n";

      $params['webapplicationtype'] = 0;
      $out .= Ajax::updateItem("show_".$p['name'].$rand,
                               $CFG_GLPI["root_doc"]. "/plugins/webapplications/ajax/dropdownTypeWebApplications.php",
                               $params, false);
      if ($p['display']) {
         echo $out;
         return $rand;
      }
      return $out;
   }


   /**
    * Show for PDF an webapplications
    *
    * @param $pdf object for the output
    * @param $ID of the webapplications
   **/
   function show_PDF($pdf) {
      global $LANG, $DB;

      $pdf->setColumnsSize(50,50);
      $col1 = '<b>'.__('ID').' '.$this->fields['id'].'</b>';
      if (isset($this->fields["date_mod"])) {
         $col2 = printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      } else {
         $col2 = '';
      }
      $pdf->displayTitle($col1, $col2);

      $pdf->displayLine(
         '<b><i>'.__('Name').':</i></b> '.$this->fields['name'],
         '<b><i>'.PluginWebapplicationsWebapplicationType::getTypeName(1).' :</i></b> '.
               Html::clean(Dropdown::getDropdownName('glpi_plugin_webapplications_webapplicationtypes',
                                                    $this->fields['plugin_webapplications_webapplicationtypes_id'])));
      $pdf->displayLine(
         '<b><i>'.__('Technician in charge of the hardware').':</i></b> '.getUserName($this->fields['users_id_tech']),
         '<b><i>'.__('Group in charge of the hardware').':</i></b> '.Html::clean(Dropdown::getDropdownName('glpi_groups',
                                                               $this->fields['groups_id_tech'])));
      $pdf->displayLine(
         '<b><i>'.__('Location').':</i></b> '.
               Html::clean(Dropdown::getDropdownName('glpi_locations', $this->fields['locations_id'])),
         '<b><i>'.PluginWebapplicationsWebapplicationServerType::getTypeName(1).':</i></b> '.
               Html::clean(Dropdown::getDropdownName('glpi_plugin_webapplications_webapplicationservertypes',
                                                    $this->fields["plugin_webapplications_webapplicationservertypes_id"])));
      $pdf->displayLine(
         '<b><i>'.PluginWebapplicationsWebapplicationTechnic::getTypeName(1).' :</i></b> '.
               Html::clean(Dropdown::getDropdownName('glpi_plugin_webapplications_webapplicationtechnics',
                                                    $this->fields['plugin_webapplications_webapplicationtechnics_id'])),
         '<b><i>'.__('Version').':</i></b> '.$this->fields['version']);

      $pdf->displayLine(
         '<b><i>'.__('Supplier').':</i></b> '.
               Html::clean(Dropdown::getDropdownName('glpi_suppliers', $this->fields['suppliers_id'])),
         '<b><i>'.__('Editor', 'webapplications').':</i></b> '.
               Html::clean(Dropdown::getDropdownName('glpi_manufacturers',
                                                    $this->fields["manufacturers_id"])));

      $pdf->displayLine(
         '<b><i>'.__('URL').':</i></b> '.$this->fields['address'], '');

      $pdf->setColumnsSize(100);

      $pdf->displayText('<b><i>'.__('Comments').':</i></b>', $this->fields['comment']);

      $pdf->displaySpace();
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
   
   
   /**
    * @since version 0.85
    *
    * @see CommonDBTM::getSpecificMassiveActions()
   **/
   function getSpecificMassiveActions($checkitem=NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         if ($isadmin) {
            $actions['PluginWebapplicationsWebapplication'.MassiveAction::CLASS_ACTION_SEPARATOR.'install']    = _x('button', 'Associate');
            $actions['PluginWebapplicationsWebapplication'.MassiveAction::CLASS_ACTION_SEPARATOR.'uninstall'] = _x('button', 'Dissociate');

            if (Session::haveRight('transfer', READ)
                     && Session::isMultiEntitiesMode()
            ) {
               $actions['PluginWebapplicationsWebapplication'.MassiveAction::CLASS_ACTION_SEPARATOR.'transfer'] = __('Transfer');
            }
         }
      }
      return $actions;
   }
   
   
   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case 'plugin_webapplications_add_item':
            self::dropdown(array());
            echo "&nbsp;".
                 Html::submit(_x('button','Post'), array('name' => 'massiveaction'));
            return true;
         case "install" :
            Dropdown::showAllItems("item_item", 0, 0, -1, self::getTypes(true), 
                                   false, false, 'typeitem');
            echo Html::submit(_x('button','Post'), array('name' => 'massiveaction'));
            return true;
            break;
         case "uninstall" :
            Dropdown::showAllItems("item_item", 0, 0, -1, self::getTypes(true), 
                                   false, false, 'typeitem');
            echo Html::submit(_x('button','Post'), array('name' => 'massiveaction'));
            return true;
            break;
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
      global $DB;
      
      $web_item = new PluginWebapplicationsWebapplication_Item();
      
      switch ($ma->getAction()) {
         case "plugin_webapplications_add_item":
            $input = $ma->getInput();
            foreach ($ids as $id) {
               $input = array('plugin_webapplications_webapplications_id' => $input['plugin_webapplications_webapplications_id'],
                                 'items_id'      => $id,
                                 'itemtype'      => $item->getType());
               if ($web_item->can(-1,UPDATE,$input)) {
                  if ($web_item->add($input)) {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                  }
               } else {
                  $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
               }
            }

            return;
         case "transfer" :
            $input = $ma->getInput();
            if ($item->getType() == 'PluginWebapplicationsWebapplication') {
            foreach ($ids as $key) {
                  $item->getFromDB($key);
                  $type = PluginWebapplicationsWebapplicationType::transfer($item->fields["plugin_webapplications_webapplicationtypes_id"], $input['entities_id']);
                  if ($type > 0) {
                     $values["id"] = $key;
                     $values["plugin_webapplications_webapplicationtypes_id"] = $type;
                     $item->update($values);
                  }

                  unset($values);
                  $values["id"] = $key;
                  $values["entities_id"] = $input['entities_id'];

                  if ($item->update($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            return;

         case 'install' :
            $input = $ma->getInput();
            foreach ($ids as $key) {
               if ($item->can($key, UPDATE)) {
                  $values = array('plugin_webapplications_webapplications_id' => $key,
                                 'items_id'      => $input["item_item"],
                                 'itemtype'      => $input['typeitem']);
                  if ($web_item->add($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
                  $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
               }
            }
            return;
            
         case 'uninstall':
            $input = $ma->getInput();
            foreach ($ids as $key) {
               if ($val == 1) {
                  if ($web_item->deleteItemByWebApplicationsAndItem($key,$input['item_item'],$input['typeitem'])) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }
}
?>