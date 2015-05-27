<?php
/*
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE
Inventaire
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

// Entry menu case
define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

if (!isset($_GET['date'])) $_GET['date'] = strftime("%Y-%m-%d");
if (!isset($_GET['type'])) $_GET['type'] = "week";
if (!isset($_GET['usertype'])) $_GET['usertype'] = "user";
if (!isset($_GET['uID'])) $_GET['uID'] = $SESSION['glpiID'];//getLoginUserID();
if (!isset($_GET['gID'])) $_GET['gID'] = 0;

switch ($_GET["usertype"]) {
   case "user" :
      $_GET['gID'] = -1;
      break;
   case "group" :
      $_GET['uID'] = -1;
      break;
   case "user_group" :
      $_GET['gID'] = "mine";
      break;
}

//$welcome = $LANG['Menu'][29];

$welcome = __('Planning');

$common = new PluginMobileCommon;
$common->displayHeader($welcome, 'ss_menu.php?menu=maintain');

PluginMobilePlanning::showSelectionForm($_GET['date'], $_GET['type'], $_GET['usertype'], $_GET['uID'], $_GET['gID']);
PluginMobilePlanning::show($_GET['date'],$_GET['type'], $_GET['uID'],$_GET['gID']);

$common->displayFooter();
?>
