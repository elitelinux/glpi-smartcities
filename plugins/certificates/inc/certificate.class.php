<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Certificates plugin for GLPI
 Copyright (C) 2003-2011 by the certificates Development Team.

 https://forge.indepnet.net/projects/certificates
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of certificates.

 Certificates is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Certificates is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Certificates. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginCertificatesCertificate extends CommonDBTM {
   
   public $dohistory                   = true;
   static $rightname                   = "plugin_certificates";
   protected $usenotepadrights         = true;
   
   static $types = array('Computer','Monitor','NetworkEquipment','Peripheral',
         'Phone','Printer','Software');
         
   static function getTypeName($nb=0) {

      return _n('Certificate', 'Certificates', $nb, 'certificates');
   }

   
   function cleanDBonPurge() {

      $temp = new PluginCertificatesCertificate_Item();
      $temp->deleteByCriteria(array('plugin_certificates_certificates_id' => $this->fields['id']));

   }
  
   function getSearchOptions() {

      $tab                       = array();
    
      $tab['common']             = self::getTypeName(2);

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();
      
      $tab[2]['table']           = 'glpi_plugin_certificates_certificatetypes';
      $tab[2]['field']           = 'name';
      $tab[2]['name']            = __('Type');
      $tab[2]['datatype']        = 'dropdown';
      
      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'dns_name';
      $tab[3]['name']            = __('DNS name', 'certificates');

      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'dns_suffix';
      $tab[4]['name']            = __('DNS suffix', 'certificates');

      $tab[5]['table']           = 'glpi_users';
      $tab[5]['field']           = 'name';
      $tab[5]['linkfield']       = 'users_id_tech';
      $tab[5]['name']            = __('Technician in charge of the hardware');
      $tab[5]['datatype']        = 'dropdown';
      $tab[5]['right']           = 'interface';

      $tab[6]['table']           = 'glpi_locations';
      $tab[6]['field']           = 'completename';
      $tab[6]['name']            = __('Location');
      $tab[6]['datatype']        = 'dropdown';

      $tab[7]['table']           = 'glpi_manufacturers';
      $tab[7]['field']           = 'name';
      $tab[7]['name']            = __('Manufacturer')." (".__('Root CA', 'certificates').")";
      $tab[7]['datatype']        = 'dropdown';

      $tab[8]['table']           = $this->getTable();
      $tab[8]['field']           = 'auto_sign';
      $tab[8]['name']            = __('Self-signed', 'certificates');
      $tab[8]['datatype']        = 'bool';

      $tab[9]['table']           = $this->getTable();
      $tab[9]['field']           = 'mailing';
      $tab[9]['name']            = __('Notification email', 'certificates');
      $tab[9]['datatype']        = 'bool';

      $tab[10]['table']          = 'glpi_groups';
      $tab[10]['field']          = 'name';
      $tab[10]['linkfield']      = 'groups_id_tech';
      $tab[10]['name']           = __('Group in charge of the hardware');
      $tab[10]['condition']      = '`is_assign`';
      $tab[10]['datatype']       = 'dropdown';

      $tab[11]['table']          = $this->getTable();
      $tab[11]['field']          = 'date_query';
      $tab[11]['name']           = __('Creation date');
      $tab[11]['datatype']       = 'date';

      $tab[12]['table']          = $this->getTable();
      $tab[12]['field']          = 'date_expiration';
      $tab[12]['name']           = __('Expiration date');
      $tab[12]['datatype']       = 'date';

      $tab[13]['table']          = 'glpi_plugin_certificates_certificatestates';
      $tab[13]['field']          = 'name';
      $tab[13]['name']           = __('Status');
      $tab[13]['datatype']       = 'dropdown';

      $tab[14]['table']          = $this->getTable();
      $tab[14]['field']          = 'command';
      $tab[14]['name']           = __('Command used', 'certificates');
      $tab[14]['datatype']       = 'text';

      $tab[15]['table']          = $this->getTable();
      $tab[15]['field']          = 'certificate_request';
      $tab[15]['name']           = __('Certificate request (CSR)', 'certificates');
      $tab[15]['datatype']       = 'text';

      $tab[16]['table']          = $this->getTable();
      $tab[16]['field']          = 'certificate_item';
      $tab[16]['name']           = self::getTypeName(1);
      $tab[16]['datatype']       = 'text';

      $tab[17]['table']          = 'glpi_plugin_certificates_certificates_items';
      $tab[17]['field']          = 'items_id';
      $tab[17]['nosearch']       = true;
      $tab[17]['massiveaction']  = false;
      $tab[17]['name']           = _n('Associated item' , 'Associated items', 2);
      $tab[17]['forcegroupby']   = true;
      $tab[17]['joinparams']     = array('jointype' => 'child');

      $tab[18]['table']          = $this->getTable();
      $tab[18]['field']          = 'is_recursive';
      $tab[18]['name']           = __('Child entities');
      $tab[18]['datatype']       = 'bool';

      $tab[19]['table']          = $this->getTable();
      $tab[19]['field']          = 'is_helpdesk_visible';
      $tab[19]['name']           = __('Associable to a ticket');
      $tab[19]['datatype']       = 'bool';

      $tab[20]['table']          = $this->getTable();
      $tab[20]['field']          = 'date_mod';
      $tab[20]['massiveaction']  = false;
      $tab[20]['name']           = __('Last update');
      $tab[20]['datatype']       = 'datetime';

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
   
   function defineTabs($options=array()) {

      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginCertificatesCertificate_Item', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Contract_Item', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Notepad', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   function prepareInputForAdd($input) {

      if (isset($input['date_query']) 
         && empty($input['date_query'])) 
            $input['date_query']='NULL';
      if (isset($input['date_expiration']) 
         && empty($input['date_expiration'])) 
            $input['date_expiration']='NULL';

      return $input;
   }

   function prepareInputForUpdate($input) {

      if (isset($input['date_query']) 
         && empty($input['date_query'])) 
            $input['date_query']='NULL';
      if (isset($input['date_expiration']) 
         && empty($input['date_expiration'])) 
            $input['date_expiration']='NULL';

      return $input;
   }
   
   /*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */
   function getSelectLinkedItem () {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_certificates_certificates_items`
              WHERE `plugin_certificates_certificates_id`='" . $this->fields['id']."'";
   }
   
   function showForm ($ID, $options=array()) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";

      echo "<td>".__('Technician in charge of the hardware')."</td>";
      echo "<td>";
      User::dropdown(array('name' => "users_id_tech",
                           'value' => $this->fields["users_id_tech"],
                           'entity' => $this->fields["entities_id"],
                           'right' => 'interface'));
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Type')."</td><td>";
      Dropdown::show('PluginCertificatesCertificateType', array('name' => "plugin_certificates_certificatetypes_id",
                                                               'value' => $this->fields["plugin_certificates_certificatetypes_id"], 
                                                               'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "<td>".__('Group in charge of the hardware')."</td>";
      echo "<td>";
      Group::dropdown(array('name'      => 'groups_id_tech',
                            'value'     => $this->fields['groups_id_tech'],
                            'entity'    => $this->fields['entities_id'],
                            'condition' => '`is_assign`'));
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".__('Location')."</td>";
      echo "<td>";
      Location::dropdown(array('value'  => $this->fields["locations_id"],
                               'entity' => $this->fields["entities_id"]));
      echo "</td>";

      echo "<td>"._n('Manufacturer', 'Manufacturers', 1)." (".__('Root CA', 'certificates').")</td>";
      echo "<td>";
      Manufacturer::dropdown(array('value' => $this->fields["manufacturers_id"]));
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".__('DNS name', 'certificates')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"dns_name");
      echo "</td>";
 
      echo "<td>".__('Creation date')."</td>";
      echo "<td>";
      Html::showDateFormItem("date_query",$this->fields["date_query"],true,true);
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".__('DNS suffix', 'certificates')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"dns_suffix");
      echo "</td>";
      
      echo "<td>".__('Expiration date');
      echo "&nbsp;";
      Html::showToolTip(nl2br(__('Empty for infinite', 'certificates')));
      echo "&nbsp;</td>";
      echo "<td>";
      Html::showDateFormItem("date_expiration",$this->fields["date_expiration"],true,true);
      echo "</td>";
      
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>".PluginCertificatesCertificateState::getTypeName(1)."</td>";
      echo "<td>";
      Dropdown::show('PluginCertificatesCertificateState', array('name' => "plugin_certificates_certificatestates_id",
                                                                  'value' => $this->fields["plugin_certificates_certificatestates_id"], 
                                                                  'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "<td>".__('Notification email', 'certificates')."</td>";
      echo "<td>";
      Dropdown::showYesNo('mailing',$this->fields["mailing"]);
      echo "</td>";
      
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".__('Self-signed', 'certificates')."</td>";
      echo "<td>";
      Dropdown::showYesNo('auto_sign',$this->fields["auto_sign"]);
      echo "</td>";
      
      echo "<td>" . __('Associable to a ticket') . "</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible',$this->fields['is_helpdesk_visible']);
      echo "</td>";
      
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>";
      echo __('Command used', 'certificates')."</td><td>";
      echo "<textarea cols='35' rows='4' name='command' >";
      echo $this->fields["command"]."</textarea>";
      echo "</td>";

      echo "<td>";
      echo __('Certificate request (CSR)', 'certificates')."</td><td>";
      echo "<textarea cols='35' rows='4' name='certificate_request' >";
      echo $this->fields["certificate_request"]."</textarea>";
      echo "</td>";
      
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      
      echo "<td class='center' colspan='2'>";
      printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      echo "</td>";
      
      echo "<td>";
      echo self::getTypeName(1)."</td><td>";
      echo "<textarea cols='35' rows='4' name='certificate_item' >";
      echo $this->fields["certificate_item"]."</textarea>";
      echo "</td>";
      
      echo "</tr>";
      
      $this->showFormButtons($options);
      
      return true;
   }
   
   
   /**
    * Make a select box for link certificates
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
   static function dropdownCertificate($options=array()) {
      global $DB, $CFG_GLPI;


      $p['name']    = 'plugin_certificates_certificates_id';
      $p['entity']  = '';
      $p['used']    = array();
      $p['display'] = true;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $where = " WHERE `glpi_plugin_certificates_certificates`.`is_deleted` = '0' ".
                       getEntitiesRestrictRequest("AND", "glpi_plugin_certificates_certificates", '', $p['entity'], true);

      if (count($p['used'])) {
         $where .= " AND `id` NOT IN (0, ".implode(",",$p['used']).")";
      }

      $query = "SELECT *
                FROM `glpi_plugin_certificates_certificatetypes`
                WHERE `id` IN (SELECT DISTINCT `plugin_certificates_certificatetypes_id`
                               FROM `glpi_plugin_certificates_certificates`
                             $where)
                ORDER BY `name`";
      $result = $DB->query($query);

      $values = array(0 => Dropdown::EMPTY_VALUE);

      while ($data = $DB->fetch_assoc($result)) {
         $values[$data['id']] = $data['name'];
      }
      $rand = mt_rand();
      $out  = Dropdown::showFromArray('_certificatetype', $values, array('width'   => '30%',
                                                                'rand'    => $rand,
                                                                'display' => false));
      $field_id = Html::cleanId("dropdown__certificatetype$rand");

      $params   = array('certificatetype' => '__VALUE__',
                        'entity' => $p['entity'],
                        'rand'   => $rand,
                        'myname' => $p['name'],
                        'used'   => $p['used']);

      $out .= Ajax::updateItemOnSelectEvent($field_id,"show_".$p['name'].$rand,
                                            $CFG_GLPI["root_doc"]."/plugins/certificates/ajax/dropdownTypeCertificates.php",
                                            $params, false);
      $out .= "<span id='show_".$p['name']."$rand'>";
      $out .= "</span>\n";

      $params['certificatetype'] = 0;
      $out .= Ajax::updateItem("show_".$p['name'].$rand,
                               $CFG_GLPI["root_doc"]. "/plugins/certificates/ajax/dropdownTypeCertificates.php",
                               $params, false);
      if ($p['display']) {
         echo $out;
         return $rand;
      }
      return $out;
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
            $actions['PluginCertificatesCertificate'.MassiveAction::CLASS_ACTION_SEPARATOR.'install']    = _x('button', 'Associate');
            $actions['PluginCertificatesCertificate'.MassiveAction::CLASS_ACTION_SEPARATOR.'uninstall'] = _x('button', 'Dissociate');

            if (Session::haveRight('transfer', READ)
                     && Session::isMultiEntitiesMode()
            ) {
               $actions['PluginCertificatesCertificate'.MassiveAction::CLASS_ACTION_SEPARATOR.'transfer'] = __('Transfer');
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
         case 'plugin_certificates_add_item':
            self::dropdownCertificate(array());
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
      
      $certif_item = new PluginCertificatesCertificate_Item();
      
      switch ($ma->getAction()) {
         case "plugin_certificates_add_item":
            $input = $ma->getInput();
            foreach ($ids as $id) {
               $input = array('plugin_certificates_certificates_id' => $input['plugin_certificates_certificates_id'],
                                 'items_id'      => $id,
                                 'itemtype'      => $item->getType());
               if ($certif_item->can(-1,UPDATE,$input)) {
                  if ($certif_item->add($input)) {
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
            if ($item->getType() == 'PluginCertificatesCertificate') {
               foreach ($ids as $key) {
                  $item->getFromDB($key);
                  $type = PluginCertificatesCertificateType::transfer($item->fields["plugin_certificates_certificatetypes_id"], $input['entities_id']);
                  if ($type > 0) {
                     $values["id"] = $key;
                     $values["plugin_certificates_certificatetypes_id"] = $type;
                     $item->update($values);
                  }
                  $state = PluginCertificatesCertificateState::transfer($this->fields["plugin_certificates_certificatestates_id"],$input['entities_id']);
                  if ($state > 0) {
                     $values["id"] = $key;
                     $values["plugin_certificates_certificatestates_id"] = $state;
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
                  $values = array('plugin_certificates_certificates_id' => $key,
                                 'items_id'      => $input["item_item"],
                                 'itemtype'      => $input['typeitem']);
                  if ($certif_item->add($values)) {
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
                  if ($certif_item->deleteItemByCertificatesAndItem($key,$input['item_item'],$input['typeitem'])) {
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
   
   
   // Cron action
   static function cronInfo($name) {

      switch ($name) {
         case 'CertificatesAlert':
            return array (
               'description' => __('Expired or expiring certificates', 'certificates'));   // Optional
            break;
      }
      return array();
   }

   static function queryExpiredCertificates() {

      $config=new PluginCertificatesConfig();
      $notif= new PluginCertificatesNotificationState();
      
      $config->getFromDB('1');
      $delay=$config->fields["delay_expired"];

      $query = "SELECT * 
         FROM `glpi_plugin_certificates_certificates`
         WHERE `date_expiration` IS NOT NULL
         AND `is_deleted` = '0'
         AND DATEDIFF(CURDATE(),`date_expiration`) > $delay 
         AND DATEDIFF(CURDATE(),`date_expiration`) > 0 ";
      $query.= "AND `plugin_certificates_certificatestates_id` NOT IN (999999";
      $query.= $notif->findStates();
      $query.= ") ";

      return $query;
   }
   
   static function queryCertificatesWhichExpire() {

      $config=new PluginCertificatesConfig();
      $notif= new PluginCertificatesNotificationState();
      
      $config->getFromDB('1');
      $delay=$config->fields["delay_whichexpire"];
      
      $query = "SELECT *
         FROM `glpi_plugin_certificates_certificates`
         WHERE `date_expiration` IS NOT NULL
         AND `is_deleted` = '0'
         AND DATEDIFF(CURDATE(),`date_expiration`) > -$delay 
         AND DATEDIFF(CURDATE(),`date_expiration`) < 0 ";
      $query.= "AND `plugin_certificates_certificatestates_id` NOT IN (999999";
      $query.= $notif->findStates();
      $query.= ") ";

      return $query;
   }
   /**
    * Cron action on certificates : ExpiredCertificates or CertificatesWhichExpire
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronCertificatesAlert($task=NULL) {
      global $DB,$CFG_GLPI;
      
      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }

      $message=array();
      $cron_status = 0;
      
      $Certificate = new self();
      $query_expired = self::queryExpiredCertificates();
      $query_whichexpire = self::queryCertificatesWhichExpire();
      
      $querys = array(Alert::NOTICE=>$query_whichexpire, Alert::END=>$query_expired);
      
      $certificate_infos = array();
      $certificate_messages = array();

      foreach ($querys as $type => $query) {
         $certificate_infos[$type] = array();
         foreach ($DB->request($query) as $data) {
            $entity = $data['entities_id'];
            $message = $data["name"].": ".
                        Html::convdate($data["date_expiration"])."<br>\n";
            $certificate_infos[$type][$entity][] = $data;

            if (!isset($certificates_infos[$type][$entity])) {
               $certificate_messages[$type][$entity] = __('Certificates expired since more', 'certificates')."<br />";
            }
            $certificate_messages[$type][$entity] .= $message;
         }
      }
      
      foreach ($querys as $type => $query) {
      
         foreach ($certificate_infos[$type] as $entity => $certificates) {
            Plugin::loadLang('certificates');

            if (NotificationEvent::raiseEvent(($type==Alert::NOTICE?"CertificatesWhichExpire":"ExpiredCertificates"),
                                              new PluginCertificatesCertificate(),
                                              array('entities_id'=>$entity,
                                                    'certificates'=>$certificates))) {
               $message = $certificate_messages[$type][$entity];
               $cron_status = 1;
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",
                                                       $entity).":  $message\n");
                  $task->addVolume(1);
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",
                                                                    $entity).":  $message");
               }

            } else {
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",$entity).
                             ":  Send certificates alert failed\n");
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",$entity).
                                          ":  Send certificates alert failed",false,ERROR);
               }
            }
         }
      }
      
      return $cron_status;
   }
   
   static function configCron($target) {

      $notif=new PluginCertificatesNotificationState();
      $config=new PluginCertificatesConfig();

      $config->showForm($target,1);
      $notif->showForm($target);
      $notif->showAddForm($target);
    
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
}

?>