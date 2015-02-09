<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Certificates plugin for GLPI
 Copyright (C) 2003-2011 by the certificates Development Team.

 https://forge.indepnet.net/projects/certificates
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of certificates.

 Certificates is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Certificates is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Certificates. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginCertificatesNotificationState extends CommonDBTM {
   
   function getFromDBbyState($plugin_certificates_certificatestates_id) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."` " .
         "WHERE `plugin_certificates_certificatestates_id` = '" . $plugin_certificates_certificatestates_id . "' ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }
   
   function findStates() {
      global $DB;

      $queryBranch='';
      // Recherche les enfants

      $queryChilds= "SELECT `plugin_certificates_certificatestates_id`
      FROM `".$this->getTable()."`";
      if ($resultChilds = $DB->query($queryChilds)) {
         while ($dataChilds = $DB->fetch_array($resultChilds)) {
            $child=$dataChilds["plugin_certificates_certificatestates_id"];
            $queryBranch .= ",$child";
         }
      }

      return $queryBranch;
  }

   function addNotificationState($plugin_certificates_certificatestates_id) {

      if ($this->getFromDBbyState($plugin_certificates_certificatestates_id)) {

         $this->update(array(
         'id'=>$this->fields['id'],
         'plugin_certificates_certificatestates_id'=>$plugin_certificates_certificatestates_id));
      } else {

         $this->add(array(
         'plugin_certificates_certificatestates_id'=>$plugin_certificates_certificatestates_id));
      }
   }
  
   function showAddForm($target) {

      echo "<div align='center'><form method='post'  action=\"$target\">";
      echo "<table class='tab_cadre_fixe' cellpadding='5'><tr ><th colspan='2'>";
      echo __('add not-used status in expiration mail', 'certificates')."</th></tr>";
      echo "<tr class='tab_bg_1'><td>";
      Dropdown::show('PluginCertificatesCertificateState', array('name' => "plugin_certificates_certificatestates_id"));
      echo "</td>";
      echo "<td>";
      echo "<div align='center'><input type='submit' name='add' value=\""._sx('button', 'Post')."\" class='submit' ></div></td></tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
  }
  
   function showForm($target) {
      global $DB;

      $rand=mt_rand();

      $query = "SELECT *
      FROM `".$this->getTable()."`
      ORDER BY `plugin_certificates_certificatestates_id` ASC ";
      if ($result = $DB->query($query)) {
         $number = $DB->numrows($result);
         if ($number != 0) {

            echo "<div align='center'><form method='post' name='massiveaction_form$rand' id='massiveaction_form$rand'  action=\"$target\">";
            echo "<table class='tab_cadre_fixe' cellpadding='5'>";
            echo "<tr>";
            echo "<th></th><th>".__('No used status in expiration mail', 'certificates')."</th>";
            echo "</tr>";
            while($ligne= $DB->fetch_array($result)) {
               $ID=$ligne["id"];
               echo "<tr class='tab_bg_1'>";
               echo "<td class='center' width='10'>";
               echo "<input type='hidden' name='id' value='$ID'>";
               echo "<input type='checkbox' name='item[$ID]' value='1'>";
               echo "</td>";
               echo "<td>".Dropdown::getDropdownName("glpi_plugin_certificates_certificatestates"
               ,$ligne["plugin_certificates_certificatestates_id"]);
               echo"</td>";
               echo "</tr>";
            }

            Html::openArrowMassives("massiveaction_form$rand", true);
            Html::closeArrowMassives(array('delete' => __('Delete permanently')));
            echo "</table>";
            Html::closeForm();
            echo "</div>";
         }
      }
   }
}

?>