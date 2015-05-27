<?php
/*
 * @version $Id: plugin.class.php 14498 2011-05-20 13:39:24Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

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

// Based on cacti plugin system
// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMobilePlugin extends Plugin {

 
   /**
    * Display plugin headgsin for a device type
    *
    * @param $target page to link
    * @param $item object
    * @param $withtemplate is the item display like a template ?
    *
    * @return Array of tabs (sorted)
    */
   static function getTabs($target, CommonGLPI $item, $withtemplate) {
      global $PLUGIN_HOOKS;

      $template = "";
      if (!empty($withtemplate)) {
         $template = "&withtemplate=$withtemplate";
      }
      $display_onglets = array();
      $tabpage = $item->getTabsURL();
      $active  = false;
      $tabid   = 0;
      $tabs    = array();
      $order   = array();
      if (isset($PLUGIN_HOOKS["headings"]) && is_array($PLUGIN_HOOKS["headings"])) {
         foreach ($PLUGIN_HOOKS["headings"] as $plug => $function) {
            if (file_exists(GLPI_ROOT . "/plugins/$plug/hook.php")) {
               include_once(GLPI_ROOT . "/plugins/$plug/hook.php");
            }
            if (is_callable($function)) {
               $onglet = call_user_func($function, $item, $withtemplate);
               if (is_array($onglet) && count($onglet)) {
                  foreach ($onglet as $key => $val) {
                     $key = $plug."_".$key;
                     $params = "target=$target&itemtype=".get_class($item)."&glpi_tab=$key";
                     if ($item instanceof CommonDBTM) {
                        $params .= "&id=".$item->getField('id')."$template";
                     }
                     $tabs[$key] = array('title'  => $val,
                                         'url'    => $tabpage,
                                         'params' => $params);
                     $order[$key] = $val;
                  }
               }
            }
         }
         // Order plugin tab
         if (count($tabs)) {
            asort($order);
            foreach ($order as $key => $val) {
               $order[$key] = $tabs[$key];
            }
         }
      }
      return $order;
   }

}

?>
