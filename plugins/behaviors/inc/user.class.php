<?php
/**
 * @version $Id: user.class.php 172 2014-11-15 17:41:55Z yllen $
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Behaviors plugin for GLPI.

 Behaviors is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Behaviors is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

 @package   behaviors
 @author    Remi Collet
 @copyright Copyright (c) 2010-2014 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2010

 --------------------------------------------------------------------------
*/

class PluginBehaviorsUser {


   static private function getUserGroup ($entity, $userid, $filter='', $first=true) {
      global $DB;

      $config = PluginBehaviorsConfig::getInstance();

      $query = "SELECT glpi_groups.id
                FROM glpi_groups_users
                INNER JOIN glpi_groups ON (glpi_groups.id = glpi_groups_users.groups_id)
                WHERE glpi_groups_users.users_id = '".$userid."'".
                getEntitiesRestrictRequest(' AND ', 'glpi_groups', '', $entity, true);

      if ($filter) {
         $query .= "AND (".$filter.")";
      }
      $rep = array();
      foreach ($DB->request($query) as $data) {
         if ($first) {
            return $data['id'];
         }
         $rep[] = $data['id'];
      }
      return ($first ? 0 : $rep);
   }


   static function getRequesterGroup ($entity, $userid, $first=true) {
      return self::getUserGroup($entity, $userid, '`is_requester`', $first);
   }


   static function getTechnicianGroup ($entity, $userid, $first=true) {
      return self::getUserGroup($entity, $userid, '`is_assign`', $first);
   }

}
