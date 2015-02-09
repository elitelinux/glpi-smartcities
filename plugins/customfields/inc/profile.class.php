<?php
/*
----------------------------------------------------------------------
GLPI - Gestionnaire Libre de Parc Informatique
Copyright (C) 2003-2009 by the INDEPNET Development Team.

http://indepnet.net/   http://glpi-project.org/
----------------------------------------------------------------------

LICENSE

This file is part of GLPI.

GLPI is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

GLPI is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GLPI; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
------------------------------------------------------------------------
*/

// ----------------------------------------------------------------------
// Sponsor: Oregon Dept. of Administrative Services, State Data Center
// Original Author of file: Ryan Foster
// Contact: Matt Hoover <dev@opensourcegov.net>
// Project Website: http://www.opensourcegov.net
// Purpose of file: Create a class to take advantage of core features
// such as update and logging.
// ----------------------------------------------------------------------


if (!defined('GLPI_ROOT')) {
   die('Sorry. You can\'t access this file directly.');
}

class PluginCustomfieldsProfile extends CommonDBTM
{
   
   //if profile deleted
   function cleanProfiles($ID)
   {
      $this->delete(array(
         'id' => $ID
      ));
   }
   
   
   function createUserAccess($profile)
   {
      $this->add(array('id' => $profile->getField('id'),
                              'name' => $profile->getField('name')));
   }
   
   
   static function changeprofile()
   {
      
      $prof = new self();
      if ($prof->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_customfields_profiles"] = $prof->fields;
      } else {
         unset($_SESSION["glpi_plugin_customfields_profiles"]);
      }
   }
   
   
   function checkRight($itemtype, $right)
   {
      global $CFG_GLPI;
      
      if (!Session::haveRight($itemtype, $right)) {
         // Check for session timeout
         if (!isset($_SESSION['glpiID'])) {
            Html::redirect($CFG_GLPI['root_doc'] . '/index.php');
            exit();
         }
         Html::displayRightError();
      }
   }
   
   
   function fieldHaveRight($field, $right)
   {
      
      $matches = array(
         "r" => array(
            "r",
            "w"
         ),
         "w" => array(
            "w"
         )
      );
      
      if (isset($_SESSION["glpi_plugin_customfields_profiles"][$field]) && in_array($_SESSION["glpi_plugin_customfields_profiles"][$field], $matches[$right])) {
         return true;
      }
      return false;
   }
   
   
   function getFromDBForProfile($ID)
   {
      global $DB;
      
      $ID_profile = 0;
      // Get user profile
      $query      = "SELECT `id`
                FROM `glpi_plugin_customfields_profiles`
                WHERE `id` = '$ID'";
      
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result)) {
            $ID_profile = $DB->result($result, 0, 0);
         }
      }
      if ($ID_profile) {
         return $this->getFromDB($ID_profile);
      }
      return false;
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

//       if ($item->getType()=='Profile') {
//             return __('Title','customfields');
//       }
      return '';
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;
      
      if ($item->getType()=='Profile') {
         $ID = $item->getField('id');
         $prof = new self();
         
         if (!$prof->getFromDBByProfile($item->getField('id'))) {
            $prof->createUserAccess($item);
         }
         $prof->showForm($item->getField('id'), array('target' => 
                     $CFG_GLPI["root_doc"]."/plugins/customfields/front/profile.form.php"));
      }
      return true;
   }
   
   function getFromDBByProfile($profiles_id) {
      global $DB;

      $ID_profile = 0;
      // Get user profile
      $query = "SELECT `id`
                FROM `glpi_plugin_customfields_profiles`
                WHERE `id` = '$profiles_id'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result)) {
            $ID_profile = $DB->result($result,0,0);
         }
      }
      if ($ID_profile) {
         return $this->getFromDB($ID_profile);
      }
      return false;
   }


   //profiles modification
   function showForm($ID, $options = array())
   {
      global $LANG, $DB;
      
      if (!Session::haveRight("profile", READ)) return false;
      
      $target = $this->getFormURL();
      if (isset($options['target'])) {
         $target = $options['target'];
      }
      
      // TODO : Should be useless if we can use this->showFormHeader() and $this->ShowFormButtons()
      if (!Session::haveRight("profile", UPDATE)) {
         return false;
      }
      
      $canedit = Session::haveRight("profile", UPDATE);
      $prof    = new Profile();
      if ($ID) {
         $this->getFromDB($ID);
         $prof->getFromDB($ID);
      }
      
      $itemtype = '';
      
      $query = "SELECT *
                FROM `glpi_plugin_customfields_fields`
                WHERE `restricted` = 1
                ORDER BY `itemtype`, `sort_order`;";
      
     
      if (($result = $DB->query($query)) && $DB->numrows($result)) {
         echo "<form action='" . $target . "' method='post'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'>";
         echo "<th colspan='4'>". __('Title','customfields') ." ".
               $prof->fields["name"]."</th>";
         echo "</tr>";
          
         while ($data = $DB->fetch_array($result)) {
            if ($data['itemtype'] != $itemtype) {
               $itemtype = $data['itemtype'];
               echo "<tr><th colspan='3'>" . __($itemtype) . "</th></tr>";
            }
            $profile_field = $data['itemtype'] . '_' . $data['system_name'];
            echo "<tr class='tab_bg_2'><td>" . $data['label'] . " (" . __($data['data_type'],'customfields') . "):</td><td>";
            
            if ($data['data_type'] == 'sectionhead') {
               Dropdown::showYesNo($profile_field, $this->fields[$profile_field], 1, 1, 1);
            } else {
               Profile::dropdownNoneReadWrite($profile_field, $this->fields[$profile_field], 1, 1, 1);
            }
            echo "</td></tr>";
         }
         
         if ($canedit) {
            echo "<tr class='tab_bg_1'>";
            echo "<td class='center' colspan='2'>";
            echo "<input type='hidden' name='id' value=$ID>";
            echo "<input type='submit' name='update_user_profile' value=\"" . _sx('button', 'Update') . "\" class='submit'>";
            echo "</td></tr>";
         }
         echo "</table>";
         $options['candel'] = false;
         Html::closeForm();
      } else {
         echo __('There is no restricted field','customfields');
      }
      
   }
   
}