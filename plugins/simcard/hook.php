<?php
/*
 * @version $Id$
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

/**
 * 
 * Determine if the plugin should be installed or upgraded
 * 
 * Returns 0 if the plugin is not yet installed
 * Returns 1 if the plugin is already installed
 * 
 * @since 1.3
 * 
 * @return number
 */
function plugin_simcard_currentVersion() {
   
   // Saves the current version to not re-detect it on multiple calls
   static $currentVersion = null;
   
   if ($currentVersion === null) {
      // result not cached
      if (
         !TableExists('glpi_plugin_simcard_simcards_items') && 
         !TableExists('glpi_plugin_simcard_configs')
      ) {
         // the plugin seems not installed
         $currentVersion = 0;
      } else {      
         if (TableExists('glpi_plugin_simcard_configs')) {
            // The plugin is at least 1.3
            // Get the current version in the plugin's configuration
            $pluginSimcardConfig = new PluginSimcardConfig();
            $currentVersion = $pluginSimcardConfig->getValue('Version');
         }
         if ($currentVersion == '') {
            // The plugin is older than 1.3
            $currentVersion = '1.2';
         }
      }
   }
   return $currentVersion;
}

function plugin_simcard_install() {
   include_once (GLPI_ROOT."/plugins/simcard/inc/profile.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcard.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcardsize.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcardvoltage.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcardtype.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/phoneoperator.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcard_item.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/config.class.php");
    
   $migration = new Migration(PLUGIN_SIMCARD_VERSION);
   
   if (plugin_simcard_currentVersion() == 0) {
         // Installation of the plugin
         PluginSimcardConfig::install($migration);
         PluginSimcardProfile::install($migration);
         PluginSimcardSimcard::install($migration);
         PluginSimcardSimcardSize::install($migration);
         PluginSimcardSimcardVoltage::install($migration);
         PluginSimcardSimcardType::install($migration);
         PluginSimcardPhoneOperator::install($migration);
         PluginSimcardSimcard_Item::install($migration);
   } else {
         PluginSimcardConfig::upgrade($migration);
         PluginSimcardProfile::upgrade($migration);
         PluginSimcardSimcard::upgrade($migration);
         PluginSimcardSimcardSize::upgrade($migration);
         PluginSimcardSimcardVoltage::upgrade($migration);
         PluginSimcardSimcardType::upgrade($migration);
         PluginSimcardPhoneOperator::upgrade($migration);
         PluginSimcardSimcard_Item::upgrade($migration);
          
   }
   return true;
}

function plugin_simcard_uninstall() {
   include_once (GLPI_ROOT."/plugins/simcard/inc/profile.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcard.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcardsize.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcardvoltage.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcardtype.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/phoneoperator.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcard_item.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/config.class.php");
    
   PluginSimcardProfile::uninstall();
   PluginSimcardSimcard::uninstall();
   PluginSimcardSimcardSize::uninstall();
   PluginSimcardSimcardVoltage::uninstall();
   PluginSimcardSimcardType::uninstall();
   PluginSimcardPhoneOperator::uninstall();
   PluginSimcardSimcard_Item::uninstall();
   PluginSimcardConfig::uninstall();
   return true;
}

// Define dropdown relations
function plugin_simcard_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("simcard")) {
      return array(
                  "glpi_plugin_simcard_simcardsizes"
                     => array("glpi_plugin_simcard_simcards"=>"plugin_simcard_simcardsizes_id"),
                  "glpi_plugin_simcard_simcardvoltages"
                     => array("glpi_plugin_simcard_simcards"=>"plugin_simcard_simcardvoltages_id"),
                  "glpi_plugin_simcard_phoneoperators"
                     => array("glpi_plugin_simcard_simcards"=>"plugin_simcard_phoneoperators_id"),
                  "glpi_plugin_simcard_simcardtypes"
                     => array("glpi_plugin_simcard_simcards"=>"plugin_simcard_simcardtypes_id"),
                  "glpi_users" => array("glpi_plugin_simcard_simcards"=>"users_id"),
                  "glpi_users" => array("glpi_plugin_simcard_simcards"=>"users_id_tech"),
                  "glpi_groups" => array("glpi_plugin_simcard_simcards"=>"groups_id"),
                  "glpi_groups" => array("glpi_plugin_simcard_simcards"=>"groups_id_tech"),
                  "glpi_manufacturers" => array("glpi_plugin_simcard_simcards" => "manufacturers_id"),
                  "glpi_states" => array("glpi_plugin_simcard_simcards" => "states_id"),
                  "glpi_locations" => array("glpi_plugin_simcard_simcards"=>"locations_id"),
                  "glpi_profiles" => array ("glpi_plugin_simcard_profiles" => "profiles_id"));
   } else {
      return array();
   }
}


// Define Dropdown tables to be manage in GLPI :
function plugin_simcard_getDropdown() {
   global $LANG;

   $plugin = new Plugin();
   if ($plugin->isActivated("simcard")) {
     return array('PluginSimcardSimcardSize'    => __('Size', 'simcard'),
                   'PluginSimcardPhoneOperator'  => __('Provider', 'simcard'),
                   'PluginSimcardSimcardVoltage' => __('Voltage', 'simcard'),
                   'PluginSimcardSimcardType' => __('Type of SIM card', 'simcard'));
   } else {
      return array();
   }

}

