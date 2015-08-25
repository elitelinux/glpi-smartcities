<?php
/*
  -------------------------------------------------------------------------
  Moreticket plugin for GLPI
  Copyright (C) 2013 by the Moreticket Development Team.
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Moreticket.

  Moreticket is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Moreticket is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Moreticket. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMoreticketWaitingType extends commonTreeDropdown {
   
   static $rightname = "plugin_moreticket";
   
   public static function getTypeName($nb=0) {

      return _n('Waiting type', 'Waiting types', $nb, 'moreticket');
   }
   
   function getAdditionalFields() {

      $tab = array(array('name'  => $this->getForeignKeyField(),
                         'label' => __('As child of'),
                         'type'  => 'parent',
                         'list'  => false)
                  );

      return $tab;


   }
   
   function getSearchOptions() {

      $tab = parent::getSearchOptions();

      return $tab;
   }
}
?>