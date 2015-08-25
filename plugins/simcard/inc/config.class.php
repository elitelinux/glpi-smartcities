<?php
/*
 * @version $Id: setup.php 36 2012-08-31 13:59:28Z walid $
 LICENSE

  This file is part of the simcard plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Simcard. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   simcard
 @author    the simcard plugin team
 @copyright Copyright (c) 2010-2011 Simcard plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/simcard
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */
 
 if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * 
 * @author dethegeek
 * @since 1.3
 *
 */
class PluginSimcardConfig extends CommonDBTM {

   // Type reservation : https://forge.indepnet.net/projects/plugins/wiki/PluginTypesReservation
   // Reserved range   : [10126, 10135]
   const RESERVED_TYPE_RANGE_MIN = 10126;
   const RESERVED_TYPE_RANGE_MAX = 10135;
   
   static $config = array();

   /**
    * 
    *
    * 
    **/
   static function install(Migration $migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
      $query = "CREATE TABLE `".$table."` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unicity` (`type`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
      $DB->query($query) or die($DB->error());
      $query = "INSERT INTO `".$table."` 
                (`type`,`value`)
               VALUES ('Version', " . PLUGIN_SIMCARD_VERSION . ")";
      $DB->query($query) or die($DB->error());
      
   }
   
   /**
    * 
    *
    * 
    **/
   static function upgrade(Migration $migration) {
      global $DB;
      
      switch (plugin_simcard_currentVersion()) {
      	case '1.2':
      	   self::install($migration);
      	   break;

      	default:
            $table = getTableForItemType(__CLASS__);
            $query = "UPDATE `".$table."`
                      SET `value`= '" . PLUGIN_SIMCARD_VERSION . "'
                      WHERE `type`='Version'";
            $DB->query($query) or die($DB->error());
      }
   }
    
   /**
    * 
    *
    * 
    **/
   static function uninstall() {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
      $query = "DROP TABLE IF EXISTS `". $table ."`";

      $DB->query($query) or die($DB->error());
   }

   /**
    * 
    *
    * 
    **/
   static function loadCache() {
      global $DB;
   
      $table = getTableForItemType(__CLASS__);
      self::$config = array();
      $query = "SELECT * FROM `". $table ."`";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         self::$config[$data['type']] = $data['value'];
      }
   }

   /**
    * Add configuration value, if not already present
    *
    * @param $name field name
    * @param $value field value
    *
    * @return integer the new id of the added item (or FALSE if fail)
    **/
   function addValue($name, $value) {
      $existing_value = $this->getValue($name);
      if (!is_null($existing_value)) {
         return false;
      } else {
         return $this->add(array('type'       => $name,
                                 'value'      => $value));
      }
   }

   /**
   * Get configuration value
   *
   * @param $name field name
   *
   * @return field value for an existing field, FALSE otherwise
   **/
   function getValue($name) {
      if (isset(self::$config[$name])) {
         return self::$config[$name];
      }

      $config = current($this->find("`type`='".$name."'"));
      if (isset($config['value'])) {
         return $config['value'];
      }
      return NULL;
   }
   
   /**
    * Update configuration value
    *
    * @param $name field name
    * @param $value field value
    *
    * @return boolean : TRUE on success
    **/
   function updateValue($name, $value) {
      $config = current($this->find("`type`='".$name."'"));
      if (isset($config['id'])) {
         return $this->update(array('id'=> $config['id'], 'value'=>$value));
      } else {
         return $this->add(array('type' => $name, 'value' => $value));
      }
   }
}
