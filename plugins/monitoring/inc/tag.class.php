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
   @since     2013

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringTag extends CommonDBTM {


   static $rightname = 'plugin_monitoring_tag';

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Tag', 'monitoring');
   }



   /**
    * @since version 0.85
    *
    * @see commonDBTM::getRights()
    **/
   function getRights($interface='central') {

      $values = parent::getRights();
      unset($values[CREATE]);

      return $values;
   }



   function getSearchOptions() {
      $tab = array();

      $tab['common'] = __('Commands', 'monitoring');

		$tab[1]['table']     = $this->getTable();
		$tab[1]['field']     = 'tag';
		$tab[1]['linkfield'] = 'tag';
		$tab[1]['name']      = __('Shinken tag', 'monitoring');
      $tab[1]['datatype']  = 'itemlink';

		$tab[2]['table']     = $this->getTable();
		$tab[2]['field']     = 'ip';
		$tab[2]['linkfield'] = 'ip';
		$tab[2]['name']      = __('Shinken IP address', 'monitoring');

		$tab[3]['table']     = $this->getTable();
		$tab[3]['field']     = 'username';
		$tab[3]['linkfield'] = 'username';
		$tab[3]['name']      = __('Username (Shinken webservice)', 'monitoring');

		$tab[4]['table']     = $this->getTable();
		$tab[4]['field']     = 'password';
		$tab[4]['linkfield'] = 'password';
		$tab[4]['name']      = __('Password (Shinken webservice)', 'monitoring');

		$tab[5]['table']     = $this->getTable();
		$tab[5]['field']     = 'iplock';
		$tab[5]['linkfield'] = 'iplock';
		$tab[5]['name']      = __('Lock shinken address', 'monitoring');
      $tab[5]['datatype']  = 'bool';

		$tab[6]['table']     = $this->getTable();
		$tab[6]['field']     = 'port';
		$tab[6]['linkfield'] = 'port';
		$tab[6]['name']      = __('Port', 'monitoring');

      $tab[7]['table']     = $this->getTable();
      $tab[7]['field']     = 'comment';
      $tab[7]['name']      = __('Comments');
      $tab[7]['datatype']  = 'text';

      return $tab;
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
   function showForm($items_id, $options=array(), $copy=array()) {
      global $DB,$CFG_GLPI;

      $this->initForm($items_id, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Tag', 'monitoring')." :</td>";
      echo "<td>";
      echo $this->fields["tag"];
      echo "</td>";
      echo "<td>".__('Username (Shinken webservice)', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      Html::autocompletionTextField($this, 'username');
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Shinken IP address', 'monitoring')." :</td>";
      echo "<td>";
      Html::autocompletionTextField($this, 'ip');
      echo "</td>";
      echo "<td>".__('Password (Shinken webservice)', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      Html::autocompletionTextField($this, 'password');
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Lock shinken IP', 'monitoring')." :</td>";
      echo "<td>";
      Dropdown::showYesNo('iplock', $this->fields["iplock"]);
      echo "</td>";
      echo "<td rowspan='2'>".__('Comments')."</td>";
      echo "<td rowspan='2' class='middle'>";
      echo "<textarea cols='45' rows='3' name='comment' >".$this->fields["comment"];
      echo "</textarea></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Port', 'monitoring')." :</td>";
      echo "<td>";
      Html::autocompletionTextField($this, 'port', array('size' => 10));
      echo "</td>";
      echo "<td colspan='2'>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='4'>";
      echo "</td>";
      echo "</tr>";


      $this->showFormButtons($options);

      return true;
   }



   function setIP($tag, $ip) {
      if (!$this->isIPLocked($tag)) {
         $id = $this->getTagID($tag);
         $input= array();
         $input['id'] = $id;
         $input['ip'] = $ip;
         $this->update($input);
      }
   }



   function getIP($tag) {

      $a_tags = $this->find("`tag`='".$tag."'", '', 1);
      if (count($a_tags) == 1) {
         $a_tag = current($a_tags);
         return $a_tag['ip'];
      }
      return '';
   }



   function getPort($tag) {

      $a_tags = $this->find("`tag`='".$tag."'", '', 1);
      if (count($a_tags) == 1) {
         $a_tag = current($a_tags);
         return $a_tag['port'];
      }
      return '';
   }



   function getAuth($tag) {

      $a_tags = $this->find("`tag`='".$tag."'", '', 1);
      if (count($a_tags) == 1) {
         $a_tag = current($a_tags);
         return $a_tag['username'].":".$a_tag['password'];
      }
      return '';
   }



   function getTagID($tag) {

      $a_tags = $this->find("`tag`='".$tag."'", '', 1);
      if (count($a_tags) == 1) {
         $a_tag = current($a_tags);
         return $a_tag['id'];
      }

      return $this->add(array('tag' => $tag));
   }



   function isIPLocked($tag) {
      $a_tags = $this->find("`tag`='".$tag."'", '', 1);
      if (count($a_tags) == 1) {
         $a_tag = current($a_tags);
         return $a_tag['iplock'];
      }
      return FALSE;
   }
}

?>