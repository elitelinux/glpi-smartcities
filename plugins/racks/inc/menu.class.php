<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Racks plugin for GLPI
 Copyright (C) 2003-2011 by the Racks Development Team.

 https://forge.indepnet.net/projects/racks
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Racks.

 Racks is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Racks is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Racks. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

class PluginRacksMenu extends CommonGLPI {

   static $rightname = 'plugin_racks';

   static function getMenuName($nb = 1) {
      return _n('Rack enclosure', 'Rack enclosures', 
                 $nb, 'racks');
   }

   static function getMenuContent() {
      global $CFG_GLPI;
      
      $menu                     = array();
      //Menu entry in tools
      $menu['title']            = self::getMenuName(2);
      $menu['page']             = PluginRacksRack::getSearchURL(false);
      $menu['links']['search']  = PluginRacksRack::getSearchURL(false);
      
      $menu['options']['racks']['links']['search'] = PluginRacksRack::getSearchURL(false);
      $menu['options']['racks']['links']['config'] = PluginRacksConfig::getFormURL(false);
      
      $menu['options']['config']['title'] = __('Setup');
      $menu['options']['config']['page']  = PluginRacksConfig::getSearchURL(false);

      $menu['options']['specifications']['title']           = __('Specifications', 'racks');
      $menu['options']['specifications']['page']            = PluginRacksItemSpecification::getSearchURL(false);
      $menu['options']['specifications']['links']['search'] = PluginRacksItemSpecification::getSearchURL(false);

      if (PluginRacksRack::canCreate()) {
         $menu['options']['racks']['links']['add'] = PluginRacksRack::getFormURL(false);
      }
      
      if (PluginRacksRackModel::canView()) {
         $menu['options']['racks']['links']['template'] = '/plugins/racks/front/setup.templates.php?add=0';
         $menu['options']['racks']['links']["<img  src='".
         $CFG_GLPI["root_doc"]."/pics/menu_showall.png' title=\"".__('Equipments models specifications', 'racks').
         "\" alt=\"".__('Equipments models specifications', 'racks')."\">"] = PluginRacksItemSpecification::getSearchURL(false);
      }

      return $menu;
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['tools']['types']['PluginRacksMenu'])) {
         unset($_SESSION['glpimenu']['tools']['types']['PluginRacksMenu']); 
      }
      if (isset($_SESSION['glpimenu']['tools']['content']['pluginracksmenu'])) {
         unset($_SESSION['glpimenu']['tools']['content']['pluginracksmenu']); 
      }
   }
}