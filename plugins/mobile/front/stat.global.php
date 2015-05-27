<?php
/*
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

define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

$common = new PluginMobileCommon;
$common->displayHeader($LANG['Menu'][13]." ".strtolower($LANG['stats'][1]), 'stat.php');

Session::checkRight("statistic","1");

if (empty($_POST["date1"]) && empty($_POST["date2"])) {
   $year = date("Y")-1;
   $_POST["date1"] = date("Y-m-d",mktime(1,0,0,date("m"),date("d"),$year));
   $_POST["date2"] = date("Y-m-d");
}

if (!empty($_POST["date1"])
    && !empty($_POST["date2"])
    && strcmp($_POST["date2"],$_POST["date1"]) < 0) {

   $tmp = $_POST["date1"];
   $_POST["date1"] = $_POST["date2"];
   $_POST["date2"] = $tmp;
}
echo "<div data-role='content'>";
 
//echo $_POST["date1"]."<br>";
//echo $_POST["date2"];
$itemtype = "Ticket";

PluginMobileStat::showDateSelector("stat.global.php");

///////// Stats nombre intervention
// Total des interventions
$entrees_total = PluginMobileStat::constructEntryValues($itemtype,"inter_total",$_POST["date1"],$_POST["date2"]);
// Total des interventions résolues
$entrees_solved = PluginMobileStat::constructEntryValues($itemtype,"inter_solved",$_POST["date1"],$_POST["date2"]);
//Temps moyen de resolution d'intervention
$entrees_avgsolvedtime = PluginMobileStat::constructEntryValues($itemtype,"inter_avgsolvedtime",$_POST["date1"],$_POST["date2"]);
//Temps moyen d'intervention reel
$entrees_avgrealtime = PluginMobileStat::constructEntryValues($itemtype,"inter_avgrealtime",$_POST["date1"],$_POST["date2"]);
//Temps moyen de prise en compte de l'intervention
$entrees_avgtaketime = PluginMobileStat::constructEntryValues($itemtype,"inter_avgtakeaccount",$_POST["date1"],$_POST["date2"]);

PluginMobileStat::showGraph(array($LANG['stats'][5]=>$entrees_total)
               ,array('title'=>$LANG['stats'][5],
                     'showtotal' => 1,
                     'unit'      => $LANG['stats'][35]));
PluginMobileStat::showGraph(array($LANG['stats'][11]=>$entrees_solved)
               ,array('title'    => $LANG['stats'][11],
                     'showtotal' => 1,
                     'unit'      => $LANG['stats'][35]));
PluginMobileStat::showGraph(array($LANG['stats'][6]=>$entrees_avgsolvedtime)
               ,array('title' => $LANG['stats'][6],
                     'unit'   => $LANG['job'][21]));
PluginMobileStat::showGraph(array($LANG['stats'][25]=>$entrees_avgrealtime)
               ,array('title' => $LANG['stats'][25],
                     'unit'   => $LANG['job'][21]));
PluginMobileStat::showGraph(array($LANG['stats'][30]=>$entrees_avgtaketime)
               ,array('title' => $LANG['stats'][30],
                     'unit'   => $LANG['job'][21]));
echo "</div>";
$common->displayFooter();

?>
