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

class PluginAdditionalalertsInfocomAlert extends CommonDBTM {
   
   static $rightname = "plugin_additionalalerts";
   
   static function getTypeName($nb=0) {

      return _n('Computer with no buy date', 'Computers with no buy date', $nb, 'additionalalerts');
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='CronTask' && $item->getField('name')=="AdditionalalertsNotInfocom") {
            return __('Plugin setup', 'additionalalerts');
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='CronTask') {

         $target = $CFG_GLPI["root_doc"]."/plugins/additionalalerts/front/infocomalert.form.php";
         self::configCron($target,$item->getField('id'));
      }
      return true;
   }
   
   // Cron action
   static function cronInfo($name) {

      switch ($name) {
         case 'AdditionalalertsNotInfocom':
            return array (
               'description' => PluginAdditionalalertsInfocomAlert::getTypeName(2));   // Optional
            break;
      }
      return array();
   }
   
   static function query($entity) {
      global $DB;

      $query = "SELECT `glpi_computers`.*
      FROM `glpi_computers`
      LEFT JOIN `glpi_infocoms` ON (`glpi_computers`.`id` = `glpi_infocoms`.`items_id` AND `glpi_infocoms`.`itemtype` = 'Computer')
      WHERE `glpi_computers`.`is_deleted` = 0
      AND `glpi_computers`.`is_template` = 0
      AND `glpi_infocoms`.`buy_date` IS NULL ";
      $query_type = "SELECT `types_id`
      FROM `glpi_plugin_additionalalerts_notificationtypes` ";
      $result_type = $DB->query($query_type);
      
      if ($DB->numrows($result_type)>0) {
         $query .= " AND (`glpi_computers`.`computertypes_id` != 0 ";
         while ($data_type=$DB->fetch_array($result_type)) {
            $type_where="AND `glpi_computers`.`computertypes_id` != '".$data_type["types_id"]."' ";
            $query .= " $type_where ";
         }
         $query .= ") ";
      }
      $query .= "AND `glpi_computers`.`entities_id`= '".$entity."' ";

      $query .= " ORDER BY `glpi_computers`.`name` ASC";

      return $query;
   }
   
      
   static function displayBody($data) {
      global $CFG_GLPI;

      $body="<tr class='tab_bg_2'><td><a href=\"".$CFG_GLPI["root_doc"]."/front/computer.form.php?id=".$data["id"]."\">".$data["name"];

      if ($_SESSION["glpiis_ids_visible"] == 1 || empty($data["name"])) {
         $body.=" (";
         $body.=$data["id"].")";
      }
      $body.="</a></td>";
      if (Session::isMultiEntitiesMode())
         $body.="<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."</td>";
      $body.="<td>".Dropdown::getDropdownName("glpi_computertypes",$data["computertypes_id"])."</td>";
      $body.="<td>".Dropdown::getDropdownName("glpi_operatingsystems",$data["operatingsystems_id"])."</td>";
      $body.="<td>".Dropdown::getDropdownName("glpi_states",$data["states_id"])."</td>";
      $body.="<td>".Dropdown::getDropdownName("glpi_locations",$data["locations_id"])."</td>";
      $body.="<td>";
      if (!empty($data["users_id"])) {

         $body.="<a href=\"".$CFG_GLPI["root_doc"]."/front/user.form.php?id=".$data["users_id"]."\">".getUserName($data["users_id"])."</a>";
 
      }
      if (!empty($data["groups_id"])) {
         
         $body.=" - <a href=\"".$CFG_GLPI["root_doc"]."/front/group.form.php?id=".$data["groups_id"]."\">";

         $body.=Dropdown::getDropdownName("glpi_groups",$data["groups_id"]);
         if ($_SESSION["glpiis_ids_visible"] == 1 ) {
            $body.=" (";
            $body.=$data["groups_id"].")";
         }
         $body.="</a>";
      }
      if (!empty($data["contact"]))
         $body.=" - ".$data["contact"];

      $body.="</td>";
      $body.="</tr>";
      
      return $body;
   }
   
   
   static function getEntitiesToNotify($field,$with_value=false) {
      global $DB;

      $query = "SELECT `entities_id` as `entity`,`$field`
               FROM `glpi_plugin_additionalalerts_infocomalerts`";
      $query.= " ORDER BY `entities_id` ASC";

      $entities = array();
      foreach ($DB->request($query) as $entitydatas) {
         PluginAdditionalalertsInfocomAlert::getDefaultValueForNotification($field,$entities, $entitydatas);
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
   /**
    * Cron action
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronAdditionalalertsNotInfocom($task=NULL) {
      global $DB,$CFG_GLPI;
      
      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }
      
      $CronTask=new CronTask();
      if ($CronTask->getFromDBbyName("PluginAdditionalalertsInfocomAlert","AdditionalalertsNotInfocom")) {
         if ($CronTask->fields["state"]==CronTask::STATE_DISABLE) {
            return 0;
         }
      } else {
         return 0;
      }
         
      $message=array();
      $cron_status = 0;
      
      foreach (self::getEntitiesToNotify('use_infocom_alert') as $entity => $repeat) {
         $query_notinfocom = self::query($entity);

         $notinfocom_infos = array();
         $notinfocom_messages = array();
         
         $type = Alert::END;
         $notinfocom_infos[$type] = array();
         foreach ($DB->request($query_notinfocom) as $data) {
         
            $entity = $data['entities_id'];
            $message = $data["name"];
            $notinfocom_infos[$type][$entity][] = $data;

            if (!isset($notinfocoms_infos[$type][$entity])) {
               $notinfocom_messages[$type][$entity] = PluginAdditionalalertsInfocomAlert::getTypeName(2)."<br />";
            }
            $notinfocom_messages[$type][$entity] .= $message;
         }
         
         foreach ($notinfocom_infos[$type] as $entity => $notinfocoms) {
            Plugin::loadLang('additionalalerts');
            
            if (NotificationEvent::raiseEvent("notinfocom",
                                              new PluginAdditionalalertsInfocomAlert(),
                                              array('entities_id'=>$entity,
                                                    'notinfocoms'=>$notinfocoms))) {
               $message = $notinfocom_messages[$type][$entity];
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
                             ":  Send infocoms alert failed\n");
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",$entity).
                                          ":  Send infocoms alert failed",false,ERROR);
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
      echo "<td>".__('Type not used for check of buy date', 'additionalalerts');
      Dropdown::show('ComputerType', array('name' => "types_id"));
      echo "&nbsp;<input type='submit' name='add_type' value=\""._sx('button','Add')."\" class='submit' ></div></td>";
      echo "</tr>";
   
      echo "</table>";
      Html::closeForm();

      echo "</div>";
         
      $type = new PluginAdditionalalertsNotificationType();
      $type->showForm($target);

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
      $entitynotification=new PluginAdditionalalertsInfocomAlert();
      if (!$entitynotification->getFromDBbyEntity($ID)) {
         $entitynotification->getEmpty();
      }

      if ($canedit) {
         echo "<form method='post' name=form action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      }
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='2'>".__('Alarms options')."</th></tr>";

      echo "<tr class='tab_bg_1'><td>" . PluginAdditionalalertsInfocomAlert::getTypeName(2) . "</td><td>";
      $default_value = $entitynotification->fields['use_infocom_alert'];
      Alert::dropdownYesNo(array('name'           => "use_infocom_alert",
                                 'value'          => $default_value,
                                 'inherit_global' => 1));
      echo "</td></tr>";

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