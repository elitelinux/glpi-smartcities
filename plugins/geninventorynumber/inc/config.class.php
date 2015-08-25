<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
 LICENSE

 This file is part of the geninventorynumber plugin.

 geninventorynumber plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 geninventorynumber plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with geninventorynumber. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   geninventorynumber
 @author    the geninventorynumber plugin team
 @copyright Copyright (c) 2008-2013 geninventorynumber plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/geninventorynumber
 @link      http://www.glpi-project.org/
 @since     2008
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginGeninventorynumberConfig extends CommonDBTM {

//   var $dohistory = true;

   static $rightname = 'config';
   public $dohistory = true;   

   static function getTypeName($nb=0) {
      return __('geninventorynumber', 'geninventorynumber');
   }

   function defineTabs($options=array()) {
      $ong = array();
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab("PluginGeninventorynumberConfigField", $ong, $options);
      $this->addStandardTab("Log", $ong, $options);
      return $ong;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if (get_class($item) == __CLASS__) {
      	$array_ret = array();
      	$array_ret[0] = __('General setup');
      	$array_ret[1] = __('PerDeviceTypeConfiguration', 'geninventorynumber');
      	return $array_ret;
      }
      return '';
   }
    
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      switch ($tabnum) {
         case 0:
            $item->showForm(1);
            break;
         case 1:
          PluginGeninventorynumberConfigField::showForConfig($item->getID());
            break;
      }
      return TRUE;
    }

   function getSearchOptions() {

      $sopt = array();
      $sopt['common'] = __('geninventorynumber', 'geninventorynumber');

      $sopt[1]['table'] = $this->getTable();
      $sopt[1]['field'] = 'name';
      $sopt[1]['name'] = __('Field');
      $sopt[1]['datatype'] = 'itemlink';

      $sopt[2]['table'] = $this->getTable();
      $sopt[2]['field'] = 'is_active';
      $sopt[2]['name'] = __('Active', 'geninventorynumber');
      $sopt[2]['datatype'] = 'bool';

      $sopt[3]['table'] = $this->getTable();
      $sopt[3]['field'] = 'comment';
      $sopt[3]['name'] = __('Comments');

      $sopt[3]['table'] = $this->getTable();
      $sopt[3]['field'] = 'index';
      $sopt[3]['name'] = __('IndexPosition', 'geninventorynumber');

      return $sopt;
   }

   function showForm($id, $options=array()) {

      global $CFG_GLPI;

      if ($id > 0) {
	       $this->getFromDB($id);
      } else {
	       // Create item
	       $this->getEmpty();
      }

      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td class='tab_bg_1' align='center'>" . __('Field') . "</td>";
      echo "<td class='tab_bg_1'>";
      echo $this->getName();
      //Html::autocompletionTextField($this, "name");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td class='tab_bg_1' align='center'>" .
   	   __('GenerationEnabled', 'geninventorynumber') . "</td>";
      echo "<td class='tab_bg_1'>";
      Dropdown::showYesNo("is_active", $this->fields["is_active"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td class='tab_bg_1' align='center'>" .
	       __('IndexPosition', 'geninventorynumber') . " " . __('Global') . "</td>";
      echo "<td class='tab_bg_1'>";
      echo "<input type='text' name='index' value='" . $this->fields["index"] . "' size='12'>&nbsp;";
      echo "</td><td colspan='2'></td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td class='tab_bg_1' colspan='4'>";
      echo "<table>";
      echo "<tr>";
      echo "<td class='tab_bg_1'>" . __('Comments') . "</td><td>";
      echo "<textarea cols='60' rows='4' name='comment' >" . $this->fields["comment"] . "</textarea>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      echo "</td>";
      echo "</tr>";
      $options['candel'] = false;
      $this->showFormButtons($options);
      return true;
   }

   static function getNextIndex() {
      global $DB;

      $query = "SELECT `index`
	             FROM `".getTableForItemType(__CLASS__)."`";
      $results = $DB->query($query);
      if ($DB->numrows($results)) {
	      return ($DB->result($results, 0 , 'index') + 1);
      } else {
	      return 0;
      }
   }

   static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);

      if (TableExists("glpi_plugin_generateinventorynumber_config")) {
         $fields = array('template_computer', 'template_monitor', 'template_printer',
      	                'template_peripheral', 'template_phone' , 'template_networking',
      	                'generate_ocs', 'generate_data_injection', 'generate_internal',
      	                'computer_gen_enabled', 'monitor_gen_enabled', 'printer_gen_enabled',
      	                'peripheral_gen_enabled', 'phone_gen_enabled', 'networking_gen_enabled',
      	                'computer_global_index', 'monitor_global_index', 'printer_global_index',
      	                'peripheral_global_index', 'phone_global_index',
      	                'networking_global_index');
      	foreach ($fields as $field) {
      	   $migration->dropField("glpi_plugin_generateinventorynumber_config", $field);
      	}
      	$migration->renameTable("glpi_plugin_generateinventorynumber_config", $table);
      }

      if (TableExists("glpi_plugin_geninventorynumber_config")) {
	      $migration->renameTable("glpi_plugin_geninventorynumber_config", $table);
      }

      if (!TableExists($table)) {
      	$sql = "CREATE TABLE IF NOT EXISTS `$table` (
      	    `id` int(11) NOT NULL auto_increment,
      	    `name`  varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
      	    `entities_id` int(11)  NOT NULL default '-1',
      	    `is_active` tinyint(1)  NOT NULL default 0,
      	    `index` int(11)  NOT NULL default 0,
      	    `comment` text COLLATE utf8_unicode_ci,
      	    PRIMARY KEY  (`id`)
      	    ) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($sql) or die($DB->error());

        $tmp['id']           = 1;
        $tmp['name']         = 'otherserial';
        $tmp['is_active']    = 1;
        $tmp['entities_id']  = 0;
        $tmp['index']        = 0;
        $config = new self();
        $config->add($tmp);
      } else {
        $migration->addField($table, 'name', 'string', array('value' => 'otherserial'));
        $migration->addField($table, 'field', 'string', array('value' => 'otherserial'));
        $migration->changeField($table, 'ID', 'id', 'autoincrement');
        $migration->changeField($table, 'FK_entities', 'entities_id', 'integer', array('value' => -1));
        $migration->changeField($table, 'active', 'is_active', 'bool');
	     if (!$migration->addField($table, 'comment', 'text')) {
	       $migration->changeField($table, 'comments', 'comment', 'text');
	     }
	     $migration->changeField($table, 'is_active', 'is_active', 'bool');
	     $migration->changeField($table, 'next_number', 'index', 'integer');
	     $migration->dropField($table, 'field');
      }

      //Remove unused table
      if (TableExists('glpi_plugin_geninventorynumber_indexes')) {
	      $migration->dropTable('glpi_plugin_geninventorynumber_indexes');
      }
      $migration->migrationOneTable($table);
   }

   static function uninstall(Migration $migration) {
      $migration->dropTable(getTableForItemType(__CLASS__));
   }

   static function updateIndex() {
      global $DB;

      $query = "UPDATE `".getTableForItemType(__CLASS__)."`
	             SET `index`=`index`+1";
      $DB->query($query);
   }

   static function isGenerationActive() {
      $config = new self();
      $config->getFromDB(1);
      return $config->fields['is_active'];
   }
}