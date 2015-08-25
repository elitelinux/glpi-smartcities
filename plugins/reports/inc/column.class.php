<?php
/**
 * @version $Id: column.class.php 294 2015-05-24 23:46:03Z yllen $
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
class PluginReportsColumn {

   // name of the column in the SQL result set
   public    $name;
   // Fields for ORDER BY when this column is selected
   public $sorton;
   // Label of the column in the report
   private   $title;
   // Extras class for rendering in HTML
   private   $extrafine;
   // Extras class for rendering in HTML in Bold
   private   $extrabold;
   // Manage total for this colum (if handled by sub-type)
   protected $withtotal;


   function __construct($name, $title, $options=array()) {

      $this->name      = $name;
      $this->title     = $title;

      // Extras class for each cell
      $this->extrafine = (isset($options['extrafine']) ? $options['extrafine'] : '');

      // Extras class for each total cell
      $this->extrabold = (isset($options['extrabold']) ? $options['extrabold'] : "class='b'");

      // Enable total for this column (if handle bu subtype)
      $this->withtotal = (isset($options['withtotal']) ? $options['withtotal'] : false);

      // Enable sort for this column
      $this->sorton = (isset($options['sorton']) ? $options['sorton'] : false);
   }


   function showTitle($output_type, &$num) {

      if (($output_type != Search::HTML_OUTPUT) || !$this->sorton) {
          echo Search::showHeaderItem($output_type, $this->title, $num);
          return;
      }
      $order = 'ASC';
      $issort = false;
      if (isset($_REQUEST['sort']) && $_REQUEST['sort']==$this->name) {
         $issort = true;
         if (isset($_REQUEST['order']) && $_REQUEST['order']=='ASC') {
            $order = 'DESC';
         }
      }
      $link  = $_SERVER['PHP_SELF'];
      $first = true;
      foreach ($_REQUEST as $name => $value) {
         if (!in_array($name,array('sort','order','PHPSESSID'))) {
            $link .= ($first ? '?' : '&amp;');
            $link .= $name .'='.urlencode($value);
            $first = false;
         }
      }
      $link .= ($first ? '?' : '&amp;').'sort='.urlencode($this->name);
      $link .= '&amp;order='.$order;
      echo Search::showHeaderItem($output_type, $this->title, $num,
                                  $link, $issort, ($order=='ASC'?'DESC':'ASC'));
   }


   function showValue($output_type, $row, &$num, $row_num, $bold=false) {

      echo Search::showItem($output_type, $this->displayValue($output_type, $row), $num, $row_num,
                            ($bold ? $this->extrabold : $this->extrafine));
   }


   function showTotal($output_type, &$num, $row_num) {

      echo Search::showItem($output_type,
                            ($this->withtotal ? $this->displayTotal($output_type) : ''),
                            $num, $row_num, $this->extrabold);
   }


   function displayValue($output_type, $row) {

      if (isset($row[$this->name])) {
         return $row[$this->name];
      }
      return '';
   }


   function displayTotal($output_type) {
      return '';
   }
}
?>