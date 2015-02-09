<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Badges plugin for GLPI
 Copyright (C) 2003-2011 by the badges Development Team.

 https://forge.indepnet.net/projects/badges
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of badges.

 Badges is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Badges is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Badges. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginBadgesBadge extends CommonDBTM {
   
   public $dohistory                   = true;
   static $rightname                   = "plugin_badges";
   protected $usenotepadrights         = true;

   static function getTypeName($nb=0) {
      return _n('Badge', 'Badges', $nb, 'badges');
   }

  
   function getSearchOptions() {

      $tab                       = array();

      $tab['common']             = self::getTypeName(2);

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();
      
      $tab[2]['table']           = 'glpi_plugin_badges_badgetypes';
      $tab[2]['field']           = 'name';
      $tab[2]['name']            = __('Type');
      $tab[2]['datatype']        = 'dropdown';
      
      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'serial';
      $tab[3]['name']            = __('Serial number');
      
      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'date_affectation';
      $tab[4]['name']            = __('Affectation date', 'badges');
      $tab[4]['datatype']        = 'date';
      
      $tab[5]['table']           = $this->getTable();
      $tab[5]['field']           = 'date_expiration';
      $tab[5]['name']            = __('Date of end of validity', 'badges');
      $tab[5]['datatype']        = 'date';
      
      $tab[6]['table']           = 'glpi_locations';
      $tab[6]['field']           = 'completename';
      $tab[6]['name']            = __('Location');
      $tab[6]['datatype']        = 'dropdown';
      
      $tab[7]['table']           = 'glpi_states';
      $tab[7]['field']           = 'completename';
      $tab[7]['name']            = __('Status');
      $tab[7]['datatype']        = 'dropdown';
      
      $tab[8]['table']           = $this->getTable();
      $tab[8]['field']           = 'comment';
      $tab[8]['name']            = __('Comments');
      $tab[8]['datatype']        = 'text';
      
      $tab[9]['table']           = $this->getTable();
      $tab[9]['field']           = 'is_helpdesk_visible';
      $tab[9]['name']            = __('Associable to a ticket');
      $tab[9]['datatype']        = 'bool';
      
      $tab[10]['table']          = 'glpi_users';
      $tab[10]['field']          = 'name';
      $tab[10]['name']           = __('User');
      $tab[10]['datatype']       = 'dropdown';
      $tab[10]['right']          = 'all';
      
      $tab[11]['table']          = $this->getTable();
      $tab[11]['field']          = 'date_mod';
      $tab[11]['name']           = __('Last update');
      $tab[11]['datatype']       = 'datetime';
      $tab[11]['massiveaction']  = false;
      
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
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Notepad', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   function prepareInputForAdd($input) {

      if (isset($input['date_affectation']) && empty($input['date_affectation'])) 
         $input['date_affectation']='NULL';
      if (isset($input['date_expiration']) && empty($input['date_expiration'])) 
         $input['date_expiration']='NULL';

      return $input;
   }

   function prepareInputForUpdate($input) {

      if (isset($input['date_affectation']) && empty($input['date_affectation'])) 
         $input['date_affectation']='NULL';
      if (isset($input['date_expiration']) && empty($input['date_expiration'])) 
         $input['date_expiration']='NULL';

      return $input;
   }

   function showForm ($ID, $options=array()) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";
      
      echo "<td>" . __('User') . "</td><td>";
      User::dropdown(array('value' => $this->fields["users_id"],
                           'entity' => $this->fields["entities_id"],
                           'right' => 'all'));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . __('Location') . "</td><td>";
      Location::dropdown(array('value'  => $this->fields["locations_id"],
                               'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "<td>" . __('Type') . "</td><td>";
      Dropdown::show('PluginBadgesBadgeType', array('name' => "plugin_badges_badgetypes_id",
                                                   'value' => $this->fields["plugin_badges_badgetypes_id"], 
                                                   'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . __('Serial number') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"serial");
      echo "</td>";
      
      echo "<td>" . __('Status') . "</td><td>";
      State::dropdown(array('value' => $this->fields["states_id"]));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . __('Affectation date', 'badges')  . "</td>";
      echo "<td>";
      Html::showDateFormItem("date_affectation",$this->fields["date_affectation"],true,true);
      echo "</td>";
      
      echo "<td>" . __('Associable to a ticket') . "</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible',$this->fields['is_helpdesk_visible']);
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . __('Date of end of validity', 'badges');
      echo "</td>";
      echo "<td>";
      Html::showDateFormItem("date_expiration",$this->fields["date_expiration"],true,true);
      echo "</td>";
      
      echo "<td colspan='2'>";
      printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . __('Comments') . "</td>";
      echo "<td class='center' colspan='3'><textarea cols='115' rows='5' name='comment' >".
               $this->fields["comment"]."</textarea>";

      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }
   
   //for search engine
   static function getSpecificValueToDisplay($field, $values, array $options=array()) {
      
      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'date_expiration' :
            
            if (empty($values[$field]))
               return __('infinite');
            else
               return Html::convdate($values[$field]);
         break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }
   
   //Massive Action
   function getSpecificMassiveActions($checkitem=NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);
      
      if (Session::haveRight('transfer', READ)
            && Session::isMultiEntitiesMode()
            && $isadmin) {
         $actions['PluginBadgesBadge'.MassiveAction::CLASS_ACTION_SEPARATOR.'transfer'] = __('Transfer');
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

            if ($item->getType() == 'PluginBadgesBadge') {
               foreach ($ids as $key) {
                  $item->getFromDB($key);
                  $type = PluginBadgesBadgeType::transfer($item->fields["plugin_badges_badgetypes_id"], $input['entities_id']);
                  if ($type > 0) {
                     $values["id"] = $key;
                     $values["plugin_badges_badgetypes_id"] = $type;
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
            break;
      }
   }
   
   
   // Cron action
   static function cronInfo($name) {

      switch ($name) {
         case 'BadgesAlert':
            return array (
               'description' => __('Badges which expires', 'badges'));   // Optional
            break;
      }
      return array();
   }
   
   static function queryExpiredBadges() {

      $config=new PluginBadgesConfig();
      $notif= new PluginBadgesNotificationState();
      
      $config->getFromDB('1');
      $delay=$config->fields["delay_expired"];

      $query = "SELECT * 
         FROM `glpi_plugin_badges_badges`
         WHERE `date_expiration` IS NOT NULL
         AND `is_deleted` = '0'
         AND DATEDIFF(CURDATE(),`date_expiration`) > $delay 
         AND DATEDIFF(CURDATE(),`date_expiration`) > 0 ";
      $query.= "AND `states_id` NOT IN (999999";
      $query.= $notif->findStates();
      $query.= ") ";

      return $query;
   }
   
   static function queryBadgesWhichExpire() {

      $config=new PluginBadgesConfig();
      $notif= new PluginBadgesNotificationState();
      
      $config->getFromDB('1');
      $delay=$config->fields["delay_whichexpire"];
      
      $query = "SELECT *
         FROM `glpi_plugin_badges_badges`
         WHERE `date_expiration` IS NOT NULL
         AND `is_deleted` = '0'
         AND DATEDIFF(CURDATE(),`date_expiration`) > -$delay 
         AND DATEDIFF(CURDATE(),`date_expiration`) < 0 ";
      $query.= "AND `states_id` NOT IN (999999";
      $query.= $notif->findStates();
      $query.= ") ";

      return $query;
   }
   /**
    * Cron action on badges : ExpiredBadges or BadgesWhichExpire
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronBadgesAlert($task=NULL) {
      global $DB,$CFG_GLPI;
      
      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }

      $message=array();
      $cron_status = 0;
      
      $query_expired = self::queryExpiredBadges();
      $query_whichexpire = self::queryBadgesWhichExpire();
      
      $querys = array(Alert::NOTICE=>$query_whichexpire, Alert::END=>$query_expired);
      
      $badge_infos = array();
      $badge_messages = array();

      foreach ($querys as $type => $query) {
         $badge_infos[$type] = array();
         foreach ($DB->request($query) as $data) {
            $entity = $data['entities_id'];
            $message = $data["name"].": ".
                        Html::convdate($data["date_expiration"])."<br>\n";
            $badge_infos[$type][$entity][] = $data;

            if (!isset($badges_infos[$type][$entity])) {
               $badge_messages[$type][$entity] = __('Badges at the end of the validity', 'badges') ."<br />";
            }
            $badge_messages[$type][$entity] .= $message;
         }
      }
      
      foreach ($querys as $type => $query) {
      
         foreach ($badge_infos[$type] as $entity => $badges) {
            Plugin::loadLang('badges');

            if (NotificationEvent::raiseEvent(($type==Alert::NOTICE?"BadgesWhichExpire":"ExpiredBadges"),
                                              new PluginBadgesBadge(),
                                              array('entities_id'=>$entity,
                                                    'badges'=>$badges))) {
               $message = $badge_messages[$type][$entity];
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
                             ":  Send badges alert failed\n");
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",$entity).
                                          ":  Send badges alert failed",false,ERROR);
               }
            }
         }
      }
      
      return $cron_status;
   }
   
   static function configCron($target) {

      $notif=new PluginBadgesNotificationState();
      $config=new PluginBadgesConfig();

      $config->showForm($target,1);
      $notif->showForm($target);
      $notif->showAddForm($target);
    
   }
}
?>