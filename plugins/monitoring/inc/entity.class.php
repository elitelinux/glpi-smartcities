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

class PluginMonitoringEntity extends CommonDBTM {


   static $rightname = 'entity';

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return "entity";
   }



   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      $array_ret = array();
      if ($item->getID() > -1) {
         if (Session::haveRight("entity", READ)) {
            $array_ret[0] = self::createTabEntry(__('Monitoring', 'monitoring'));
         }
      }
      return $array_ret;
   }



   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getID() > -1) {
         $pmEntity = new PluginMonitoringEntity();
         $pmHostconfig = new PluginMonitoringHostconfig();

         $pmHostconfig->showForm($item->getID(), "Entity");
         $pmEntity->showForm($item->fields['id']);
      }
      return true;
   }



   /**
   * Display form for entity tag
   *
   * @param $items_id integer ID of the entity
   * @param $options array
   *
   *@return bool true if form is ok
   *
   **/
   function showForm($items_id, $options=array()) {
      global $DB,$CFG_GLPI;

      $a_entities = $this->find("`entities_id`='".$items_id."'", "", 1);
      if (count($a_entities) == '0') {
         $input = array();
         $input['entities_id'] = $items_id;
         $id = $this->add($input);
         $this->getFromDB($id);
      } else {
         $a_entity = current($a_entities);
         $this->getFromDB($a_entity['id']);
      }

      echo "<form name='form' method='post'
         action='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/entity.form.php'>";

      echo "<table class='tab_cadre_fixe'";

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'>";
      echo __('Set tag to link entity with a specific Shinken server', 'monitoring');
      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Tag', 'monitoring')." :</td>";
      echo "<td>";
      echo "<input type='text' name='tag' value='".$this->fields["tag"]."' size='30'/>";

      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' align='center'>";
      echo "<input type='hidden' name='id' value='".$this->fields['id']."'/>";
      echo "<input type='submit' name='update' value=\"".__('Save')."\" class='submit'>";
      echo "</td>";
      echo "</tr>";

      echo "</table>";
      Html::closeForm();

      return true;
   }



   function getEntitiesByTag($tag = '') {
      global $DB;

      if ($tag == '') {
         return array('-1' => "-1");
      } else {
         $output = array();
         $query = "SELECT * FROM `".$this->getTable()."`
            WHERE `tag`='".$tag."'";
         $result = $DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $output[$data['entities_id']] = $data['entities_id'];
         }
         return $output;
      }
   }



   static function getTagByEntities($entities_id) {
      global $DB;

      $query = "SELECT * FROM `glpi_plugin_monitoring_entities`
         WHERE `entities_id`='".$entities_id."'
            LIMIT 1";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         return $data['tag'];
      }
   }

}

?>