function plugin_simcard_AssignToTicket($types) {
   global $LANG;

   //if (Session::haveRight("simcard_open_ticket", "1")) {
   if (Session::haveRight(PluginSimcardProfile::RIGHT_SIMCARD_SIMCARD, PluginSimcardSimcard::RIGHT_SIMCARD_OPEN_TICKET)) {
      $types['PluginSimcardSimcard'] = _sn('SIM card', 'SIM cards', 2, 'simcard');
   }

   return $types;
}

//force groupby for multible links to items

function plugin_simcard_forceGroupBy($type) {

   return true;
   switch ($type) {
      case 'PluginSimcardSimcard':
         return true;
         break;

   }
   return false;
}


function plugin_simcard_getAddSearchOptions($itemtype) {
   global $LANG;
    
   $sopt = array();

   $reservedTypeIndex = PluginSimcardConfig::RESERVED_TYPE_RANGE_MIN;
   
   if (in_array($itemtype,PluginSimcardSimcard_Item::getClasses())) {
      //if (Session::haveRight("simcard","r")) {
   	  if (PluginSimcardSimcard::canView()) {
         $sopt[$reservedTypeIndex]['table']         = 'glpi_plugin_simcard_simcards';
         $sopt[$reservedTypeIndex]['field']         = 'name';
         $sopt[$reservedTypeIndex]['name']          = _sn('SIM card', 'SIM cards', 2, 'simcard')." - ".__s('Name');
         $sopt[$reservedTypeIndex]['forcegroupby']  = true;
         $sopt[$reservedTypeIndex]['massiveaction'] = false;
         $sopt[$reservedTypeIndex]['datatype']      = 'itemlink';
         $sopt[$reservedTypeIndex]['itemlink_type'] = 'PluginSimcardSimcard';
         $sopt[$reservedTypeIndex]['joinparams']    = array('beforejoin'
                                                            => array('table'      => 'glpi_plugin_simcard_simcards_items',
                                                            'joinparams' => array('jointype' => 'itemtype_item')));
         $reservedTypeIndex++;
         $sopt[$reservedTypeIndex]['table']         = 'glpi_plugin_simcard_simcards';
         $sopt[$reservedTypeIndex]['field']         = 'phonenumber';
         $sopt[$reservedTypeIndex]['name']          = _sn('SIM card', 'SIM cards', 2, 'simcard')." - ".__s('Phone number', 'simcard');
         $sopt[$reservedTypeIndex]['massiveaction'] = false;
         $sopt[$reservedTypeIndex]['forcegroupby']  = true;
         $sopt[$reservedTypeIndex]['joinparams']    = array('beforejoin'
                                                            => array('table'      => 'glpi_plugin_simcard_simcards_items',
                                                            'joinparams' => array('jointype' => 'itemtype_item')));
         $reservedTypeIndex++;
         $sopt[$reservedTypeIndex]['table']         = 'glpi_plugin_simcard_simcards';
         $sopt[$reservedTypeIndex]['field']         = 'serial';
         $sopt[$reservedTypeIndex]['name']          = _sn('SIM card', 'SIM cards', 2, 'simcard')." - ".__s('Voltage', 'simcard');
         $sopt[$reservedTypeIndex]['massiveaction'] = false;
         $sopt[$reservedTypeIndex]['forcegroupby']  = true;
         $sopt[$reservedTypeIndex]['joinparams']    = array('beforejoin'
                                                            => array('table'      => 'glpi_plugin_simcard_simcards_items',
                                                            'joinparams' => array('jointype' => 'itemtype_item')));
   	  }
   }
   return $sopt;
}


// Hook done on purge item case

function plugin_item_purge_simcard($item) {

   $temp = new PluginSimcardSimcard_Item();
   $temp->deleteByCriteria(array('itemtype' => get_class($item),
                                 'items_id' => $item->getField('id')));
   return true;
}

function plugin_datainjection_populate_simcard() {
   global $INJECTABLE_TYPES;
   
   $INJECTABLE_TYPES['PluginSimcardSimcardInjection']      = 'simcard';
}

/**
 * 
 * Determine if the plugin should be installed or upgraded
 * 
 * Returns 0 if the plugin is not yet installed
 * Returns 1 if the plugin is already installed
 * 
 * @since 1.3
 */
function plugin_simcard_postinit() {
   global $UNINSTALL_TYPES, $ORDER_TYPES, $ALL_CUSTOMFIELDS_TYPES, $DB;
   $plugin = new Plugin();
   if ($plugin->isInstalled('uninstall') && $plugin->isActivated('uninstall')) {
      array_push($UNINSTALL_TYPES, 'PluginSimcardSimcard');
   }
   if ($plugin->isInstalled('order') && $plugin->isActivated('order')) {
      array_push($ORDER_TYPES, 'PluginSimcardSimcard');
   }
   if ($plugin->isInstalled('customfields') && $plugin->isActivated('customfields')) {
      PluginCustomfieldsItemtype::registerItemtype('PluginSimcardSimcard');
   }
}
?>
