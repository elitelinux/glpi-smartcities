<?php
/*
  -------------------------------------------------------------------------
  Moreticket plugin for GLPI
  Copyright (C) 2013 by the Moreticket Development Team.
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Moreticket.

  Moreticket is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Moreticket is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Moreticket. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginMoreticketConfig extends CommonDBTM {

   static $rightname = "plugin_moreticket";
   
   static function getConfig($update = false) {
      static $config = null;

      if (is_null($config)) {
         $config = new self();
      }
      if ($update) {
         $config->getFromDB(1);
      }
      return $config;
   }

   function __construct() {
      if (TableExists($this->getTable())) {
         $this->getFromDB(1);
      }
   }
   
   static function getTypeName($nb=0) {
      return __("Setup");
   }
   
   function getRights($interface='central') {

      $values = parent::getRights();

      unset($values[CREATE], $values[DELETE], $values[PURGE]);
      return $values;
   }
   
   function showForm(){
      
      $this->getFromDB(1);
      echo "<div class='center'>";
      echo "<form name='form' method='post' action='".$this->getFormURL()."'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".__("Plugin configuration", "moreticket")."</th></tr>";
      
      echo "<input type='hidden' name='id' value='1'>";
      
      echo "<tr class='tab_bg_1'>
            <td>".__("Use waiting process", "moreticket")."</td><td>";
      Dropdown::showYesNo("use_waiting", $this->fields["use_waiting"]);
      echo "</td>";
      echo "</tr>";
      
      if ($this->usewaiting() == true) {
      
         echo "<tr class='tab_bg_1'>
               <td>".__("Report date is mandatory", "moreticket")."</td><td>";
         Dropdown::showYesNo("date_report_mandatory", $this->fields["date_report_mandatory"]);
         echo "</td>";
         echo "</tr>";
         
         echo "<tr class='tab_bg_1'>
               <td>".__("Waiting type is mandatory", "moreticket")."</td><td>";
         Dropdown::showYesNo("waitingtype_mandatory", $this->fields["waitingtype_mandatory"]);
         echo "</td>";
         echo "</tr>";
         
      }
      echo "<tr class='tab_bg_1'>
            <td>".__("Use solution process", "moreticket")."</td><td>";
      Dropdown::showYesNo("use_solution", $this->fields["use_solution"]);
      echo "</td>";
      echo "</tr>";
      
      if ($this->useSolution() == true) {
         
         echo "<tr class='tab_bg_1'>
               <td>".__("Solution type is mandatory", "moreticket")."</td><td>";
         Dropdown::showYesNo("solutiontype_mandatory", $this->fields["solutiontype_mandatory"]);
         echo "</td>";
         echo "</tr>";
         
      }
      
      echo "<tr class='tab_bg_1'>
            <td>".__("Close ticket informations", "moreticket")."</td><td>";
      Dropdown::showYesNo("close_informations", $this->fields["close_informations"]);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>
            <td>".__("Status used to display solution bloc", "moreticket")."</td><td>";

      $solution_status = $this->getSolutionStatus($this->fields["solution_status"]);

      foreach (array(Ticket::CLOSED, Ticket::SOLVED) as $status) {
         $checked = isset($solution_status[$status]) ? 'checked' : '';
         echo "<input type='checkbox' name='solution_status[".$status."]' value='1' $checked>&nbsp;";
         echo Ticket::getStatus($status)."<br>";
      }
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td colspan='2' align='center'>";
      echo "<input type='submit' name='update' value=\""._sx("button", "Post")."\" class='submit' >";
      echo "</td>";
      echo "</tr>";
      
      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }
   
   function getSolutionStatus($input){
      
      $solution_status = array();
      
      if (!empty($input)) {
         $solution_status = json_decode($input, true);
      }
      
      return $solution_status;
   }
   
   function useWaiting() {
      return $this->fields['use_waiting'];
   }
   
   function mandatoryReportDate() {
      return $this->fields['date_report_mandatory'];
   }
   
   function mandatoryWaitingType() {
      return $this->fields['waitingtype_mandatory'];
   }
   
   function useSolution() {
      return $this->fields['use_solution'];
   }
   
   function mandatorySolutionType() {
      return $this->fields['solutiontype_mandatory'];
   }
   
   function solutionStatus() {
      return $this->fields["solution_status"];
   }
   
   function closeInformations(){
      return $this->fields["close_informations"];
   }
}

?>