<?php
/**
 * @version $Id: columnlink.class.php 294 2015-05-24 23:46:03Z yllen $
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   reports
 @authors    Nelly Mahu-Lasson, Remi Collet, Alexandre Delaunay
 @copyright Copyright (c) 2009-2015 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

/**
 * class PluginReportsColumn to manage output
 */
class PluginReportsColumnLink extends PluginReportsColumn {

   private $obj           = NULL;
   private $with_comment  = 0;
   private $with_navigate = 0;


   function __construct($name, $title, $itemtype, $options=array()) {

      parent::__construct($name, $title, $options);

      $this->obj = getItemForItemtype($itemtype);

      if (isset($options['with_comment'])) {
         $this->with_comment = $options['with_comment'];
      }

      if (isset($options['with_navigate'])) {
         $this->with_navigate = $options['with_navigate'];
         Session::initNavigateListItems($this->obj->getType(), _n('Report', 'Reports', 2));
      }
   }


   function displayValue($output_type, $row) {

      if (!isset($row[$this->name]) || !$row[$this->name]) {
         return '';
      }

      if (!$this->obj || !$this->obj->getFromDB($row[$this->name])) {
         return $row[$this->name];
      }

      if ($this->with_navigate) {
         Session::addToNavigateListItems($this->obj->getType(), $row[$this->name]);
      }

      if ($output_type == Search::HTML_OUTPUT) {
         return $this->obj->getLink($this->with_comment);
      }

      return $this->obj->getNameID();
   }
}
?>
