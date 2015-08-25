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

class PluginMonitoringContact extends CommonDBTM {

   static $rightname = 'plugin_monitoring_componentscatalog';

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Contact', 'monitoring');
   }



   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      $array_ret = array();
      if (($item->getID() > 0) && (PluginMonitoringContact::canView())) {
         $array_ret[0] = self::createTabEntry(
                 __('Monitoring', 'monitoring')."-".__('Contact', 'monitoring'));
      }
      return $array_ret;
   }



   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getID() > 0) {
         $pmContact = new PluginMonitoringContact();
         $pmContact->showForm(0);
      }
      return true;
   }


   /**
   * Display form for agent configuration
   *
   * @param $items_id integer ID
   * @param $options array
   *
   *@return bool true if form is ok
   *
   **/
   function showForm($items_id, $options=array()) {
      global $DB,$CFG_GLPI;

      if ($items_id == '0') {
         $a_list = $this->find("`users_id`='".$_GET['id']."'", '', 1);
         if (count($a_list)) {
            $array = current($a_list);
            $items_id = $array['id'];
         }
      }

      if ($items_id != '0') {
         $this->getFromDB($items_id);
      } else {
         $this->getEmpty();
      }

//      $this->initForm($items_id, $options);
      $this->showFormHeader($options);

      if ($items_id!='') {
         $this->getFromDB($items_id);

         echo "<tr class='tab_bg_1'>";
         echo "<td>".__('Template name')."&nbsp;:</td>";
         echo "<td align='center'>";
         Dropdown::show("PluginMonitoringContacttemplate",
                 array('name' => 'plugin_monitoring_contacttemplates_id',
                       'value'=> $this->fields['plugin_monitoring_contacttemplates_id']));
         echo "</td>";
         echo "<td colspan='2'>";
         echo "</td>";
         echo "</tr>";


         $this->showFormButtons($options);
      } else {
         // Add button for host creation
         echo "<tr>";
         echo "<td colspan='4' align='center'>";
         echo "<input name='users_id' type='hidden' value='".$_GET['id']."' />";
         echo "<input name='add' value='".__('Manage this user for monitoring system', 'monitoring')."' class='submit' type='submit'></td>";
         echo "</tr>";
         $this->showFormButtons(array('canedit'=>false));
      }

      return true;
   }


}

?>