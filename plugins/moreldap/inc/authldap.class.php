<?php
/*
 * @version $Id: configauthldap.php 36 2012-08-31 13:59:28Z dethegeek $
----------------------------------------------------------------------
MoreLDAP plugin for GLPI
----------------------------------------------------------------------

LICENSE

This file is part of MoreLDAP plugin.

MoreLDAP plugin is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

MoreLDAP plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with MoreLDAP plugin; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
------------------------------------------------------------------------
@package   MoreLDAP
@author    the MoreLDAP plugin team
@copyright Copyright (c) 2014-2014 MoreLDAP plugin team
@license   GPLv2+
http://www.gnu.org/licenses/gpl.txt
@link      https://forge.indepnet.net/projects/moreldap
@link      http://www.glpi-project.org/
@since     2014
------------------------------------------------------------------------
*/
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMoreldapAuthLDAP extends CommonDBTM {

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;
      
      $tabNames = array();
      
      if (in_array(get_class($item), array("AuthLDAP"))) {
         $tabNames = array(1 => __("MoreLDAP configuration", "moreldap"));
      } else {
         $tabNames = '';
      }
      return $tabNames;
   }

   function preconfig($type = '') {
      switch ($type) {
      	default:
      	   $this->fields['location'] = 'PhysicalDeliveryOfficeName';
      	   $this->fields['location_enabled'] = 'N';
      	   $this->fields['entities_id'] = 0;
      	   $this->fields['is_recursive'] = 0;
      }
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
   
      if (in_array(get_class($item), array("AuthLDAP"))) {
         $AuthLDAP = new PluginMoreldapAuthLDAP;
         
         if (!$AuthLDAP->getFromDB($item->fields['id'])) {
            //The directory exists in GLPI but there is no data in the plugin
            $AuthLDAP->preconfig();
            
         }
         
         $location_enabled = ($AuthLDAP->fields['location_enabled'] == 'Y') ? ' checked' : '';
         
         echo '<div class="spaced">';
         echo '<form id="items" name="items" method="post" action="' . Toolbox::getItemTypeFormURL(__CLASS__). '">';
         echo '<table class="tab_cadre_fixehov">';
         echo '<tr class="tab_bg_2">';
         echo '<th colspan="2">' . __("MoreLDAP", "moreldap") . '</th>';
         echo '</tr>';
         echo '<tr class="tab_bg_1">';
         echo '<td>' . __("LDAP attribute : location of users", "moreldap") . '</td>';
         echo '<td>' . __("Enabled", "moreldap") . '&nbsp;<input type="checkbox" name="location_enabled"' . $location_enabled . ' value="location_enabled"><br />';
         echo '<input size="72" type="text" name="location" value="' . $AuthLDAP->fields['location'] . '"> ';
         echo '<br />';
         Entity::dropdown(array('value' => $AuthLDAP->fields['entities_id']));
         echo '&nbsp;' . __("recursive", "moreldap") . "&nbsp;";
         Dropdown::showYesNo('is_recursive', $AuthLDAP->fields['is_recursive']);
         echo ' </td>';
         echo '</tr>';
         echo '<tr class="tab_bg_1">';
         echo '<td colspan="2" class="center">';
         echo '<input type="hidden" value="' . $item->fields['id'] . '" name="id">';
         echo '<input type="submit" class="submit" name="update" value="' . _sx('button', 'Save') . '">';
         echo '</td>';
         echo '</tr>';
         echo '</table>';
         Html::closeForm();
         echo "</div>";
      } 
      return true;
   }
    
}