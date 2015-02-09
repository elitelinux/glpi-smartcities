<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Financialreports plugin for GLPI
 Copyright (C) 2003-2011 by the Financialreports Development Team.

 https://forge.indepnet.net/projects/financialreports
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Financialreports.

 Financialreports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Financialreports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Financialreports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginFinancialreportsConfig extends CommonDBTM {
   
   function showForm() {
      global $DB;
      
      $query = "SELECT * FROM
               `".$this->getTable()."`
               ORDER BY `states_id` ASC";
      if ($result = $DB->query($query)) {
         $number = $DB->numrows($result);
         if ($number != 0) {

            echo "<form method='post' name='massiveaction_form' id='massiveaction_form' action='".$this->getFormURL()."'>";
            echo "<div align='center'>";
            echo "<table class='tab_cadre_fixe' cellpadding='5'>";
            echo "<tr>";
            echo "<th>".__('Status')."</th><th></th>";
            echo "</tr>";
            while($ligne= $DB->fetch_array($result)) {
               $ID=$ligne["id"];
               echo "<tr class='tab_bg_1'>";
               echo "<td width='10'>";
               echo "<input type='checkbox' name='item[$ID]'";
               if (isset($_POST['check']) && $_POST['check'] == 'all')
                  echo " checked ";
               echo ">";
               echo "</td>";
               echo "<td>".Dropdown::getDropdownName("glpi_states",$ligne["states_id"])."</td>";
               echo "</tr>";
            }
            echo "</table></div>";
            
            Html::openArrowMassives("massiveaction_form", true);
            Html::closeArrowMassives(array('delete_state' => _sx('button','Delete permanently')));
            Html::closeForm();
            
            echo "<div align='center'><form method='post' action='".$this->getFormURL()."'>";
            echo "<table class='tab_cadre_fixe' cellpadding='5'><tr ><th colspan='2'>";
            echo __('Disposal status', 'financialreports')." : </th></tr>";
            echo "<tr class='tab_bg_1'><td>";
            Dropdown::show('State', array('name' => "states_id",'value' => $ligne["states_id"]));
            echo "</td>";
            echo "<td>";
            echo "<div align='center'>";
            echo "<input type='submit' name='add_state' value='". _sx('button', 'Post')."' class='submit' >";
            echo "</div></td></tr>";
            echo "</table>";
            Html::closeForm();
            echo "</div>";
            
         } else {
            echo "<div align='center'><form method='post' action='".$this->getFormURL()."'>";
            echo "<table class='tab_cadre' cellpadding='5'><tr ><th colspan='2'>";
            echo __('Disposal status', 'financialreports')." : </th></tr>";
            echo "<tr class='tab_bg_1'><td>";
            Dropdown::show('State', array('name' => "states_id"));
            echo "</td>";
            echo "<td>";
            echo "<div align='center'>";
            echo "<input type='submit' name='add_state' value='". _sx('button', 'Post')."' class='submit' >";
            echo "</div></td></tr>";
            echo "</table>";
            Html::closeForm();
            echo "</div>";
         }
      }
   }
   
   function getRights($interface='central') {

      $values = parent::getRights();

      unset($values[CREATE], $values[UPDATE], $values[DELETE], $values[PURGE]);
      return $values;
   }
}
?>