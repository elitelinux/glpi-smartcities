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

// Sponsor: Oregon Dept. of Administrative Services, State Data Center
// Original Author of file: Ryan Foster
// Contact: Matt Hoover <dev@opensourcegov.net>
// Project Website: http://www.opensourcegov.net
// Purpose of file: Main configuration page
// ----------------------------------------------------------------------

include ('../../../inc/includes.php');

$plugin = new Plugin();

// Check if plugin is installed and enabled

if ($plugin->isActivated("customfields")) {

   // Check ACL

   Session::checkRight("config", UPDATE);

   // Header

   Html::header(
      __('Configuration'),
      '',
      'config',
      'plugin',
      'customfields'
   );

   echo "<div class='center'>";
   
   echo "<table class='tab_cadre' cellpadding='5'>";
   echo "<tr><th colspan='5'>"
      . __('Manage Custom Fields', 'customfields')
      . "</th></tr>";
   echo "<tr>";
   echo "<th>" . __('Device Type', 'customfields') . "</th>";
   echo "<th>" . __('Status', 'customfields') . "</th>";
   echo "<th>&nbsp;</th>";
   echo "<th>" . __('Device Type', 'customfields') . "</th>";
   echo "<th>" . __('Status', 'customfields') . "</th>";
   echo "</tr>";

   // List supported item types
   
   $query = "SELECT *
             FROM `glpi_plugin_customfields_itemtypes`
             WHERE `itemtype` <> 'Version'
             ORDER BY `id`";
   
   $result = $DB->query($query);
   $twoData = array();
   $twoDataOutput = array();
   $continueFetchAssoc = true;
   //while ( ($twoData[1] = $DB->fetch_assoc($result)) || ($twoData[2] = $DB->fetch_assoc($result)) ) {
   while ($continueFetchAssoc) {
   	  $twoData[1] = $DB->fetch_assoc($result);
   	  $twoData[2] = $DB->fetch_assoc($result);
   	  if ($twoData[1] == false || $twoData[2] == false) {
   	  	$continueFetchAssoc = false;
   	  }
   	  $twoDataOutput[1] = "<td>&nbsp;</td><td>&nbsp;</td>";
   	  $twoDataOutput[2] = $twoDataOutput[1];
      echo "<tr class='tab_bg_1'>";
      foreach($twoData as $index => $data) {
	   	 if ($data !== false) {
	   	 	if (class_exists($data['itemtype'])) {
		
		         $item = new $data['itemtype']();
		
		         if ($item->canCreate()) {
		
		            // List only, if the user can create an object of the type
		
		            $twoDataOutput[$index] = "<td><a href='./manage.php?itemtype="
		               . $data['itemtype']
		               . "'>"
		               . call_user_func(
		                  array(
		                     $data['itemtype'],
		                     'getTypeName'
		                  )
		               )
		               . "</a></td>";
		
		            // Show enabled or disabled?
		
		            if ($data['enabled'] == 1) {
		
		               $twoDataOutput[$index] .=  "<td class='b'>"
		                  . __('Enabled', 'customfields')
		                  . "</td>";
		
		            } else {
		
		               $twoDataOutput[$index] .=  "<td><i>"
		                  . __('Disabled', 'customfields')
		                  . "</i></td>";
		
		            }
		
		        }
		
	   	 	 }
	   	 	 
	   	 }
	   	  
      }
      echo $twoDataOutput[1] . "<td>&nbsp;</td>" . $twoDataOutput[2];
      echo "</tr>";

   }

   echo "</table><br>";

   // Custom dropdowns
   
   echo "<table class='tab_cadre' cellpadding='5'>";
   echo "<tr><th>" . __('Setup of Custom Fields Plugin', 'customfields') . "</th></tr>";
   echo "<tr class='tab_bg_1'><td class='center'>";
   echo "<a href='./dropdown.php'>"
      . __('Manage Custom Dropdowns', 'customfields')
      . "</a>";
   echo "</td></tr>";
   echo "</table></div>";
   
} else {

   // Custom fields plugin not activated

   Html::header(__('Setup'), '', "config", "plugins");
   echo "<div class='center'><br><br>"
      . "<img src=\""
      . $CFG_GLPI["root_doc"]
      . "/pics/warning.png\" alt='warning'><br><br>";

   // text is hard coded because language setting are not accessible

   echo "<b>Please activate the plugin</b></div>";

}

// Footer

if (strstr($_SERVER['PHP_SELF'], "popup")) {
   Html::popFooter();
} else {
   Html::footer();
}