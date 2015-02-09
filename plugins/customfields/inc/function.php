<?php
/*
----------------------------------------------------------------------
GLPI - Gestionnaire Libre de Parc Informatique
Copyright (C) 2003-2009 by the INDEPNET Development Team.

http://indepnet.net/   http://glpi-project.org
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
// Purpose of file: Collection of various functions used by the plugin.
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die('Sorry. You can\'t access this file directly.');
}

// ** DATABASE FUNCTIONS ** //

/**
 * Removes most accents used in European languages
 *
 * @param $str Input string containing accents
 * @return mixed Cleaned string
 */
function plugin_customfields_remove_accents($str)
{
   
   $str  = htmlentities($str, ENT_COMPAT, 'UTF-8');

   $str  = preg_replace(
      '/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil|ring);/',
      '$1',
      $str
   );

   $from = array(
      '&#192;',
      '&#193;',
      '&#194;',
      '&#195;',
      '&#196;',
      '&#197;',
      '&#199;',
      '&#200;',
      '&#201;',
      '&#202;',
      '&#203;',
      '&#204;',
      '&#205;',
      '&#206;',
      '&#207;',
      '&#208;',
      '&#209;',
      '&#210;',
      '&#211;',
      '&#212;',
      '&#213;',
      '&#214;',
      '&#217;',
      '&#218;',
      '&#219;',
      '&#220;',
      '&#221;',
      '&#224;',
      '&#225;',
      '&#226;',
      '&#227;',
      '&#228;',
      '&#229;',
      '&#230;',
      '&#231;',
      '&#232;',
      '&#233;',
      '&#234;',
      '&#235;',
      '&#236;',
      '&#237;',
      '&#238;',
      '&#239;',
      '&#240;',
      '&#241;',
      '&#242;',
      '&#243;',
      '&#244;',
      '&#245;',
      '&#246;',
      '&#249;',
      '&#250;',
      '&#251;',
      '&#252;',
      '&#253;',
      '&#255;',
      '&#256;',
      '&#257;',
      '&#258;',
      '&#259;',
      '&#260;',
      '&#261;',
      '&#262;',
      '&#263;',
      '&#264;',
      '&#265;',
      '&#266;',
      '&#267;',
      '&#268;',
      '&#269;',
      '&#270;',
      '&#271;',
      '&#272;',
      '&#273;',
      '&#274;',
      '&#275;',
      '&#276;',
      '&#277;',
      '&#278;',
      '&#279;',
      '&#280;',
      '&#281;',
      '&#282;',
      '&#283;',
      '&#284;',
      '&#285;',
      '&#286;',
      '&#287;',
      '&#288;',
      '&#289;',
      '&#290;',
      '&#291;',
      '&#292;',
      '&#293;',
      '&#294;',
      '&#295;',
      '&#296;',
      '&#297;',
      '&#298;',
      '&#299;',
      '&#300;',
      '&#301;',
      '&#302;',
      '&#303;',
      '&#304;',
      '&#305;',
      '&#308;',
      '&#309;',
      '&#310;',
      '&#311;',
      '&#312;',
      '&#313;',
      '&#314;',
      '&#315;',
      '&#316;',
      '&#317;',
      '&#318;',
      '&#319;',
      '&#320;',
      '&#321;',
      '&#322;',
      '&#323;',
      '&#324;',
      '&#325;',
      '&#326;',
      '&#327;',
      '&#328;',
      '&#329;',
      '&#330;',
      '&#331;',
      '&#332;',
      '&#333;',
      '&#334;',
      '&#335;',
      '&#336;',
      '&#337;',
      '&#340;',
      '&#341;',
      '&#342;',
      '&#343;',
      '&#344;',
      '&#345;',
      '&#346;',
      '&#347;',
      '&#348;',
      '&#349;',
      '&#350;',
      '&#351;',
      '&#352;',
      '&#353;',
      '&#354;',
      '&#355;',
      '&#356;',
      '&#357;',
      '&#360;',
      '&#361;',
      '&#362;',
      '&#363;',
      '&#364;',
      '&#365;',
      '&#366;',
      '&#367;',
      '&#368;',
      '&#369;',
      '&#370;',
      '&#371;',
      '&#372;',
      '&#373;',
      '&#374;',
      '&#375;',
      '&#376;',
      '&#377;',
      '&#378;',
      '&#379;',
      '&#380;',
      '&#381;',
      '&#382;'
   );

   $to = array(
      'A',
      'A',
      'A',
      'A',
      'A',
      'A',
      'C',
      'E',
      'E',
      'E',
      'E',
      'I',
      'I',
      'I',
      'I',
      'D',
      'N',
      'O',
      'O',
      'O',
      'O',
      'O',
      'U',
      'U',
      'U',
      'U',
      'Y',
      'a',
      'a',
      'a',
      'a',
      'a',
      'a',
      'a',
      'c',
      'e',
      'e',
      'e',
      'e',
      'i',
      'i',
      'i',
      'i',
      'o',
      'n',
      'o',
      'o',
      'o',
      'o',
      'o',
      'u',
      'u',
      'u',
      'u',
      'y',
      'y',
      'A',
      'a',
      'A',
      'a',
      'A',
      'a',
      'C',
      'c',
      'C',
      'c',
      'C',
      'c',
      'C',
      'c',
      'D',
      'd',
      'D',
      'd',
      'E',
      'e',
      'E',
      'e',
      'E',
      'e',
      'E',
      'e',
      'E',
      'e',
      'G',
      'g',
      'G',
      'g',
      'G',
      'g',
      'G',
      'g',
      'G',
      'H',
      'H',
      'h',
      'I',
      'i',
      'I',
      'i',
      'I',
      'i',
      'I',
      'i',
      'I',
      'i',
      'J',
      'j',
      'K',
      'k',
      'k',
      'L',
      'l',
      'L',
      'l',
      'L',
      'l',
      'L',
      'l',
      'L',
      'l',
      'N',
      'n',
      'N',
      'n',
      'N',
      'n',
      'n',
      'N',
      'n',
      'O',
      'o',
      'O',
      'o',
      'O',
      'o',
      'R',
      'r',
      'R',
      'r',
      'R',
      'r',
      'S',
      's',
      'S',
      's',
      'S',
      's',
      'S',
      's',
      'T',
      't',
      'T',
      't',
      'U',
      'u',
      'U',
      'u',
      'U',
      'u',
      'U',
      'u',
      'U',
      'u',
      'U',
      'u',
      'W',
      'w',
      'Y',
      'y',
      'Y',
      'Z',
      'z',
      'Z',
      'z',
      'Z',
      'z',
   );

   return str_replace($from, $to, html_entity_decode($str));

}

