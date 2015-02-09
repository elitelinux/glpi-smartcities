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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginRacksItemSpecificationCentral extends CommonGLPI {

   static $rightname = "plugin_racks_model";
   
   static function getTypeName($nb=0) {
      return __('Equipments models specifications', 'racks');
   }

   function defineTabs($options=array()) {
      $ong = array();
      $this->addStandardTab(__CLASS__, $ong, $options);
      return $ong;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item->getType() == __CLASS__) {
         $tabs[1] = __('Servers', 'racks');
         $tabs[2] = _n('Network equipment' , 'Network equipments', 2, 'racks');
         $tabs[3] = _n('Peripheral' , 'Peripherals', 2, 'racks');
         $tabs[4] = _n('Other equipment' , 'Others equipments', 2, 'racks');

         return $tabs;
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      $itemspecification = new PluginRacksItemSpecification();
      if ($item->getType() == __CLASS__) {
         switch ($tabnum) {
            case 1 :
               $itemspecification->showList('', -1 , 'ComputerModel');
               break;

            case 2 :
               $itemspecification->showList('', -1 , 'NetworkEquipmentModel');
               break;

            case 3 :
               $itemspecification->showList('', -1 , 'PeripheralModel');
               break;

            case 4 :
               $itemspecification->showList('', -1 , 'PluginRacksOtherModel');
               break;
         }
      }
      return true;
   }

}
?>