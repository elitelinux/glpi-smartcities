<?php
/**
 * @version $Id: methodtools.class.php 396 2014-11-23 18:46:25Z yllen $
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

class PluginWebservicesMethodTools extends PluginWebservicesMethodCommon {

   /**
    * Get a list of KB/FAQ articles
    * for an authenticated user (or anonymous if allowed from config)
    *
    * @param $params array of options
    * @param $protocol the commonication protocol used
   **/
   static function methodListKnowBaseItems($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array('start'         => 'integer,optional',
                      'limit'         => 'integer,optional',
                      'contains'      => 'string,optional',
                      'category'      => 'string,optional',
                      'faq'           => 'bool,optional',
                      'type'          => 'string,optionnal',
                      'help'          => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         $params['faq'] = 1;

      } else if (!(isset($params['faq']))) {
         $params['faq'] = 0;
      }

      $kb = new KnowbaseItem();
      if (!$kb->canView()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      if (!(isset($params['category']))) {
         $params['category'] = 0;
      } else if (!is_numeric($params['category'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'category');
      }

      if (!(isset($params['contains']))) {
         $params['contains'] = '';
      }

      if (!isset($params['type'])) {
         $params['type'] == 'search';
      }

      $query = KnowbaseItem::getListRequest(array('faq'      =>  $params['faq'],
                                                  'contains' => addslashes($params['contains']),
                                                  'knowbaseitemcategories_id'
                                                             => $params['category']),
                                                   $params['type']);

      $resp = array ();
      if (isset($params['count'])) {
         $resp['count'] = $DB->numrows($DB->query($query));
      } else {
         $start = 0;
         $limit = $CFG_GLPI["list_limit_max"];
         if (isset($params['limit']) && is_numeric($params['limit'])) {
            $limit = $params['limit'];
         }
         if (isset($params['start']) && is_numeric($params['start'])) {
            $start = $params['start'];
         }
         $query .= " LIMIT $start, $limit";

         foreach ($DB->request($query) as $data) {
            $data['resume'] = Toolbox::unclean_cross_side_scripting_deep($data['answer']);
            $data['resume'] = Html::clean(Html::resume_text(Html::clean($data['resume']), 200));
            $data['resume'] = html_entity_decode($data['resume'], 0, 'UTF-8');
            unset($data['answer']);
            $resp[] = $data;
         }
      }

      return $resp;
   }


   /**
    * Get list of documents attached to an item
    *
    * @param   $item    Object
    * @param   $id2name Boolean
    *
    * @return Array of documents
    */
   static function getDocForItem($item, $id2name=false) {
      global $DB;

      if (Session::getLoginUserID()) {
         $query   = "SELECT * ";
      } else {
         $query   = "SELECT `id`, `name`, `filename`, `mime` ";
         $id2name = false;
      }
      $query .= "FROM `glpi_documents`
                 WHERE `id` IN (SELECT `documents_id`
                                FROM `glpi_documents_items`
                                WHERE `itemtype` = '".$item->getType()."'
                                      AND `items_id` = '".$item->getID()."')";

      $resp = array();
      foreach ($DB->request($query) as $data) {
         if ($id2name) {
            $data['users_name']
               = Html::clean(getUserName($data['users_id']));
            $data['documentcategories_name']
               = Html::clean(Dropdown::getDropdownName('glpi_documentcategories',
                                                       $data['documentcategories_id']));
         }
         $resp[] = $data;
      }

      return $resp;
   }


   /**
    * Get a KB/FAQ article
    * for an authenticated user (or anonymous if allowed from config)
    *
    * @param $params array of options
    * @param $protocol the commonication protocol used
   **/
   static function methodGetKnowBaseItem($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array('id'      => 'integer,mandatory',
                      'help'    => 'bool,optional');
      }

      $kb = new KnowbaseItem();
      if (!Session::haveRightsOr('knowbase', array(READ, KnowbaseItem::READFAQ))) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      if (!isset($params['id'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'id');
      }
      if (!is_numeric($params['id'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'id');
      }
      if (!$kb->can($params['id'], READ)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
      }

      $answer  = Toolbox::unclean_cross_side_scripting_deep($kb->getField('answer'));
      $resp    = $kb->fields;

      $resp['answer']       = $answer;
      $resp['answer_text']  = html_entity_decode(Html::clean($answer), 0, 'UTF-8');
      $resp['documents']    = self::getDocForItem($kb);
      $kb->updateCounter();

      return $resp;
   }
}