/**
 * Replace punctuation and spaces with underscore, letters to lowercase.
 * Removes most accents, but does not replace foreign scripts,
 * chinese characters, etc.
 *
 * @param $str Input string containing illegal characters
 * @return string Cleaned output string
 */

function plugin_customfields_make_system_name($str)
{
   
   $str = plugin_customfields_remove_accents(trim($str));
   return strtr(
      $str,
      ' ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()+={}[]<>,.?/~`|:;"\'\\',
      '_abcdefghijklmnopqrstuvwxyz______________________________'
   );

}

/**
 * Return table name for a specific itemtype
 *
 * @param $itemtype Type of item
 * @return string Name of table
 */

function plugin_customfields_table($itemtype)
{
   return 'glpi_plugin_customfields_' . strtolower(getPlural($itemtype));
}

/**
 * Activate custom fields for a specific device (used if auto activate is
 * turned off)
 *
 * @param $itemtype Type of item
 * @param $ID ID of item
 */

function plugin_customfields_activate($itemtype, $ID)
{
   global $DB;
   
   if (isset($itemtype) && $ID >= 0) {
      if ($table = plugin_customfields_table($itemtype)) {
         $query = "INSERT INTO `$table`
                   (`id`) VALUES ('" . intval($ID) . "')";
         $DB->query($query);
      }
   }
}

