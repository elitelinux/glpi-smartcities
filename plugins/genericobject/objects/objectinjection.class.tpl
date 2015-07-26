<?php
/*
 This file is part of the genericobject plugin.

 Genericobject plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Genericobject plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Genericobject. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   genericobject
 @author    the genericobject plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/genericobject
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

/**
 * This file is automatically managed by genericobject plugin. Do not edit it !
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class %%INJECTIONCLASS%% extends %%CLASSNAME%%
                                                implements PluginDatainjectionInjectionInterface {

   function __construct() {
      $this->table = getTableForItemType(get_parent_class($this));
   }

   static function getTable() {

      $parenttype = get_parent_class();
      return $parenttype::getTable();

   }

   function isPrimaryType() {
      return true;
   }

   function connectedTo() {
      return array();
   }
   
   /**
    * Standard method to add an object into glpi
    *
    * @param values fields to add into glpi
    * @param options options used during creation
    * @return an array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
    *
   **/
   function addOrUpdateObject($values=array(), $options=array()) {

      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }

   function getOptions($primary_type = '') {
      $parent = get_parent_class($this);
      $parentclass = new $parent();
      return $parentclass->getObjectSearchOptions(true);
   }
   
}