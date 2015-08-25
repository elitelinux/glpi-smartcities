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

class PluginMonitoringContact_Item extends CommonDBTM {


   static $rightname = 'plugin_monitoring_componentscatalog';

   static function getTypeName($nb=0) {
      return __('Contacts', 'monitoring');
   }



   function showContacts($itemtype, $items_id) {
      global $DB,$CFG_GLPI;

      $this->addContact($itemtype, $items_id);

      $group = new Group();
      $user  = new User();

      $rand = mt_rand();

      echo "<form method='post' name='contact_item_form$rand' id='contact_item_form$rand' action=\"".
                $CFG_GLPI["root_doc"]."/plugins/monitoring/front/contact_item.form.php\">";

      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      echo "<th>";
      echo __('Contacts', 'monitoring');
      echo "</th>";
      echo "</tr>";

      echo "</table>";

      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      echo "<th width='10'>&nbsp;</th>";
      echo "<th>".__('Group')." - ".__('Name')."</th>";
      echo "<th colspan='3'></th>";
      echo "</tr>";

      $used = array();
      // Display groups first
      $query = "SELECT * FROM `".$this->getTable()."`
         WHERE `items_id`='".$items_id."'
            AND `itemtype`='".$itemtype."'
            AND `groups_id` > 0";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $group->getFromDB($data['groups_id']);

         echo "<tr>";
         echo "<td>";
         echo "<input type='checkbox' name='item[".$data["id"]."]' value='1'>";
         echo "</td>";
         echo "<td class='center'>";
         echo $group->getLink(1);
         echo "</td>";
         echo "<td colspan='3'>";

         echo "</td>";

         echo "</tr>";
      }

      echo "<tr>";
      echo "<th width='10'>&nbsp;</th>";
      echo "<th>".__('User')." - ".__('Name')."</th>";
      echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Email address')."</th>";
      echo "<th>".__('Phone')."</th>";
      echo "</tr>";

      $entity = new Entity();
      $used = array();
      // Display Users
      $query = "SELECT * FROM `".$this->getTable()."`
         WHERE `items_id`='".$items_id."'
            AND `itemtype`='".$itemtype."'
            AND `users_id` > 0";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $user->getFromDB($data['users_id']);

         echo "<tr>";
         echo "<td>";
         echo "<input type='checkbox' name='item[".$data["id"]."]' value='1'>";
         echo "</td>";
         echo "<td class='center'>";
         echo $user->getLink(1);
         echo "</td>";
         $entity->getFromDB($data['entities_id']);
         echo "<td class='center'>";
         echo $entity->getName()." <strong>(R)</strong>";
         echo "</td>";
         echo "<td class='center'>";
         $a_emails = UserEmail::getAllForUser($data['users_id']);
         $first = 0;
         foreach ($a_emails as $email) {
            if ($first == 0) {
               echo $email;
            }
            $first++;
         }
         echo "</td>";
         echo "<td class='center'>";
         echo $user->fields['phone'];
         echo "</td>";

         echo "</tr>";
      }

      Html::openArrowMassives("contact_item_form$rand", true);
      Html::closeArrowMassives(array('deleteitem' => _sx('button', 'Delete permanently')));
      Html::closeForm();
      echo "</table>";

   }


   function addContact($itemtype, $items_id) {
      global $DB,$CFG_GLPI;

      $this->getEmpty();

      $this->showFormHeader();

      echo "<tr>";
      echo "<td>";
      echo __('User')."&nbsp;:";
      echo "<input type='hidden' name='items_id' value='".$items_id."'/>";
      echo "<input type='hidden' name='itemtype' value='".$itemtype."'/>";
      echo "</td>";
      echo "<td>";

      $paramscomment = array('value'  => '__VALUE__');

      $toupdate = array('users_id' => 'value',
                        'to_update'  => "show_entity",
                        'url'        => $CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/dropdownUserEntities.php",
                        'moreparams' => $paramscomment);

      Dropdown::show("User", array('name'=>'users_id', 'toupdate'=> $toupdate));

      echo "</td>";
      echo "<td>";
      echo __('Entity')." (".strtolower(__('Recursive')).")&nbsp;:";
      echo "</td>";
      echo "<td>";
      echo "<span id='show_entity'></span>\n";
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons();
      $this->showFormHeader();

      echo "<tr>";
      echo "<td>";
      echo __('Group')."&nbsp;:";
      echo "<input type='hidden' name='items_id' value='".$items_id."'/>";
      echo "<input type='hidden' name='itemtype' value='".$itemtype."'/>";
      echo "</td>";
      echo "<td>";
      Dropdown::show("Group", array('name'=>'groups_id'));
      echo "</td>";
      echo "<td colspan='2'>";
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons();
   }



}

?>