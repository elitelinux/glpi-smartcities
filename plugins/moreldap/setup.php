<?php
/*
 * @version $Id: setup.php 36 2012-08-31 13:59:28Z dethegeek $
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

define ("PLUGIN_MORELDAP_VERSION", "0.1.2");

// Minimal GLPI version, inclusive
define ("PLUGIN_MORELDAP_GLPI_MIN_VERSION", "0.84");
// Maximum GLPI version, exclusive
define ("PLUGIN_MORELDAP_GLPI_MAX_VERSION", "0.85");

// Get the name and the version of the plugin - Needed
function plugin_version_moreldap() {
	global $LANG;

	$author = "<a href='mailto:dethegeek@gmail.com'>Dethegeek</a>";
	return array ('name'     => __("MoreLDAP", "moreldap"),
			'version'        => PLUGIN_MORELDAP_VERSION,
			'author'         => $author,
			'license'        => 'GPLv2+',
			'homepage'       => 'https://forge.indepnet.net/projects/moreldap',
			'minGlpiVersion' => PLUGIN_MORELDAP_GLPI_MIN_VERSION);
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_moreldap_check_prerequisites() {
	if (version_compare(GLPI_VERSION, PLUGIN_MORELDAP_GLPI_MIN_VERSION, 'lt') || version_compare(GLPI_VERSION, PLUGIN_MORELDAP_GLPI_MAX_VERSION, 'ge')) {
		echo "This plugin requires GLPI >= " . PLUGIN_MORELDAP_GLPI_MIN_VERSION . " and GLPI < " . PLUGIN_MORELDAP_GLPI_MAX_VERSION;
		return false;
	}
	return true;
}


function plugin_init_moreldap() {
	global $PLUGIN_HOOKS, $CFG_GLPI, $LANG;
	
	$PLUGIN_HOOKS['csrf_compliant']['moreldap'] = true;
	
	$plugin = new Plugin();
	if ($plugin->isInstalled("moreldap") && $plugin->isActivated("moreldap")) {
   	
	   //Add a tab on AuthLDAP items
	   Plugin::registerClass('PluginMoreldapAuthLDAP', array('addtabon' => 'AuthLDAP'));
	   
   	// request more attributes from LDAP
   	$PLUGIN_HOOKS['retrieve_more_field_from_ldap']['moreldap'] = "plugin_retrieve_more_field_from_ldap_moreldap";
   	// Retrieve others datas from LDAP
   	$PLUGIN_HOOKS['retrieve_more_data_from_ldap']['moreldap'] = "plugin_retrieve_more_data_from_ldap_moreldap";

	      // Indicate where the configuration page can be found
      if (Session::haveRight('config', 'w')) {
         $PLUGIN_HOOKS['config_page']['moreldap'] = 'front/authldap.php';
      }
	}
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_moreldap_check_config() {
	return true;
}
