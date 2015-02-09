<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Additionalalerts plugin for GLPI
 Copyright (C) 2003-2011 by the Additionalalerts Development Team.

 https://forge.indepnet.net/projects/additionalalerts
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Additionalalerts.

 Additionalalerts is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Additionalalerts is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with additionalalerts. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginAdditionalalertsOcsAlert extends CommonDBTM {
   
   static $rightname = "plugin_additionalalerts";
   
   static function getTypeName($nb=0) {

      return __('OCSNG synchronization', 'additionalalerts');
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='CronTask' && $item->getField('name')=="AdditionalalertsOcs") {
            return __('Plugin setup', 'additionalalerts');
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='CronTask') {

         $target = $CFG_GLPI["root_doc"]."/plugins/additionalalerts/front/ocsalert.form.php";
         self::configCron($target,$item->getField('id'));
      }
      return true;
   }
   
   // Cron action
   static function cronInfo($name) {

      switch ($name) {
         case 'AdditionalalertsOcs':
            return array (
            'description' => __('OCS-NG Synchronization alerts', 'additionalalerts'));   // Optional
            break;
         case 'AdditionalalertsNewOcs':
            return array (
            'description' => __('Alert for the new imported computers', 'additionalalerts'));
            break;
      }
      return array();
   }
   
   static function queryNew($config,$entity) {

      $query = "SELECT `glpi_plugin_ocsinventoryng_ocslinks`.`last_ocs_update`,`glpi_plugin_ocsinventoryng_ocslinks`.`last_update`,`glpi_plugin_ocsinventoryng_ocslinks`.`plugin_ocsinventoryng_ocsservers_id`, `glpi_computers`.*
            FROM `glpi_plugin_ocsinventoryng_ocslinks`,`glpi_computers`
            WHERE `glpi_plugin_ocsinventoryng_ocslinks`.`computers_id` = `glpi_computers`.`id`
            AND `glpi_computers`.`is_deleted` = 0
            AND `glpi_computers`.`is_template` = 0
            AND `glpi_computers`.`states_id` = '".$config["states_id_default"]."' AND `glpi_plugin_ocsinventoryng_ocslinks`.`plugin_ocsinventoryng_ocsservers_id` = '".$config["id"]."' ";
      $query.= "AND `glpi_computers`.`entities_id` = '".$entity."' ";
      $query .= " ORDER BY `glpi_plugin_ocsinventoryng_ocslinks`.`last_ocs_update` ASC";

      return $query;

   }

   static function query($delay_ocs,$config,$entity) {
      global $DB;

      $delay_stamp_ocs= mktime(0, 0, 0, date("m"), date("d")-$delay_ocs, date("y"));
      $date_ocs=date("Y-m-d",$delay_stamp_ocs);
      $date_ocs = $date_ocs." 00:00:00";

      $query = "SELECT `glpi_plugin_ocsinventoryng_ocslinks`.`last_ocs_update`,`glpi_plugin_ocsinventoryng_ocslinks`.`last_update`,`glpi_plugin_ocsinventoryng_ocslinks`.`plugin_ocsinventoryng_ocsservers_id`, `glpi_computers`.*
          FROM `glpi_plugin_ocsinventoryng_ocslinks`,`glpi_computers`
          WHERE `glpi_plugin_ocsinventoryng_ocslinks`.`computers_id` = `glpi_computers`.`id`
          AND `glpi_computers`.`is_deleted` = 0
          AND `glpi_computers`.`is_template` = 0
          AND `last_ocs_update` <= '".$date_ocs."' AND `glpi_plugin_ocsinventoryng_ocslinks`.`plugin_ocsinventoryng_ocsservers_id` = '".$config["id"]."'";
      $query_state= "SELECT `states_id`
            FROM `glpi_plugin_additionalalerts_notificationstates` ";
      $result_state = $DB->query($query_state);
      if ($DB->numrows($result_state)>0) {
         $query .= " AND (`glpi_computers`.`states_id` = 999999 ";
         while ($data_state=$DB->fetch_array($result_state)) {
            $type_where="OR `glpi_computers`.`states_id` = '".$data_state["states_id"]."' ";
            $query .= " $type_where ";
         }
         $query .= ") ";
      }
      $query.= "AND `glpi_computers`.`entities_id` = '".$entity."' ";

      $query .= " ORDER BY `glpi_plugin_ocsinventoryng_ocslinks`.`last_ocs_update` ASC";

      return $query;

   }

   static function displayBody($data) {
      global $CFG_GLPI;

      $computer= new computer();
      $computer->getFromDB($data["id"]);

      $body="<tr class='tab_bg_2'><td><a href=\"".$CFG_GLPI["root_doc"]."/front/computer.form.php?id=".$computer->fields["id"]."\">".$computer->fields["name"];

      if ($_SESSION["glpiis_ids_visible"] == 1 || empty($computer->fields["name"])) {
         $body.=" (";
         $body.=$computer->fields["id"].")";
      }
      $body.="</a></td>";
      if (Session::isMultiEntitiesMode())
         $body.="<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."</td>";
      $body.="<td>".Dropdown::getDropdownName("glpi_operatingsystems",$computer->fields["operatingsystems_id"])."</td>";
      $body.="<td>".Dropdown::getDropdownName("glpi_states",$computer->fields["states_id"])."</td>";
      $body.="<td>".Dropdown::getDropdownName("glpi_locations",$computer->fields["locations_id"])."</td>";
      $body.="<td>";
      if (!empty($computer->fields["users_id"])) {
            $body.="<a href=\"".$CFG_GLPI["root_doc"]."/front/user.form.php?id=".$computer->fields["users_id"]."\">".getUserName($computer->fields["users_id"])."</a>";
      }

      if (!empty($computer->fields["groups_id"])) {
         $body.=" - <a href=\"".$CFG_GLPI["root_doc"]."/front/group.form.php?id=".$computer->fields["groups_id"]."\">";
      }

      $body.=Dropdown::getDropdownName("glpi_groups",$computer->fields["groups_id"]);
      if ($_SESSION["glpiis_ids_visible"] == 1 ) {
         $body.=" (";
         $body.=$computer->fields["groups_id"].")";
      }
      $body.="</a>";

      if (!empty($computer->fields["contact"]))
         $body.=" - ".$computer->fields["contact"];

      $body.=" - </td>";
      $body.="<td>".Html::convdatetime($data["last_ocs_update"])."</td>";
      $body.="<td>".Html::convdatetime($data["last_update"])."</td>";
      $body.="<td>".Dropdown::getDropdownName("glpi_plugin_ocsinventoryng_ocsservers",$data["plugin_ocsinventoryng_ocsservers_id"])."</td>";

      $body.="</tr>";

      return $body;
   }
   
   static function getEntitiesToNotify($field,$with_value=false) {
      global $DB;

      $query = "SELECT `entities_id` as `entity`,`$field`
               FROM `glpi_plugin_additionalalerts_ocsalerts`";
      $query.= " ORDER BY `entities_id` ASC";

      $entities = array();
      foreach ($DB->request($query) as $entitydatas) {
         self::getDefaultValueForNotification($field,$entities, $entitydatas);
      }

      return $entities;
   }

   static function getDefaultValueForNotification($field, &$entities, $entitydatas) {
      
      $config = new PluginAdditionalalertsConfig();
      $config->getFromDB(1);
      //If there's a configuration for this entity & the value is not the one of the global config
      if (isset($entitydatas[$field]) && $entitydatas[$field] > 0) {
         $entities[$entitydatas['entity']] = $entitydatas[$field];
      }
      //No configuration for this entity : if global config allows notification then add the entity
      //to the array of entities to be notified
      else if ((!isset($entitydatas[$field])
                || (isset($entitydatas[$field]) && $entitydatas[$field] == -1))
               && $config->fields[$field]) {
         $entities[$entitydatas['entity']] = $config->fields[$field];
      }
   }

   static function cronAdditionalalertsOcs($task=NULL) {
      global $DB,$CFG_GLPI;
      
      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }
      
      $CronTask=new CronTask();
      if ($CronTask->getFromDBbyName("PluginAdditionalalertsOcsAlert","AdditionalalertsOcs")) {
         if ($CronTask->fields["state"]==CronTask::STATE_DISABLE) {
            return 0;
         }
      } else {
         return 0;
      }
         
      $message=array();
      $cron_status = 0;

      foreach (self::getEntitiesToNotify('delay_ocs') as $entity => $delay_ocs) {
         
         foreach ($DB->request("glpi_plugin_ocsinventoryng_ocsservers","`is_active` = 1") as $config) {
            $query_ocs = self::query($delay_ocs,$config,$entity);

            $ocs_infos = array();
            $ocs_messages = array();
            
            $type = Alert::END;
            $ocs_infos[$type] = array();
            foreach ($DB->request($query_ocs) as $data) {
            
               $entity = $data['entities_id'];
               $message = $data["name"];
               $ocs_infos[$type][$entity][] = $data;

               if (!isset($ocsmachines_infos[$type][$entity])) {
                  $ocs_messages[$type][$entity] = PluginAdditionalalertsOcsAlert::getTypeName(2)."<br />";
               }
               $ocs_messages[$type][$entity] .= $message;
            }
            
            foreach ($ocs_infos[$type] as $entity => $ocsmachines) {
               Plugin::loadLang('additionalalerts');
               
               if (NotificationEvent::raiseEvent("ocs",
                                                 new PluginAdditionalalertsOcsAlert(),
                                                 array('entities_id'=>$entity,
                                                       'ocsmachines'=>$ocsmachines,
                                                       'delay_ocs'=>$delay_ocs))) {
                  $message = $ocs_messages[$type][$entity];
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
                                ":  Send ocsmachines alert failed\n");
                  } else {
                     Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",$entity).
                                             ":  Send ocsmachines alert failed",false,ERROR);
                  }
               }
            }
         }
      }
      return $cron_status;
   }
   
   static function cronAdditionalalertsNewOcs($task=NULL) {
      global $DB,$CFG_GLPI;
      
      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }
      
      $CronTask=new CronTask();
      if ($CronTask->getFromDBbyName("PluginAdditionalalertsOcsAlert","AdditionalalertsNewOcs")) {
         if ($CronTask->fields["state"]==CronTask::STATE_DISABLE) {
            return 0;
         }
      } else {
         return 0;
      }
         
      $message=array();
      $cron_status = 0;
      
      foreach (self::getEntitiesToNotify('use_newocs_alert') as $entity => $repeat) {
         foreach ($DB->request("glpi_plugin_ocsinventoryng_ocsservers","`is_active` = 1") as $config) {
            $query_newocsmachine = self::queryNew($config,$entity);

            $newocsmachine_infos = array();
            $newocsmachine_messages = array();
            
            $type = Alert::END;
            $newocsmachine_infos[$type] = array();
            foreach ($DB->request($query_newocsmachine) as $data) {
            
               $entity = $data['entities_id'];
               $message = $data["name"];
               $newocsmachine_infos[$type][$entity][] = $data;

               if (!isset($newocsmachines_infos[$type][$entity])) {
                  $newocsmachine_messages[$type][$entity] = __('New imported computers from OCS-NG', 'additionalalerts')."<br />";
               }
               $newocsmachine_messages[$type][$entity] .= $message;
            }
            
            $delay_ocs = 0;
            foreach ($newocsmachine_infos[$type] as $entity => $newocsmachines) {
               Plugin::loadLang('additionalalerts');
               
               if (NotificationEvent::raiseEvent("newocs",
                                                 new PluginAdditionalalertsOcsAlert(),
                                                 array('entities_id'=>$entity,
                                                       'ocsmachines'=>$newocsmachines,
                                                       'delay_ocs'=>$delay_ocs))) {
                  $message = $newocsmachine_messages[$type][$entity];
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
                                ":  Send newocsmachines alert failed\n");
                  } else {
                     Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",$entity).
                                             ":  Send newocsmachines alert failed",false,ERROR);
                  }
               }
            }
         }
      }
      return $cron_status;
   }
   
   static function configCron($target,$ID) {

      echo "<div align='center'>";
      echo "<form method='post' action=\"$target\">";
      echo "<table class='tab_cadre_fixe' cellpadding='5'>";
      $colspan=2;
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Parameter', 'additionalalerts')."</td>";
      echo "<td>".__('Status used by OCS-NG', 'additionalalerts');
      Dropdown::show('State', array('name' => "states_id"));
      echo "&nbsp;<input type='submit' name='add_state' value=\""._sx('button','Add')."\" class='submit' ></div></td>";
      echo "</tr>";
      echo "</table>";
      Html::closeForm();

      echo "</div>";
         
      $state = new PluginAdditionalalertsNotificationState();
      $state->showForm($target);
 
   }
   
   function getFromDBbyEntity($entities_id) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `entities_id` = '$entities_id'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
         return false;
      }
      return false;
   }
   
   static function showNotificationOptions(Entity $entity) {

      $con_spotted = false;

      $ID = $entity->getField('id');
      if (!$entity->can($ID,READ)) {
         return false;
      }

      // Notification right applied
      $canedit = Session::haveRight('notification',UPDATE) && Session::haveAccessToEntity($ID);

      // Get data
      $entitynotification=new PluginAdditionalalertsOcsAlert();
      if (!$entitynotification->getFromDBbyEntity($ID)) {
         $entitynotification->getEmpty();
      }

      if ($canedit) {
         echo "<form method='post' name=form action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      }
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr class='tab_bg_1'><td>" . __('New imported computers from OCS-NG', 'additionalalerts') . "</td><td>";
      $default_value = $entitynotification->fields['use_newocs_alert'];
      Alert::dropdownYesNo(array('name'           => "use_newocs_alert",
                                 'value'          => $default_value,
                                 'inherit_global' => 1));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td >" . __('OCS-NG Synchronization alerts', 'additionalalerts') . "</td><td>";
      Alert::dropdownIntegerNever('delay_ocs', $entitynotification->fields["delay_ocs"],
                                  array('max'            => 99,
                                        'inherit_global' => 1));
      echo "&nbsp;"._n('Day','Days',2)."</td>";
      echo "</tr>";

      if ($canedit) {
         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='4'>";
         echo "<input type='hidden' name='entities_id' value='$ID'>";
         if ($entitynotification->fields["id"]) {
            echo "<input type='hidden' name='id' value=\"".$entitynotification->fields["id"]."\">";
            echo "<input type='submit' name='update' value=\""._sx('button','Save')."\" class='submit' >";
         } else {
            echo "<input type='submit' name='add' value=\""._sx('button','Save')."\" class='submit' >";
         }
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
      } else {
         echo "</table>";
      }
   }
}

?>