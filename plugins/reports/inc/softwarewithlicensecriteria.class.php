<?php
/**
 * @version $Id: softwarewithlicensecriteria.class.php 294 2015-05-24 23:46:03Z yllen $
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
 * Dropdown for softwares with license
 */
class PluginReportsSoftwareWithLicenseCriteria extends PluginReportsDropdownCriteria {


   /**
    * @param $report
    * @param $name      (default 'softwares_id')
    * @param $label     (default '')
   **/
   function __construct($report, $name='softwares_id', $label='') {

      parent::__construct($report, $name, 'glpi_softwares',
                          ($label ? $label : _n('Software', 'Software', 1)));
   }


   function displayDropdownCriteria() {
      global $DB;

      $query = "SELECT `glpi_softwares`.`name`, `glpi_softwares`.`id`
                FROM `glpi_softwarelicenses`
                LEFT JOIN `glpi_softwares`
                     ON `glpi_softwarelicenses`.`softwares_id` = `glpi_softwares`.`id`
                LEFT JOIN `glpi_entities`
                     ON (`glpi_softwares`.`entities_id` = `glpi_entities`.`id`)
                WHERE `glpi_softwarelicenses`.`entities_id`
                           IN (" . $_SESSION['glpiactiveentities_string'] . ")
                GROUP BY `glpi_softwares`.`name`";
      $result = $DB->query($query);

      if ($DB->numrows($result)) {
         echo "<select name='".$this->getName()."'>";
         while ($data = $DB->fetch_array($result)) {
            echo "<option value='" . $data["id"] . "'";
            if ($data["id"] == $this->getParameterValue()) {
               echo " selected = 'selected'";
            }
            echo ">" . $data["name"];
            echo "</option>";
         }
         echo "</select>";
      } else {
         echo "<span class='red b center'>".__('No item found')."</span>";
      }
   }

}
?>