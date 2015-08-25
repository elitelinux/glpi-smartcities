<?php
/*
 * @version $Id$
 LICENSE

  This file is part of the purgelogs plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with purgelogs. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   purgelogs
 @author    the purgelogs plugin team
 @copyright Copyright (c) 2010-2011 purgelogs plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/purgelogs
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

// Init the hooks of the plugins -Needed
function plugin_init_purgelogs() {
   global $PLUGIN_HOOKS,$CFG_GLPI;
   $PLUGIN_HOOKS['csrf_compliant']['purgelogs'] = true;
   
   $plugin = new Plugin();
   if ($plugin->isInstalled('purgelogs') && $plugin->isActivated('purgelogs')) {
       
      //if glpi is loaded
      if (Session::getLoginUserID() && Session::haveRight("config", UPDATE)) {
         $PLUGIN_HOOKS['config_page']['purgelogs'] = 'front/config.form.php';
      }
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_purgelogs() {
   return array ('name'           => __("Purge history", "purgelogs"),
                 'version'        => '0.85+1.1',
                 'author'         => "<a href='www.teclib.com'>TECLIB'</a>",
                 'homepage'       => 'https://forge.indepnet.net/projects/show/purgelogs',
                 'minGlpiVersion' => '0.85');
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_purgelogs_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.85','lt') || version_compare(GLPI_VERSION,'0.91','ge')) {
      echo "This plugin requires GLPI >= 0.85 and GLPI < 0.90";
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_purgelogs_check_config() {
   return true;
}
