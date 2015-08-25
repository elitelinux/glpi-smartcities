<?php
/*
 * @version $Id: install.php 36 2012-08-31 13:59:28Z dethegeek $
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

/**
 * Create the database tables for the first install of the plugin
 */
function plugin_moreldap_DatabaseInstall()
{
   global $DB;
   
   $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_moreldap_config` (
               `id` int(11) NOT NULL auto_increment,
               `name` varchar(64) UNIQUE NOT NULL default '0',
               `value` varchar(250) NOT NULL default '',
               PRIMARY KEY  (`id`)
            ) ENGINE=MyISAM
            DEFAULT
              CHARSET=utf8
              COLLATE=utf8_unicode_ci";
   $DB->query($query) or die($DB->error());

   $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_moreldap_authldaps` (
               `id` int(11) NOT NULL auto_increment,
               `location` varchar(255) NOT NULL default '',
               `location_enabled` varchar(1) NOT NULL default 'N',
               `entities_id` INT(11) NOT NULL default '0',
               `is_recursive` INT(1) NOT NULL DEFAULT '0',
               PRIMARY KEY  (`id`)
            ) ENGINE=MyISAM
            DEFAULT
              CHARSET=utf8
              COLLATE=utf8_unicode_ci";
   $DB->query($query) or die($DB->error());
   
   $query = "INSERT INTO `glpi_plugin_moreldap_config`
             SET `name`='Version', `value`='" . PLUGIN_MORELDAP_VERSION ."'";
   $DB->query($query) or die($DB->error());
}

function plugin_moreldap_DatabaseUninstall()
{
   global $DB;
   
   $query = "DROP TABLE IF EXISTS `glpi_plugin_moreldap_config`";
   $DB->query($query) or die($DB->error());
    
   $query = "DROP TABLE IF EXISTS `glpi_plugin_moreldap_authldaps`";
   $DB->query($query) or die($DB->error());
    
}