/**
 * Activates custom fields for all devices of a specific type
 *
 * @param $itemtype Type of item
 */

function plugin_customfields_activate_all($itemtype)
{
   global $DB;

   // Only if there are fields

   $query  = "SELECT `id`
             FROM `glpi_plugin_customfields_fields`
             WHERE `itemtype` = '$itemtype'";
   $result = $DB->query($query);
   
   if ($DB->numrows($result) > 0) {

      // Create data table for the itemtype

      plugin_customfields_create_data_table($itemtype);

      // Item table

      $table1 = getTableForItemType($itemtype);

      // Customfields data table

      $table2 = plugin_customfields_table($itemtype);

      if ($itemtype == 'Entity') {

         // Add a row for the Root Entity

         $sql = "INSERT INTO `$table2` (`id`) VALUES ('0')";
         $DB->query($sql);

      }

      // Add empty data for all existing objects
      
      $query  = "SELECT a.`id`, b.`id` AS skip
                FROM $table1 AS a
                LEFT JOIN $table2 AS b
                     ON a.`id` = b.`id`";
      $result = $DB->query($query);
      
      while ($data = $DB->fetch_assoc($result)) {

         if (is_null($data['skip'])) {
            $sql     = "INSERT INTO `$table2`
                           (`id`)
                    VALUES ('" . intval($data['id']) . "')";
            $DB->query($sql);
         }

      }

   }

}

/**
 * Activate all items of all types in the database
 */

function plugin_customfields_activate_all_types()
{
   global $DB;
   
   $sql    = "SELECT `itemtype`
           FROM `glpi_plugin_customfields_itemtypes`
           WHERE `enabled` = 1";
   $result = $DB->query($sql);
   
   while ($data = $DB->fetch_array($result)) {
      plugin_customfields_activate_all($data['itemtype']);
   }

}

/**
 * Create a table to store custom data for a device type if it doesn't
 * already exist
 *
 * @param $itemtype Type of item
 * @return bool Success
 */

function plugin_customfields_create_data_table($itemtype)
{
   global $DB;
   
   $table = plugin_customfields_table($itemtype);
   
   if (!TableExists($table)) {

      $sql = "CREATE TABLE `$table` (
               `id` int(11) NOT NULL default '0',
               PRIMARY KEY (`id`)
              )ENGINE=MyISAM
              DEFAULT
                CHARSET=utf8
                COLLATE=utf8_unicode_ci
                AUTO_INCREMENT=3";

      $result = $DB->query($sql);
      return ($result ? true : false);

   }

   return true;

}

/**
 * Disable custom fields for a specific itemtype
 *
 * @param $itemtype Type of item
 */

function plugin_customfields_disable_device($itemtype)
{
   global $DB, $ACTIVE_CUSTOMFIELDS_TYPES, $LANG;
   
   unset($ACTIVE_CUSTOMFIELDS_TYPES[$itemtype]);
   $query  = "UPDATE `glpi_plugin_customfields_itemtypes`
             SET `enabled` = 0
             WHERE `itemtype` = '$itemtype'";
   $result = $DB->query($query);

   Session::addMessageAfterRedirect(
      __('Customfields disabled for this device type','customfields')
   );

}

// ** DISPLAY FUNCTIONS ** //

/**
 * Show a static representation of the custom field value (used for readonly
 * fields)
 *
 * @param $value The falue to display
 * @param string $size Additional style hints
 */

function plugin_customfields_showValue($value, $size = '')
{
   if ($size != '') {
      echo '<div style="text-align:left;overflow:auto;border:1px solid #999;'
         . $size
         . '">';
   }
   
   if ($value != '' && $value != '&nbsp;') {
      echo $value;
   } else {
      echo '-';
   }
   
   if ($size != '') {
      echo '</div>';
   }
}
