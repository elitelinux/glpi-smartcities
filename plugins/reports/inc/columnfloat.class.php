<?php
/**
 * @version $Id: columnfloat.class.php 294 2015-05-24 23:46:03Z yllen $
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
class PluginReportsColumnFloat extends PluginReportsColumn {

   private $total;
   private $with_zero = 1;
   private $decimal   = -1;


   function __construct($name, $title, $options=array()) {

      if (!isset($options['extrafine'])) {
         $options['extrafine'] =  "class='right'";
      }

      if (!isset($options['extrabold'])) {
         $options['extrabold'] =  "class='b right'";
      }

      if (isset($options['with_zero'])) {
         $this->with_zero = $options['with_zero'];
      }

      if (isset($options['decimal'])) {
         $this->decimal = $options['decimal'];
      }

      parent::__construct($name, $title, $options);

      $this->total = 0.0;
   }


   function displayValue($output_type, $row) {

      if (isset($row[$this->name])) {
         $this->total += floatval($row[$this->name]);

         if ($row[$this->name] || $this->with_zero) {
            return Html::formatNumber($row[$this->name], false, $this->decimal);
         }
      }
      return '';
   }


   function displayTotal($output_type) {
      return Html::formatNumber($this->total, false, $this->decimal);;
   }
}
?>