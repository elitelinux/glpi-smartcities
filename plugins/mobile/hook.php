<?php
/*
 * 
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------


function plugin_mobile_install() {
   global $DB, $LANG;

 
    if (! TableExists("glpi_plugin_mobile_options")) {
        $query_op = "CREATE TABLE `glpi_plugin_mobile_options` (
			`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			`users_id` INTEGER NOT NULL,
			`cols_limit` INTEGER,
			`rows_limit` INTEGER,
			`edit_mode` INTEGER DEFAULT 1,
			`native_select` INTEGER DEFAULT 1,
			PRIMARY KEY (`id`)
			)
			ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				";
        $DB->query($query_op) or die("error creating glpi_plugin_mobile_options " . $DB->error());
    } 
 
 
    if (! TableExists("glpi_plugin_mobile_profiles")) {
        $query_pro = "CREATE TABLE `glpi_plugin_mobile_profiles` (
			`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			`profiles_id` VARCHAR(45) NOT NULL,
			`mobile_user` CHAR(1),
			PRIMARY KEY (`id`)
			)
			ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				";
        $DB->query($query_pro) or die("error creating glpi_plugin_mobile_profiles " . $DB->error());
    }  
 

$query1 = "INSERT INTO glpi_plugin_mobile_options (users_id, cols_limit, rows_limit, edit_mode)
                  VALUES ('".$_SESSION['glpiID']."', '3', '9', '0')";  
  
$DB->queryOrDie($query1, "insert profiles for Mobile in plugin"); 
      
   require_once "inc/profile.class.php";
   PluginMobileProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   
   
   return true;
}

function plugin_mobile_uninstall() {
   global $DB;
   
$drop1 = "DROP TABLE glpi_plugin_mobile_options";

$DB->queryOrDie($drop1, "drop profiles for Mobile in plugin"); 

$drop2 = "DROP TABLE glpi_plugin_mobile_profiles";

$DB->queryOrDie($drop2, "drop profiles for Mobile in plugin"); 
      
   return true;
}


function plugin_get_headings_mobile($item,$withtemplate){
   global $LANG;
      
   switch (get_class($item)) {
      case 'Profile' :
         if ($item->getField('id') > 0)
            return array(
               1 => $LANG['plugin_mobile']["name"]
            );
         break;
   }
   return false;
}

function plugin_headings_actions_mobile($item){
   
   switch (get_class($item)) {
      case 'Profile' :
         return array(
            1 => "plugin_headings_mobile_profile"
         );
         break;
   }
   return false;
}


function plugin_headings_mobile_profile($item,$withtemplate=0) {
	global $CFG_GLPI;
	
	$prof = new PluginMobileProfile();
	 
	if (!$prof->getFromDBByProfile($item->getField('id')))
      $prof->createAccess($item->getField('id'));
      
	$prof->showForm(
      $item->getField('id'), 
      array('target' => $CFG_GLPI["root_doc"]."/plugins/mobile/front/profile.form.php")
   );
}
?>
