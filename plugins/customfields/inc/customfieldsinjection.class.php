<?php
/*
 * @version $Id: customfieldinjection.class.php 196 2012-07-10 16:24:45Z remi $
 -------------------------------------------------------------------------
 customfields - CustomFields plugin for GLPI
 Copyright (C) 2003-2011 by the customfields Development Team.
 
 https://forge.indepnet.net/projects/customfields
 -------------------------------------------------------------------------
 
 LICENSE
 
 This file is part of customfields.
 
 customfields is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 
 customfields is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with customfields. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Walid Nouh
// Purpose of file: Handle calls from the Data injection plugin
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginCustomFieldsCustomFieldInjection
 *
 * This class adds support for the Data injection plugin handling import of
 * custom fields.
 */

class PluginCustomFieldsCustomFieldInjection
   extends PluginCustomFieldsCustomField
   implements PluginDatainjectionInjectionInterface
{

   /**
    * Constructor. Set table
    */

   function __construct()
   {

      // Needed for getSearchOptions

      $this->table = getTableForItemType('PluginCustomFieldsCustomField');

   }

   /**
    * @see PluginDatainjectionInjectionInterface::isPrimaryType()
    */

   function isPrimaryType()
   {
      return true;
   }

   /**
    * @see PluginDatainjectionInjectionInterface::connectedTo()
    */
   
   function connectedTo()
   {
      return array();
   }

   /**
    * @see PluginDatainjectionInjectionInterface::getOptions()
    */

   function getOptions($primary_type = '')
   {
      
      $tab = Search::getOptions(get_parent_class($this));

      $notimportable            = array(
         5,
         9,
         29,
         30,
         31,
         50,
         53,
         56,
         57,
         58,
         59,
         60,
         80,
         91,
         92,
         122,
         130,
         131,
         132,
         133,
         134,
         135,
         136,
         137,
         138,
         139,
         140
      );

      $options['ignore_fields'] = $notimportable;

      $options['displaytype']   = array(

         "dropdown" => array(
            2,
            32,
            3,
            8,
            49,
            10
         ),

         "user" => array(
            6,
            24
         ),

         "multiline_text" => array(
            4
         ),

         "date" => array(
            9
         ),

         "bool" => array(
            11,
            7
         )
      );
      
      $tab = PluginDatainjectionCommonInjectionLib::addToSearchOptions(
         $tab,
         $options,
         $this
      );
      
      return $tab;

   }
   
   
   /**
    * Standard method to delete an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    *
    * @param fields fields to add into glpi
    * @param options options used during creation
    **/
   function deleteObject($values = array(), $options = array())
   {
      
      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->deleteObject();
      return $lib->getInjectionResults();
   }
   
   /**
    * Standard method to add an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    *
    * @param values fields to add into glpi
    * @param options options used during creation
    *
    * @return an array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
    **/
   function addOrUpdateObject($values = array(), $options = array())
   {
      global $LANG;
      
      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }
   
}