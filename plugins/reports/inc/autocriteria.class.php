<?php
/**
 * @version $Id: autocriteria.class.php 294 2015-05-24 23:46:03Z yllen $
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
 * AutCriteria class manage a new search & filtering criteria
 * It manage display & sql code associated
 */
abstract class PluginReportsAutoCriteria {

   //Criteria's internal name
   private $name = "";

   //Label of the criteria (refers to an entry in the locale file)
   private $criterias_labels = array ();

   //Parameters are stored as name => value
   private $parameters = array ();

   //Field in the SQL request (can be table.field)
   private $sql_field = "";

   //Report in which the criteria will be added to
   private $report = null;


   /**
    * Contructor
    * @param report              the report in which the criteria is added
    * @param $name               the criteria's name
    * @param $sql_field          the sql field associated with the criteria
    *                            (can be set later with setSqlField).(default '')
    *          - Sql_field can be prefixed with table name
    *          - if sql_field=='' then sql_field=name
    * @param $label     string   (default NULL)
   **/
   function __construct($report, $name, $sql_field='', $label=NULL) {

      $this->setName($name);
      if ($sql_field) {
         $this->setSqlField($sql_field);
      } else {
         $this->setSqlField($name);
      }
      if (!is_null($label)) {
         $this->addCriteriaLabel($this->getName(), $label);
      }
      $this->setReport($report);
      $this->report->addCriteria($this);
      $this->setDefaultValues();
   }


   //-------------- Getters ------------------//

   /**
    * Get report object
   **/
   function getReport() {
      return $this->report;
   }


   /**
    * Get all parameters associated with the criteria
   **/
   function getParameterValue() {
      return $this->parameters[$this->name];
   }


   /**
    * Get sql_field associated with the criteria
    *
    * @return the sql_field associated with the criteria
   **/
   function getSqlField() {
      return $this->sql_field;
   }


   /**
    * Get a specific parameter
    *
    * @param parameter the parameter's name
    *
    * @return the parameter's value
   **/
   function getParameter($parameter) {
      return $this->parameters[$parameter];
   }


   /**
    * Get the label associated with the criteria
    *
    * @param parameter the parameter's name
    *
    * @return label associated with the criteria
   **/
   function getCriteriaLabel($parameter='') {
      return $this->criterias_labels[$parameter ? $parameter : $this->getName()];
   }


   /**
    * Get the criteria's title
   **/
   function getSubName() {
      return "";
   }


   /**
    * Get criteria's name
    *
    * @return criteria's name
   **/
   function getName() {
      return $this->name;
   }



   /**
    * Get all the parameters associated with the criteria
    *
    * @return the parameters
   **/
   function getParameters() {
      return $this->parameters;
   }


   /**
    * Build Sql code associated with the criteria (to be included into the global report's sql query)
    *
    * @param $link   default 'AND')
    *
    * @return a where sql request
   **/
   public function getSqlCriteriasRestriction($link='AND') {
      return $link . " " . $this->getSqlField() . "='" . $this->parameters[$this->getName()] . "' ";
   }


   /**
    * Get URL to be used by bookmarking system
    *
    * @return the bookmark's url associated with the criteria
   **/
   public function getBookmarkUrl() {

      $url = "";
      foreach ($this->parameters as $parameter => $value) {
         $url .= '&' .
         $parameter . '=' . $value;
      }
      return $url;
   }


   //-------------- Setters ------------------//

   /**
    * Set report
    *
    * @param $report the report in which the criteria is put
   **/
   function setReport($report) {
      $this->report = $report;
   }


   /**
    * Set criteria's parameters
    *
    * @param $parameters the parameters
   **/
   function setParameters($parameters) {
      $this->parameters = $parameters;
   }


   /**
    * Add a new parameter to the criteria
    * If parameter exists, it overwrites the existing values
    *
    * @param $name   parameter's name
    * @param $value  parameter's value
   **/
   function addParameter($name, $value) {
      $this->parameters[$name] = $value;
   }


   /**
    * Set sql field associated with the criteria
    *
    * @param sql_field sql field associated with the criteria
   **/
   function setSqlField($sql_field) {
      $this->sql_field = $sql_field;
   }


   /**
    * Set criteria's name
    *
    * @param $name   criteria's name
   **/
   function setName($name) {
      $this->name = strtr($name, '`.', '__');
   }


   /**
    * Add a label to the criteria
    *
    * @param $name   criteria's name
    * @param $label  add criteria's label
   **/
   function addCriteriaLabel($name, $label) {
      $this->criterias_labels[$name] = $label;
   }


   /**
    * Set criteria's default value()
    * This method is abstract ! Needs to be implemented in each criteria
   **/
   abstract public function setDefaultValues();


   //-------------- Other ------------------//

   /**
    * Display criteria in the criteria's selection form
    * This method is abstract : needs to be implemented by each criteria !
   **/
   abstract public function displayCriteria();


   /**
    * Set parameter's values get the criteria working
   **/
   public function manageCriteriaValues() {

      foreach ($this->parameters as $parameter => $value) {

         //Add GET & POST values in order to get pager & export working correctly
         if (isset($_GET[$parameter])) {
            $_POST[$parameter] = $this->parameters[$parameter] = $_GET[$parameter];
         } else {
            if (isset ($_POST[$parameter])) {
               $this->parameters[$parameter] = $_POST[$parameter];
            } else {
               $_POST[$parameter] = $this->parameters[$parameter];
            }
         }
      }
   }

}
?>