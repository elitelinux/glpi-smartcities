<?php
/**
 * @version $Id: methodinventaire.class.php 399 2015-01-09 09:26:22Z tsmr $
 -------------------------------------------------------------------------
 LICENSE

 This file is part of Webservices plugin for GLPI.

 Webservices is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Webservices is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Webservices. If not, see <http://www.gnu.org/licenses/>.

 @package   Webservices
 @author    Nelly Mahu-Lasson
 @copyright Copyright (c) 2009-2014 Webservices plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/webservices
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginWebservicesMethodInventaire extends PluginWebservicesMethodCommon {

   //----------------------------------------------------//
   //----------------- Read methods --------------------//
   //--------------------------------------------------//

   /**
    * Get a list of objects
    * for an authenticated user
    *
    * @param $params    array of options
    * @param $protocol        the commonication protocol used
   **/
   static function methodListObjects($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array('start'         => 'integer,optional',
                      'limit'         => 'integer,optional',
                      'name'          => 'string,optional',
                      'serial'        => 'string,optional',
                      'otherserial'   => 'string,optional',
                      'locations_id'  => 'integer,optional',
                      'location_name' => 'string,optional',
                      'room'          => 'string (Location only)',
                      'building'      => 'string (Location only)',
                      'itemtype'      => 'string,mandatory',
                      'show_label'    => 'bool, optional (0 default)',
                      'help'          => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      //Must be superadmin to use this method
      if(!Session::haveRight('config', UPDATE)){
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      $resp  = array();
      $start = 0;
      $limit = $_SESSION['glpilist_limit'];
      if (isset($params['limit']) && is_numeric($params['limit'])) {
         $limit = $params['limit'];
      }
      if (isset($params['start']) && is_numeric($params['start'])) {
         $start = $params['start'];
      }
      foreach (array('show_label','show_name') as $key) {
          $params[$key] = (isset($params[$key])?$params[$key]:false);
      }

      if (!isset($params['itemtype'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'itemtype');
      }
      if (!class_exists($params['itemtype'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'itemtype');
      }

      //Fields to return to the client when search search is performed
      $params['return_fields'][$params['itemtype']] = array('id', 'interface', 'is_default',
                                                            'locations_id',  'name', 'otherserial',
                                                            'serial');

      $output = array();
      $item   = new $params['itemtype'];
      if (!$item->canView()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED, '');
      }
      $table = $item->getTable();

      //Restrict request
      if ($item->isEntityAssign()) {
         $where = getEntitiesRestrictRequest('WHERE', $table);
      } else {
         $where = "WHERE 1 ";
      }
      if ($item->maybeDeleted()) {
         $where .= " AND `$table`.`is_deleted` = '0'";
      }
      if ($item->maybeTemplate()) {
         $where .= " AND `$table`.`is_template` = '0'";
      }
      $left_join = "";
      if ($item->getField('entities_id') != NOT_AVAILABLE) {
         $left_join = " LEFT JOIN `glpi_entities`
                           ON (`$table`.`entities_id` = `glpi_entities`.`id`) ";

         $already_joined = array();
         $left_join.= self::listInventoryObjectsRequestLeftJoins($params, $item, $table, $already_joined).
                      getEntitiesRestrictRequest(" AND ", $table);

         $where = self::listInventoryObjectsRequestParameters($params, $item, $table, $where);
      }
      $query = "SELECT `$table`.* FROM `$table`
                $left_join
                $where
                ORDER BY `id`
                LIMIT $start,$limit";

      foreach ($DB->request($query) as $data) {
         $tmp      = array();
         $toformat = array('table'         => $table, 'data'  => $data,
                           'searchOptions' => Search::getOptions($params['itemtype']),
                           'options'       => $params);
         parent::formatDataForOutput($toformat, $tmp);
         $output[] = $tmp;
      }
      return $output;
   }


   /**
    * Get an object for an authenticated user
    *
    * @param $params    array of options
    * @param $protocol        the commonication protocol used
   **/
   static function methodGetObject($params, $protocol) {
      global $CFG_GLPI,$WEBSERVICE_LINKED_OBJECTS;

      if (isset($params['help'])) {
         $options =  array('id'         => 'integer',
                           'help'       => 'bool,optional',
                           'show_label' => 'bool, optional',
                           'show_name'  => 'bool, optional');
          foreach ($WEBSERVICE_LINKED_OBJECTS as $option => $value) {
            $options[$option] = $value['help'];
          }
          return $options;
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      $p['itemtype']      = '';
      $p['id']            = false;
      $p['return_fields'] = array();
      $p['show_label']    = $p['show_name'] = false;
      foreach ($params as $key => $value) {
         $p[$key]         = $value;
      }

      //Check mandatory parameters
      foreach (array('itemtype','id') as $mandatory_field) {
         if (!isset($p[$mandatory_field])) {
            return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '',
                               $mandatory_field);
         }
      }

      //Check mandatory parameters validity
      if (!is_numeric($p['id'])) {
          return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'id=' . $p['id']);
      }
      if (!class_exists($p['itemtype'])) {
          return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                             'itemtype=' . $p['itemtype']);
      }

      $item = new $p['itemtype'];
      if (!$item->canView()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED, '', $params['itemtype']);
      }
      if (!$item->getFromDB($p['id'])
          || !$item->can($p['id'], READ)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
      }

      $output   = array();
      $toformat = array('data'          => $item->fields,
                        'options'       => $p,
                        'searchOptions' => Search::getOptions($params['itemtype']),
                        'itemtype'      => $p['itemtype']);
      parent::formatDataForOutput($toformat, $output);
      self::processLinkedItems($output, $params, $p , $protocol, $toformat);
      return $output;
   }


   /**
    * Process itemtypes linked to the primary type
    * @param $output the array to be populated
    * @param $params
    * @param $p
    * @param $protocol
    * @param $toformat
   **/
   static function processLinkedItems(&$output, $params, $p , $protocol, $toformat) {
      global $WEBSERVICE_LINKED_OBJECTS;

      //-------------------------------//
      //--- Process linked objects ---//
      //-----------------------------//
      foreach ($WEBSERVICE_LINKED_OBJECTS as $key => $option) {
         //If option is allowed and itemtype is allowed for this option
         if (isset($p[$key])
             && ($p[$key] == 1)
             && class_exists($p['itemtype'])
             && in_array($p['itemtype'], $option['allowed_types'])) {

            $toformat['options']['linked_itemtype'] = $option['itemtype'];
            $toformat['options']['source_itemtype'] = $p['itemtype'];
            $function_name                          = "get".$option['itemtype']."s";

            if (method_exists($option['class'], $function_name)) {
                $result = call_user_func(array($option['class'], $function_name), $protocol,
                                         $toformat, $p);
                if (!empty($result)) {
                   $output[$option['itemtype']] = $result;
                }
            }
         }
      }
   }


   static function getItems($protocol, $params=array(), $original_params=array()) {

      $flip = (isset($params['options']['flip_itemtypes'])
                  ?$params['options']['flip_itemtypes']:false);

      if (!$flip) {
         //Source itemtype (used to find the right _items table)
         $source_itemtype  = $params['options']['source_itemtype'];
         //Linked itemtype : items to look for in the _items table
         $linked_itemtype  = $params['options']['linked_itemtype'];
         $item             = new $linked_itemtype();
         $source_item      = new $source_itemtype();
         $fk               = getForeignKeyFieldForTable($source_item->getTable());
      } else {
         //Source itemtype (used to find the right _items table)
         $linked_itemtype  = $params['options']['source_itemtype'];
         //Linked itemtype : items to look for in the _items table
         $source_itemtype  = $params['options']['linked_itemtype'];
         $item             = new $source_itemtype();
         $linked_item      = new $linked_itemtype();
         $fk               = "items_id";
      }

      $table = 'glpi_'.(strtolower($source_itemtype)).'s_items';

      $sql = "`itemtype` = '".$linked_itemtype."'
               AND `$fk` = '".Toolbox::addslashes_deep($params['data']['id'])."'";

      $computer_item = array('Monitor','Printer','Phone','Peripheral');

      $itemtype_class = getItemtypeForTable($table);

      $item_class = new $itemtype_class();

      if ($item_class instanceof CommonDBRelation
          && !in_array($linked_itemtype, $computer_item)) {
         $fk_items = $item_class->items_id_1;
      } else if ($item_class instanceof CommonDBChild
                 && !in_array($linked_itemtype,$computer_item)) {
         $fk_items = $item_class->items_id;
      } else {
         $fk_items = "items_id";
      }

      $output = array();
      foreach (getAllDatasFromTable($table,$sql) as $data) {

            $item->getFromDB($data[$fk_items]);
            $resp     = array();
            $toformat = array('data'          => $item->fields,
                              'searchOptions' => Search::getOptions(get_class($item)),
                              'options'       => $params['options']);
            parent::formatDataForOutput($toformat, $resp);
            $output[$item->fields['id']] = $resp;
      }
      return $output;
   }


   /**
    * Get network ports for an object for an authenticated user
    *
    * @param $protocol                    the commonication protocol used
    * @param $params             array    parameters
    * @param $original_params    array
    */
   static function getNetworkports($protocol, $params=array(), $original_params=array()) {
      global $DB;

      if (!Session::haveRight("networking", READ)) {
         return array();
      }
      $item = new $params['options']['itemtype']();
      $resp = array();

      if ($item->can($params['data']['id'], READ)) {
         //Get all ports for the object
         $ports = getAllDatasFromTable('glpi_networkports',
                                       "`itemtype` = '".Toolbox::addslashes_deep($params['options']['itemtype']).
                                          "' AND `items_id` = '".Toolbox::addslashes_deep($params['data']['id'])."'");
         $output  = array();
         $oneport = new NetworkPort();
         foreach ($ports as $port) {
            $resp     = array();
            $toformat = array('data'          => $port,
                              'searchOptions' => Search::getOptions('NetworkPort'),
                              'options'       => $params['options']);
            parent::formatDataForOutput($toformat, $resp);

            //Get VLANS
            $port_vlan  = new NetworkPort_Vlan();
            $onevlan    = new Vlan();
            foreach ($port_vlan->getVlansForNetworkPort($port['id']) as $vlans_id ) {
               $onevlan->getFromDB($vlans_id);
               $vlan        = array();
               $params_vlan = array('data'           => $onevlan->fields,
                                    'searchOptions' => Search::getOptions('Vlan'),
                                    'options'       => $params['options']);
               parent::formatDataForOutput($params_vlan, $vlan);
               $resp['Vlan'][$vlans_id] = $vlan;
            }

            $output[$port['id']] = $resp;
         }
      }

      return $output;
   }


   static function getSoftwares($protocol, $params=array(), $original_params=array()) {
      global $DB, $WEBSERVICE_LINKED_OBJECTS;

      if (!Session::haveRight("software", READ)) {
         return array();
      }
      $item = new $params['options']['itemtype']();
      $resp = array();
      $software = new Software();

      //Store softwares, versions and licenses
      $softwares = array();

      if ($item->can($params['data']['id'], READ) && $software->can(-1, READ)) {

         foreach (array('SoftwareVersion', 'SoftwareLicense') as $itemtype) {
            $link_table = "glpi_computers_".Toolbox::addslashes_deep(strtolower($itemtype))."s";
            $table      = getTableForItemType($itemtype);
            $query      = "SELECT DISTINCT `gsv`.*
                           FROM `".Toolbox::addslashes_deep($link_table)."` AS gcsv,
                                `".Toolbox::addslashes_deep($table)."` AS gsv
                           WHERE `gcsv`.`computers_id`
                                       = '".Toolbox::addslashes_deep($params['data']['id'])."'
                                 AND `gcsv`.`".getForeignKeyFieldForTable($table)."` = `gsv`.`id`
                           GROUP BY `gsv`.`softwares_id`
                           ORDER BY `gsv`.`softwares_id` ASC";

            foreach ($DB->request($query) as $version_or_license) {

               //Software is not yet in the list
               if (!isset($softwares['Software'][$version_or_license['softwares_id']])) {
                  $software->getFromDB($version_or_license['softwares_id']);
                  $toformat = array('data'          => $software->fields,
                                    'searchOptions' => Search::getOptions('Software'),
                                    'options'       => $params['options']);
                  $tmp = array();
                  parent::formatDataForOutput($toformat, $tmp);
                  $softwares['Software'][$version_or_license['softwares_id']] = $tmp;

               }

               $toformat2 = array('data'          => $version_or_license,
                                  'searchOptions' => Search::getOptions($itemtype),
                                  'options'       => $params['options']);
               $tmp = array();
               parent::formatDataForOutput($toformat2, $tmp);
               $softwares['Software'][$version_or_license['softwares_id']][$itemtype][$version_or_license['id']]
                  = $tmp;

            }
         }
      }
      return $softwares;
   }


   static function getSoftwareVersions($protocol, $params=array(), $original_params=array()) {
      return self::getSoftwareVersionsOrLicenses($protocol, $params, new SoftwareVersion());
   }


   static function getSoftwareLicenses($protocol, $params=array(), $original_params=array()) {
      return self::getSoftwareVersionsOrLicenses($protocol, $params, new SoftwareLicense());
   }


   static function getSoftwareVersionsOrLicenses($protocol, $params=array(), CommonDBTM $item) {
      global $DB;

      $software = new Software();
      $resp     = array();

      if ($software->can($params['data']['id'], READ)) {
         $query = "SELECT `gsv`.*
                   FROM `".Toolbox::addslashes_deep($item->getTable())."` AS gsv,
                        `glpi_softwares` AS gs
                   WHERE `gsv`.`softwares_id` = `gs`.`id`
                         AND `gsv`.`softwares_id`
                                 = '".Toolbox::addslashes_deep($params['data']['id'])."'
                   GROUP BY `gsv`.`softwares_id`
                   ORDER BY `gsv`.`softwares_id` ASC";

        $toformat = array('searchOptions' => Search::getOptions(get_class($item)),
                          'options'       => $params['options']);

         foreach ($DB->request($query) as $version_or_license) {
           $toformat['data'] = $version_or_license;
           $result           = array();

           parent::formatDataForOutput($toformat, $result);
           $resp[$version_or_license['id']] = $result;
         }
      }

      return $resp;
   }


   static function getMonitors($protocol, $params=array(), $original_params=array()) {
      return self::getItems($protocol, $params);
   }


   static function getPrinters($protocol, $params=array(), $original_params=array()) {
      return self::getItems($protocol, $params);
   }


   static function getPhones($protocol, $params=array(), $original_params=array()) {
      return self::getItems($protocol, $params);
   }


   static function getPeripherals($protocol, $params=array(), $original_params=array()) {
      return self::getItems($protocol, $params);
   }


   static function getDocuments($protocol, $params=array(), $original_params=array()) {

      $params['options']['flip_itemtypes'] = true;
      return self::getItems($protocol, $params);
   }


   static function getReservations($protocol, $params=array(), $original_params=array()) {

      //Source itemtype (used to find the right _items table)
      $linked_itemtype = $params['options']['source_itemtype'];
      //Linked itemtype : items to look for in the _items table
      $source_itemtype = $params['options']['linked_itemtype'];
      $item            = new $source_itemtype();
      $linked_item     = new $linked_itemtype();
      $fk = "items_id";

      foreach (getAllDatasFromTable('glpi_reservationitems',
                                    "`itemtype` = '".$linked_itemtype."'
                                     AND `$fk` = '".$params['data']['id']."'") as $reservationitems) {
         $reservationitems_id = $reservationitems['id'];
      }

      $output = array();
      foreach (getAllDatasFromTable('glpi_reservations',
                                    "`reservationitems_id`='".$reservationitems_id."' ") as $data) {
         $item->getFromDB($data['id']);
         $resp     = array();
         $toformat = array('data'          => $item->fields,
                           'searchOptions' => Search::getOptions(get_class($item)),
                           'options'       => $params['options']);
         PluginWebservicesMethodCommon::formatDataForOutput($toformat, $resp);
         $output[$item->fields['id']] = $resp;
      }
      return $output;
   }


   /**
    * Check standard parameters for get requests
    *
    * @param $params    the input parameters
    * @param $protocol  the commonication protocol used
    *
    * @return 1 if checks are ok, an error if checks failed
   **/
   static function checkStandardParameters($params, $protocol) {

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      if (!isset($params['id'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'id');
      }

      if (!isset($params['itemtype'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'itemtype');
      }

      if (!is_numeric($params['id'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                            $params['itemtype'].'=' . $params['id']);
      }
      return 1;
   }


   //-----------------------------------------------//
   //--------- Itemtype independant methods -------//
   //---------------------------------------------//

   /**
    * Contruct parameters restriction for listInventoryObjects sql request
    *
    * @param $params    the input parameters
    * @param $item      CommonDBTM object
    * @param $table
    * @param $where
   **/
   static function listInventoryObjectsRequestParameters($params, CommonDBTM $item, $table,
                                                         $where="WHERE 1") {

      $already_used = array();

      foreach ($params as $key => $value) {
         //Key representing the FK associated with the _name value
         $key_transformed = preg_replace("/_name/", "s_id", $key);
         $fk_table        = getTableNameForForeignKeyField($key);
         $option          = $item->getSearchOptionByField('field', $key_transformed);

         if (!empty($option)) {
            if (!in_array($key, $already_used)
                && isset($params[$key])
                && $params[$key]
                && $item->getField($option['linkfield']) != NOT_AVAILABLE) {

               if (getTableNameForForeignKeyField($key)) {
                  $where .= " AND `$table`.`$key`='" . Toolbox::addslashes_deep($params[$key]) . "'";

               } else {
                  //
                  if (($key != $key_transformed) || ($table != $option['table'])) {
                     $where .= " AND `".Toolbox::addslashes_deep($option['table'])."`.`".Toolbox::addslashes_deep($option['field']) ."`
                                    LIKE '%" . Toolbox::addslashes_deep($params[$key]) . "%'";

                  } else {
                     $where .= " AND `$table`.`$key`
                                    LIKE '%" . Toolbox::addslashes_deep($params[$key]) . "%'";
                  }
               }
               $already_used[] = $key;

            }
         }
      }

      return $where;
   }


   /**
    * Contruct parameters restriction for listInventoryObjects sql request
    *
    * @param $params          the input parameters
    * @param $item            CommonDBTM object
    * @param $table
    * @param $already_joined
   **/
   static function listInventoryObjectsRequestLeftJoins($params, CommonDBTM $item, $table,
                                                        $already_joined) {

      $join           = "";
      $already_joined = array();

      foreach ($params as $key => $value) {

         //Key representing the FK associated with the _name value
         $key_transformed = preg_replace("/_name/", "s_id", $key);
         $option          = $item->getSearchOptionByField('field', $key_transformed);

         if (!empty($option)
             && !isset($option['common'])
             && $table != $option['table']
             && !in_array($option['table'], $already_joined)) {

            $join.= " \nINNER JOIN `".Toolbox::addslashes_deep($option['table'])."`
                           ON (`".Toolbox::addslashes_deep($table)."`.`".Toolbox::addslashes_deep($option['linkfield'])."`
                                 = `".Toolbox::addslashes_deep($option['table'])."`.`id`) ";
            $already_joined[] = $option['table'];
         }

      }
      return $join;
   }


   /**
    * List inventory objects (global search)
    *
    * @param $params    the input parameters
    * @param $protocol  the commonication protocol used
    *
   **/
   static function methodListInventoryObjects($params, $protocol) {
      global $DB, $CFG_GLPI;

      //Display help for this function
      if (isset($params['help'])) {

         foreach (Search::getOptions('State') as $itemtype => $option) {

            if (!isset($option['common'])) {
               if (isset($option['linkfield']) && $option['linkfield'] != '' ) {

                  if (in_array($option['field'], array('name', 'completename'))) {
                     $fields[$option['linkfield']] = 'integer,optional';
                     $name_associated              = str_replace("s_id", "_name",
                                                                 $option['linkfield']);
                     if (!isset($option['datatype']) || $option['datatype'] == 'text') {
                        $fields[$name_associated] = 'string,optional';
                     }
                  } else {
                     $fields[$option['field']] = 'string,optional';
                  }

               } else {
                  $fields[$option['field']] = 'string,optional';
               }

            }
         }
         $fields['start'] = 'integer,optional';
         $fields['limit'] = 'integer,optional';
         return $fields;
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      //Must be superadmin to use this method
      if(!Session::haveRight('config', UPDATE)){
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      $resp = array();

      $itemtypes = array();
      //If several itemtypes given, build an array
      if (isset($params['itemtype'])) {
         if (!is_array($params['itemtype'])) {
            $itemtypes = array($params['itemtype']);

         } else {
            $itemtypes = $params['itemtype'];
         }
      } else {
         $itemtypes = plugin_webservices_getTicketItemtypes();
      }

      //Check read right on each itemtype
      foreach ($itemtypes as $itemtype) {
         $item = new $itemtype();
         if (!$item->canView()) {
            $key = array_search($itemtype, $itemtypes);
            unset($itemtypes[$key]);
            $resp[] = self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED, '', $itemtype);
         }
      }

      //If nothing in the array, no need to go further !
      if (empty($itemtypes)) {
         return $resp;
      }

      $resp  = array();
      $start = 0;
      $limit = $_SESSION['glpilist_limit'];
      if (isset($params['limit']) && is_numeric($params['limit'])) {
         $limit = $params['limit'];
      }
      if (isset($params['start']) && is_numeric($params['start'])) {
         $start = $params['start'];
      }

      $first = true;
      $query = "";

      foreach ($itemtypes as $itemtype) {
         if (in_array($itemtype, $itemtypes)) {
            $item  = new $itemtype();
            $item->getEmpty();
            $table = getTableForItemType($itemtype);
            $already_joined = array();
            if (!$first) {
               $query.= " UNION ";
            }
            $query.= "\nSELECT `".Toolbox::addslashes_deep($table)."`.`name`,
                               `".Toolbox::addslashes_deep($table)."`.`id`,
                               `glpi_entities`.`completename` AS entities_name,
                               `glpi_entities`.`id` AS entities_id,
                               '".Toolbox::addslashes_deep($itemtype)."' AS itemtype";
            if(FieldExists($table, 'serial')) {
               $query.= ", `".Toolbox::addslashes_deep($table)."`.`serial`";
            } else {
               $query.= ", '' as `serial`";
            }
            if(FieldExists($table, 'otherserial')) {
               $query.= ", `".Toolbox::addslashes_deep($table)."`.`otherserial`";
            } else {
               $query.= ", '' as `otherserial`";
            }
            $query .= " FROM `".Toolbox::addslashes_deep($table)."`";
            if (!in_array($table, $already_joined)) {
               $query.= " LEFT JOIN `glpi_entities` ON (`".Toolbox::addslashes_deep($table).
                           "`.`entities_id` = `glpi_entities`.`id`)";
               $already_joined[] = 'glpi_entities';
            }
            $query.= self::listInventoryObjectsRequestLeftJoins($params, $item, $table,
                                                                $already_joined).
                      getEntitiesRestrictRequest(" AND ", $table);
            if ($item->maybeTemplate()) {
               $query .= " AND `".Toolbox::addslashes_deep($table)."`.`is_template`='0' ";

            }
            if ($item->maybeDeleted()) {
               $query .= " AND `".Toolbox::addslashes_deep($table)."`.`is_deleted`='0' ";

            }
            $query .= self::listInventoryObjectsRequestParameters($params, $item, $table);
            $first  = false;
         }
         $first = false;


      }
      $query .= " ORDER BY `name`
                  LIMIT $start, $limit";

      foreach ($DB->request($query) as $data) {
         if (!($item = getItemForItemtype($data['itemtype']))) {
            continue;
         }
         $data['itemtype_name'] = Html::clean($item->getTypeName());
         $resp[]                = $data;
      }
      return $resp;
   }


   //----------------------------------------------------//
   //----------------- Write methods --------------------//
   //--------------------------------------------------//

   /**
    * Create inventory objects
    *
    * @param $params    the input parameters
    * @param $protocol  the commonication protocol used
    *
   **/
   static function methodCreateObjects($params, $protocol) {
      global $CFG_GLPI;

      if (isset($params['help'])) {
         if (!is_array($params['help'])) {
            return array('fields'   => 'array, mandatory',
                         'help'     => 'bool, optional');
         } else {
            $resp = array();
            foreach($params['help'] as $itemtype) {
               $item = new $itemtype();
               //If user has right access on this itemtype
               if ($item->canCreate()) {
                  $item->getEmpty();
                  $blacklisted_field = array($item->getIndexName());
                  foreach($item->fields as $field => $default_v) {
                     if(!in_array($field,$blacklisted_field)) {
                        $resp[$itemtype][] = $field;
                     }
                  }
               }
            }
            return $resp;
         }
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      //Must be superadmin to use this method
      if(!Session::haveRight('config', UPDATE)){
         $errors[$itemtype][] = self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      if (!isset($params['fields'])
          || empty($params['fields'])
          || !is_array($params['fields'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'fields');
      }

      $datas   = array();
      $resp    = array();
      $errors  = array();

      foreach ($params['fields'] as $itemtype => $items) {
         foreach ($items as $fields) {
            $item = new $itemtype();

            foreach($fields as $field => $value) {
               if ($item->isField($field) || in_array($field, array('withtemplate'))) {
                  $datas[$field] = $value;
               }
            }

            if ($item->isField('entities_id')
                && !isset($datas['entities_id'])
                && isset($_SESSION["glpiactive_entity"])) {
               $datas['entities_id'] = $_SESSION["glpiactive_entity"];
            }

            if (!$item->can(-1, CREATE ,$datas)) {
               $errors[$itemtype][] = self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED,
                                                  '', self::getDisplayError());
            } else {
               if ($newID = $item->add($datas)) {
                  $resp[$itemtype][] = self::methodGetObject(array('itemtype' => $itemtype,
                                                                   'id'       => $newID),
                                                             $protocol);
               } else {
                  $errors[$itemtype][] = self::Error($protocol, WEBSERVICES_ERROR_FAILED,
                                                     '', self::getDisplayError());
               }
            }
         }
      }

      if (count($errors)) {
         $resp = array($resp,$errors);
      }

      return $resp;
   }


   /**
    * Delete inventory objects
    *
    * @param $params    the input parameters
    * @param $protocol  the commonication protocol used
    *
   **/
   static function methodDeleteObjects($params, $protocol) {
      global $CFG_GLPI;

      if (isset($params['help'])) {
         return array('fields'   => 'array, mandatory',
                      'help'     => 'bool, optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      //Must be superadmin to use this method
      if(!Session::haveRight('config', UPDATE)){
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      if (!isset ($params['fields'])
          || empty($params['fields'])
          || !is_array($params['fields'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'fields');
      }

      $resp    = array();
      $errors  = array();
      foreach($params['fields'] as $itemtype => $items) {
         foreach($items as $num => $key) {
            foreach($key as $name => $value) {
               $tab[$name] = $value;
               $item       = new $itemtype();
               $right = 'DELETE';
               if (!$item->maybeDeleted) {
                  $right = 'PURGE';
               }
               if(!$item->can($tab['id'], $right)){
                  $errors[$itemtype][$tab['id']] = self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED,
                                                              '', self::getDisplayError());
               } else {
                  $resp[$itemtype][$tab['id']] = $item->delete(array('id' => $tab['id']), $tab['force']);
               }
            }
         }
      }

      if (count($errors)) {
         $resp = array($resp,$errors);
      }

      return $resp;
   }


   /**
    * Update inventory objects
    *
    * @param $params    the input parameters
    * @param $protocol  the commonication protocol used
    *
   **/
   static function methodUpdateObjects($params, $protocol) {
      global $CFG_GLPI;

      if (isset($params['help'])) {
         return array('fields'   => 'array, mandatory',
                      'help'     => 'bool, optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      if (!isset($params['fields'])
          || empty($params['fields'])
          || !is_array($params['fields'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'fields');
      }

      if (!isset($_SESSION["glpi_currenttime"])) {
         $_SESSION["glpi_currenttime"] = date("Y-m-d H:i:s");
      }

      $resp    = array();
      $datas   = array();
      $errors  = array();

      foreach ($params['fields'] as $itemtype => $items) {
         foreach ($items as $fields) {
            $item    = new $itemtype();
            $id_item = $item->getIndexName();

            if (!isset($fields[$id_item])) {
               $errors[$itemtype][] = self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '',
                                                  'id');
            } else {
               if (!$item->getFromDB($fields[$id_item])) {
                  $errors[$itemtype][] = self::Error($protocol, WEBSERVICES_ERROR_FAILED,
                                                    '', self::getDisplayError());
               }
            }
            $datas = $item->fields;
            foreach($fields as $field => $value) {
               $datas[$field] = $value;
            }


            if (!$item->can($fields[$id_item], UPDATE, $datas)) {
               $errors[$itemtype][] = self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED,
                                                  '', self::getDisplayError());
            } else {
               if ($item->update($datas)) {
                  $resp[$itemtype][] = self::methodGetObject(array('itemtype' => $itemtype,
                                                                   'id'       => $fields[$id_item]),
                                                             $protocol);
               } else {
                  $errors[$itemtype][] = self::Error($protocol, WEBSERVICES_ERROR_FAILED,
                                                     '', self::getDisplayError());
               }
            }
         }
      }

      if (count($errors)) {
         $resp = array($resp,$errors);
      }

      return $resp;
   }


   /**
    * Link inventory object to another one
    *
    * @param $params    the input parameters
    * @param $protocol  the commonication protocol used
    *
   **/
   static function methodLinkObjects($params, $protocol) {
      global $CFG_GLPI;

      if (isset($params['help'])) {
         return array('fields' => 'array, mandatory',
                      'help'   => 'bool, optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      //Must be superadmin to use this method
      if(!Session::haveRight('config', UPDATE)){
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      if ((!isset ($params['fields']) || empty($params['fields'])
            || !is_array($params['fields']))) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'fields');
      }

      if (!isset($_SESSION["glpi_currenttime"])) {
         $_SESSION["glpi_currenttime"] = date("Y-m-d H:i:s");
      }

      $resp    = array();
      $errors  = array();

      foreach ($params['fields'] as $links) {
         if (!in_array($links['from_item']['itemtype'], array('Computer'))
             && !preg_match("/Device/", $links['from_item']['itemtype'])) {
            $errors[] = self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED, '',
                                    self::getDisplayError());
         }

         switch ($links['from_item']['itemtype']) {
            case 'Computer':
               // Direct connections
               if (in_array($links['to_item']['itemtype'], array('Monitor', 'Peripheral', 'Phone',
                                                                 'Printer'))) {
                  $comp_item              = new Computer_Item();
                  $data                   = array();
                  $data['items_id']       = $links['to_item']['id'];
                  $data['computers_id']   = $links['from_item']['id'];
                  $data['itemtype']       = $links['to_item']['itemtype'];

                  if (!$comp_item->can(-1, UPDATE, $data)) {
                     $errors[] = self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED, '',
                                             self::getDisplayError());
                  } else {
                     if ($comp_item->add($data)) {
                        $resp['Computer'][$data['computers_id']]
                              = self::methodGetObject(array('itemtype'        => 'Computer',
                                                            'id'              => $data['computers_id'],
                                                            'with_printer'    => 1,
                                                            'with_monitor'    => 1,
                                                            'with_phone'      => 1,
                                                            'with_peripheral' => 1),
                                                      $protocol);
                     } else {
                        $errors[] = self::Error($protocol, WEBSERVICES_ERROR_FAILED,
                                                '', self::getDisplayError());
                     }
                  }
               }

               // Device connection
               if (preg_match("/Device/",$links['to_item']['itemtype'])) {
                  $comp_device            = new Computer_Device();
                  $links_field            = getPlural(strtolower($links['to_item']['itemtype']))."_id";
                  $data                   = array();
                  $data['computers_id']   = $links['from_item']['id'];
                  $data[$links_field]     = $links['to_item']['id'];
                  $data['itemtype']       = $links['to_item']['itemtype'];

                  if (!isset($links['to_item']['quantity'])
                      || !is_numeric($links['to_item']['quantity'])) {
                     $quantity = 1;
                  } else {
                      $quantity = $links['to_item']['quantity'];
                   }

                   if (isset($links['to_item']['specificity'])) {
                      if (!is_numeric($links['to_item']['specificity'])) {
                        $errors[] = self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER,
                                                '', 'specificity');
                     } else {
                        $data['specificity'] = $links['to_item']['specificity'];
                     }
                  }

                  $linked = false;

                  for ($i=0 ; $i<$quantity ; $i++) {
                     if (!$comp_device->can(-1, UPDATE, $data)) {
                        $errors[] = self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED,
                                                '',self::getDisplayError());
                     } else {
                        if ($comp_device->add($data)) {
                           $linked = true;
                        }
                     }
                  }

                  if ($linked) {
                     $resp['Computer'][$data['computers_id']]
                           = self::methodGetObject(array('itemtype'  => 'Computer',
                                                         'id'        =>$data['computers_id']),
                                                   $protocol);
                  } else {
                     $errors[] = self::Error($protocol, WEBSERVICES_ERROR_FAILED,
                                             '',self::getDisplayError());
                  }
               }
               //other link object
            break;

         //itemtype
         }
      }

      if (count($errors)) {
         $resp = array($resp,$errors);
      }

      return $resp;

   }


   /**
    * List all users of the current entity, with search criterias
    * for an authenticated user
    *
    * @param $params    array of options (user, group, location, login, name)
    * @param $protocol        the commonication protocol used
    *
    * @return array of hashtable
   **/
   static function methodListUsers($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array('count'    => 'bool,optional',
                      'start'    => 'integer,optional',
                      'limit'    => 'integer,optional',
                      'order'    => 'string,optional',
                      'entity'   => 'integer,optional',
                      'parent'   => 'bool,optional',
                      'user'     => 'integer,optional',
                      'group'    => 'integer,optional',
                      'location' => 'integer,optional',
                      'login'    => 'string,optional',
                      'name'     => 'string,optional',
                      'help'     => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      $orders = array('id'     => '`glpi_users`.`id`',
                      'name'   => ($_SESSION['glpinames_format'] == User::FIRSTNAME_BEFORE
                                    ? '`glpi_users`.`firstname`,`glpi_users`.`realname`'
                                    : '`glpi_users`.`realname`,`glpi_users`.`firstname`'),
                      'login'  => '`glpi_users`.`name`');

      $parent = 1;
      if (isset($params['parent'])) {
         $parent = ($params['parent'] ? 1 : 0);
      }

      if (isset($params['entity'])) {
         if (!Session::haveAccessToEntity($params['entity'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED, '', 'entity');
         }
         $ent = $params['entity'];
      } else {
         $ent = '';
      }

      $query = "LEFT JOIN `glpi_profiles_users`
                     ON (`glpi_users`.`id` = `glpi_profiles_users`.`users_id`)
                LEFT JOIN `glpi_useremails`
                     ON (`glpi_users`.`id` = `glpi_useremails`.`users_id`
                         AND `glpi_useremails`.`is_default`)
                WHERE `glpi_users`.`is_deleted` = '0'
                      AND `glpi_users`.`is_active` = '1' ".
                      getEntitiesRestrictRequest('AND', "glpi_profiles_users", '', $ent, $parent);

      if (isset($params['user']) && is_numeric($params['user'])) {
         $query .= " AND `glpi_users`.`id` = '" . $params['user'] . "'";
      }
      if (isset($params['group']) && is_numeric($params['group'])) {
         $query .= " AND `glpi_users`.`id` IN (SELECT `users_id`
                                               FROM `glpi_groups_users`
                                               WHERE `groups_id` = '" . $params['group'] . "')";
      }
      if (isset($params['location']) && is_numeric($params['location'])) {
         $query .= " AND `glpi_users`.`locations_id` = '" . $params['location'] . "'";
      }
      if (isset($params['login'])) {
         $query .= " AND `glpi_users`.`name` LIKE '" . addslashes($params['login']) . "'";
      }
      if (isset($params['name'])) {
         if ($_SESSION['glpinames_format'] == User::FIRSTNAME_BEFORE) {
            $query .= " AND CONCAT(`glpi_users`.`firstname`,' ',`glpi_users`.`realname`)";
         } else {
            $query .= " AND CONCAT(`glpi_users`.`realname`,' ',`glpi_users`.`firstname`)";
         }
         $query .= " LIKE '" . addslashes($params['name']) . "'";
      }

      $resp = array ();
      if (isset($params['count'])) {
         $query = "SELECT COUNT(DISTINCT `glpi_users`.`id`) AS count
                   FROM `glpi_users`
                   $query";

         $resp = $DB->request($query)->next();
      } else {
         $start = 0;
         $limit = $_SESSION['glpilist_limit'];
         if (isset($params['limit']) && is_numeric($params['limit'])) {
            $limit = $params['limit'];
         }
         if (isset($params['start']) && is_numeric($params['start'])) {
            $start = $params['start'];
         }
         if (isset($params['order']) && isset($orders[$params['order']])) {
            $order = $orders[$params['order']];
         } else {
            $order = $orders['id'];
         }

         $query = "SELECT DISTINCT(`glpi_users`.`id`) AS id,
                          `glpi_users`.`name`, `firstname`,
                          `realname`, `email`, `phone`, `glpi_users`.`locations_id`,
                          `glpi_locations`.`completename` AS locations_name
                   FROM `glpi_users`
                   LEFT JOIN `glpi_locations`
                        ON (`glpi_users`.`locations_id` = `glpi_locations`.`id`)
                   $query
                   ORDER BY $order
                   LIMIT $start,$limit";

         foreach ($DB->request($query) as $data) {
            $data['displayname'] = formatUserName(0, $data['name'], $data['realname'],
                                                  $data['firstname']);
            $resp[] = $data;
         }
      }

      return $resp;
   }


   /**
    * Get a Document the authenticated user can view or anonymous (for public FAQ)
    *
    * @param $params    array of options (document, ticket)
    * @param $protocol        the commonication protocol used
    *
    * @return a hashtable
   **/
   static function methodGetDocument($params, $protocol) {

      if (isset($params['help'])) {
         return array('document' => 'integer,mandatory',
                      'ticket'   => 'interger,optional',
                      'id2name'  => 'bool,optional',
                      'help'     => 'bool,optional');
      }

      // Allowed for anonymous user for public FAQ (right check in canViewFile)

      $doc = new Document();

      // Option parameter ticket
      if (isset($params['ticket']) && !is_numeric($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                            'ticket=' . $params['ticket']);
      }

      $options=array();
      if (isset($params['ticket'])) {
         $options['tickets_id'] = $params['ticket'];
      }

      // Mandatory parameter document
      if (!isset($params['document'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'document');
      }

      if (!is_numeric($params['document'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                            'document=' . $params['document']);
      }

      if (!$doc->getFromDB($params['document'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
      }

      if (!$doc->canViewFile($options)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      $resp           = $doc->fields;
      $resp['base64'] = base64_encode(file_get_contents(GLPI_DOC_DIR."/".$doc->fields['filepath']));

      if (isset($params['id2name'])) {
         $resp['users_name']
               = Html::clean(getUserName($doc->fields['users_id']));
         $resp['documentcategories_name']
               = Html::clean(Dropdown::getDropdownName('glpi_documentcategories',
                                                       $doc->fields['documentcategories_id']));
      }
      return $resp;
   }


   /**
    * This method return groups list allowed
    * for an authenticated user
    *
    * @param $params array of options
    * @param $protocol the commonication protocol used
    *
    * @return an response ready to be encode (ID + completename)
   **/
   static function methodListGroups($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array ('count'      => 'bool,optional',
                       'start'      => 'integer,optional',
                       'limit'      => 'integer,optional',
                       'mine'       => 'bool,optional',
                       'filter'     => 'string, optional',
                       'parent'     => 'integer,optional',
                       'under'      => 'integer,optional',
                       'withparent' => 'bool,optional',
                       'name'       => 'string,optional',
                       'help'       => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      $withparent = (isset($params['withparent']) && $params['withparent']);
      $restrict   = getEntitiesRestrictRequest('', 'glpi_groups', '', '', $withparent);
      if (isset($params['mine'])) {
         if (count($_SESSION['glpigroups'])) {
            $restrict .= "AND `id` IN ('".implode("','", $_SESSION['glpigroups'])."')";
         } else {
            $restrict .= "AND 0";
         }
      }
      if (isset($params['parent'])) {
         if (!is_numeric($params['parent'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'parent');
         }
         $restrict .= " AND `groups_id` = '".$params['parent']."'";
      }
      if (isset($params['under'])) {
         if (!is_numeric($params['under'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'under');
         }
         $restrict .= " AND `id` IN ('".implode("','", getSonsOf('glpi_groups', $params['under']))."')";
      }
      $resp = array ();
      if (isset($params['count'])) {
         $resp['count'] = countElementsInTable('glpi_groups', $restrict);
         return $resp;
      }

      if (isset($params['filter'])) {
         $filters = array('is_requester', 'is_assign', 'is_notify', 'is_itemgroup', 'is_usergroup');
         if (in_array($params['filter'], $filters)) {
            $restrict .= " AND ".$params['filter'];
         } else {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'filter');
         }
     }

      if (isset($params['name'])) {
         if ($item instanceof CommonTreeDropdown) {
            $restricty .= " AND `completename` LIKE '" . addslashes($params['name']) . "'";
         } else {
            $restrict .= " AND `name` LIKE '" . addslashes($params['name']) . "'";
         }
      }

      $start = 0;
      $limit = $_SESSION['glpilist_limit'];
      if (isset($params['limit']) && is_numeric($params['limit'])) {
         $limit = $params['limit'];
      }
      if (isset($params['start']) && is_numeric($params['start'])) {
         $start = $params['start'];
      }
      $sql = "SELECT *
              FROM `glpi_groups`
              WHERE $restrict
              ORDER BY `id`
              LIMIT $start,$limit";

      foreach ($DB->request($sql) as $data) {
         $data['member'] = (in_array($data['id'], $_SESSION['glpigroups']) ? 1 : 0);
         $resp[] = $data;
      }
      return $resp;
   }


   /**
    * Get Phone for a Computer
    * for an authenticated user
    *
    * @param $params array of options (computer)
    * @param $protocol the commonication protocol used
    *
    * @return hashtable -fields of glpi_computer
    * @deprecated since 1.1.0
   **/
   static function methodGetPhones($params, $protocol) {

      if (isset($params['help'])) {
         $params = array('itemtype' => 'string, mandatory',
                         'id'       => 'integer, mandatory',
                         'id2name'  => 'bool,optional',
                         'help'     => 'bool,optional');
         //Do not use computer parameter but id instead.
         //DEPRECATED, must be removed in the next release
         if ($params['itemtype'] == 'Computer') {
            $params['computer'] = 'integer, optional, deprecated (use id instead)';
         }
         return $params;
      }

      $check = self::checkStandardParameters($params,$protocol);
      if ($check == 1) {
      	// cette function n'existe pas
         return self::getItemPhones($protocol, $params['itemtype'], $params['id'],
                                    isset($params['id2name']));
      }
      return $check;
   }


   /**
    * Get netwok ports for an object
    * for an authenticated user
    *
    * @param $params array of options (computer)
    * @param protocol the commonication protocol used
    *
    * @return hashtable -fields of glpi_contracts
    * @deprecated since 1.1.0
   **/
   static function methodGetNetworkports($params, $protocol) {

      if (isset($params['help'])) {
         return array('id' => 'integer, mandatory',
                      'id2name'  => 'bool,optional',
                      'itemtype' => 'string, mandatory',
                      'help'     => 'bool,optional');
      }

      $check = self::checkStandardParameters($params,$protocol);
      if ($check == 1) {
         return self::getItemNetworkports($protocol, $params['itemtype'], $params['id'],
                                          isset($params['id2name']));
      }
      return $check;
   }


   static function getRelatedObjects($params, $protocol, &$resp) {

      if (isset($params['infocoms'])) {
         $infocoms = self::methodGetInfocoms($params, $protocol);
         if (!self::isError($protocol, $infocoms)) {
            $resp['infocoms'] = $infocoms;
         }
      }

      if (isset($params['contracts'])) {
         $contracts = self::methodGetContracts($params, $protocol);
         if (!self::isError($protocol, $contracts)) {
            $resp['contracts'] = $contracts;
         }
      }

      if (isset($params['networkports'])) {
         $networkports = self::methodGetNetworkports($params, $protocol);
         if (!self::isError($protocol, $networkports)) {
            $resp['networkports'] = $networkports;
         }
      }
   }


   /**
    * Return Infocom for an object
    *
    * @param $protocol           the commonication protocol used
    * @param $params    array
    *
    * @return a hasdtable, fields of glpi_infocoms
   **/
   static function methodgetItemInfocoms($params, $protocol) {

      if (isset($params['help'])) {
         $params = array('itemtype' =>'string, mandatory',
                         'id'       => 'integer, mandatory',
                         'id2name'  => 'bool,optional',
                         'help'     => 'bool,optional');
      }

      $check = self::checkStandardParameters($params,$protocol);
      if (!$check == 1) {
         exit();
      }

      if (!Session::haveRight("infocom", READ)) {
         return array();
      }
      $infocom = new InfoCom();
      $item    = new $params['itemtype']();

      $item->getTypeName();
      if (!$infocom->getFromDBforDevice($params['itemtype'], $params['id'])
          || !$item->can($params['id'], READ)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
      }

      $resp = $infocom->fields;
      $resp['warranty_expiration'] = Infocom::getWarrantyExpir($infocom->fields['buy_date'],
                                                               $infocom->fields['warranty_duration']);

      if ($id2name) {
         // TODO : more dropdown value
         $resp['suppliers_name']
               = Html::clean(Dropdown::getDropdownName('glpi_suppliers',
                                                       $infocom->fields['suppliers_id']));
         $resp['budgets_names']
               = Html::ml_clean(Dropdown::getDropdownName('glpi_budgets',
                                                          $infocom->fields['budgets_id']));
      }
      return $resp;
   }


   /**
    * Return Infocom for an object
    *
    * @param $protocol        the commonication protocol used
    * @param $params    array
    *
    * @return a hasdtable, fields of glpi_infocoms
   **/
   static function methodgetItemContracts($params, $protocol) {
      global $DB;

      if (isset($params['help'])) {
         $params = array('itemtype' =>'string, mandatory',
                         'id'       => 'integer, mandatory',
                         'id2name'  => 'bool,optional',
                         'help'     => 'bool,optional');
      }
      $check = self::checkStandardParameters($params,$protocol);
      if (!$check == 1) {
         exit();
      }

      $item = new $params['itemtype']();
      if (!$item->getFromDB($params['id'])
          || !Session::haveRight('contract', READ)
          || !$item->can($params['id'], READ)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
      }

      $contract = new Contract();

      $query = "SELECT `glpi_contracts`.*
                FROM `glpi_contracts_items`, `glpi_contracts`
                LEFT JOIN `glpi_entities` ON (`glpi_contracts`.`entities_id` = `glpi_entities`.`id`)
                WHERE `glpi_contracts`.`id` = `glpi_contracts_items`.`contracts_id`
                      AND `glpi_contracts_items`.`items_id` = '".$params['id']."'
                      AND `glpi_contracts_items`.`itemtype` = '".$params['itemtype']."'".
                      getEntitiesRestrictRequest(" AND","glpi_contracts",'','',true)."
                ORDER BY `glpi_contracts`.`name`";

      $result = $DB->query($query);
      $resp   = array();

      while ($datas = $DB->fetch_array($result)) {
         $contract->getFromDB($datas['id']);
         $resp[$datas['id']] = $contract->fields;

         if ($id2name) {
            $resp[$datas['id']]['contracttypes_name']
                  = Html::clean(Dropdown::getDropdownName('glpi_contracttypes',
                                                          $contract->fields['contracttypes_id']));
         }
      }

      return $resp;
   }


   /**
    * Get netwok ports for an object
    * for an authenticated user
    *
    * @param $protocol the commonication protocol used
    * @param $item_type : type of the item
    * @param $item_id : ID of the item
    * @param $id2name : translate id of dropdown to name
    *
   **/
   static function getItemNetworkports($protocol, $item_type, $item_id, $id2name=false) {
      global $DB;

      $item = new $item_type();
      $resp = array();

      if ($item->getFromDB($item_id)  && $item->canView()) {
         //Get all ports for the object
         $ports = getAllDatasFromTable('glpi_networkports',
                                       "`itemtype`='$item_type' AND `items_id`='$item_id'");

         foreach ($ports as $port) {
            if ($id2name) {
               if ($port['networkinterfaces_id'] > 0) {
                  $port['networkinterfaces_name']
                        = Html::clean(Dropdown::getDropdownName('glpi_networkinterfaces',
                                                                $port['networkinterfaces_id']));
               }
            }

            if ($port['netpoints_id'] > 0) {
               //Get netpoint informations
               $netpoint = new Netpoint();
               $netpoint->getFromDB($port['netpoints_id']);
               if ($id2name) {
                  $netpoint->fields['location_name']
                        = Html::clean(Dropdown::getDropdownName('glpi_locations',
                                                                $netpoint->fields['locations_id']));
               }
               $port['netpoints'][$netpoint->fields['id']] = $netpoint->fields;
            }

            //Get VLANS
            $vlan = new NetworkPort_Vlan();
            $tmp  = new Vlan();
            foreach ($vlan->getVlansForNetworkPort($port['id']) as $vlans_id ) {
               $tmp->getFromDB($vlans_id);
               $port['vlans'][$tmp->fields['id']] = $tmp->fields;
            }

            $resp[$port['id']] = $port;
         }
      }
      return $resp;
   }
}
?>
