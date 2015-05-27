<?php
/*
 * @version $Id: popup.php 12360 2010-09-09 13:20:42Z walid $
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

define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

Session::checkLoginUser();

if (!isset($_GET["itemtype"])) exit();
else $itemtype = $_GET["itemtype"];

if (!isset($_GET["type"])) exit();
else $type = $_GET["type"];


$common = new PluginMobileCommon();

$common->displayHeader($LANG['common'][12], "search.php?itemtype=$itemtype"
   ."&amp;menu=".$_SESSION['plugin_mobile']['menu']
   ."&amp;ssmenu=".$_SESSION['plugin_mobile']['ssmenu']
   ."&amp;start=".$_SESSION['plugin_mobile']['start']
   ."&amp;rand=".mt_rand()
);

$searchconfig = new PluginMobileSearchconfig;
$searchconfig->showSearchConfigList($itemtype, $type);

$common->displayFooter();

?>
