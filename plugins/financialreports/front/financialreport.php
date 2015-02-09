<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Financialreports plugin for GLPI
 Copyright (C) 2003-2011 by the Financialreports Development Team.

 https://forge.indepnet.net/projects/financialreports
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Financialreports.

 Financialreports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Financialreports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Financialreports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

Html::header(PluginFinancialreportsFinancialreport::getTypeName(),'',"utils","report");

Session::checkCentralAccess();

//First time this screen is displayed : set the pc mode to 'all'
if (!isset($_SESSION["displaypc"])) {
   $_SESSION["displaypc"] = false;
}
//Changing the pc mode
if (isset($_GET["displaypc"])) {
   if ($_GET["displaypc"] == "false") {
      $_SESSION["displaypc"]=false;
   } else {
      $_SESSION["displaypc"]=true;
   }
}
//First time this screen is displayed : set the notebook mode to 'all'
if (!isset($_SESSION["displaynotebook"])) {
   $_SESSION["displaynotebook"] = false;
}
//Changing the notebook mode
if (isset($_GET["displaynotebook"])) {
   if ($_GET["displaynotebook"] == "false") {
      $_SESSION["displaynotebook"]=false;
   } else {
      $_SESSION["displaynotebook"]=true;
   }
}
//First time this screen is displayed : set the server mode to 'all'
if (!isset($_SESSION["displayserver"])) {
   $_SESSION["displayserver"] = false;
}
//Changing the server mode
if (isset($_GET["displayserver"])) {
   if ($_GET["displayserver"] == "false") {
      $_SESSION["displayserver"]=false;
   } else {
      $_SESSION["displayserver"]=true;
   }
}
//First time this screen is displayed : set the monitor mode to 'all'
if (!isset($_SESSION["displaymonitor"])) {
   $_SESSION["displaymonitor"] = false;
}
//Changing the monitor mode
if (isset($_GET["displaymonitor"])) {
   if ($_GET["displaymonitor"] == "false") {
      $_SESSION["displaymonitor"]=false;
   } else {
      $_SESSION["displaymonitor"]=true;
   }
}
//First time this screen is displayed : set the printer mode to 'all'
if (!isset($_SESSION["displayprinter"])) {
   $_SESSION["displayprinter"] = false;
}
//Changing the printer mode
if (isset($_GET["displayprinter"])) {
   if ($_GET["displayprinter"] == "false") {
      $_SESSION["displayprinter"]=false;
   } else {
      $_SESSION["displayprinter"]=true;
   }
}
//First time this screen is displayed : set the networking mode to 'all'
if (!isset($_SESSION["displaynetworking"])) {
   $_SESSION["displaynetworking"] = false;
}
//Changing the networking mode
if (isset($_GET["displaynetworking"])) {
   if ($_GET["displaynetworking"] == "false") {
      $_SESSION["displaynetworking"]=false;
   } else {
      $_SESSION["displaynetworking"]=true;
   }
}
//First time this screen is displayed : set the peripheral mode to 'all'
if (!isset($_SESSION["displayperipheral"])) {
   $_SESSION["displayperipheral"] = false;
}
//Changing the peripheral mode
if (isset($_GET["displayperipheral"])) {
   if ($_GET["displayperipheral"] == "false") {
      $_SESSION["displayperipheral"]=false;
   } else {
      $_SESSION["displayperipheral"]=true;
   }
}
//First time this screen is displayed : set the phone mode to 'all'
if (!isset($_SESSION["displayphone"])) {
   $_SESSION["displayphone"] = false;
}
//Changing the phone mode
if (isset($_GET["displayphone"])) {
   if ($_GET["displayphone"] == "false") {
      $_SESSION["displayphone"]=false;
   } else {
      $_SESSION["displayphone"]=true;
   }
}
//First time this screen is displayed : set the rebus mode to 'all'
if (!isset($_SESSION["displaydisposal"])) {
   $_SESSION["displaydisposal"] = false;
}
//Changing the rebus mode
if (isset($_GET["displaydisposal"])) {
   if ($_GET["displaydisposal"] == "false") {
      $_SESSION["displaydisposal"]=false;
   } else {
      $_SESSION["displaydisposal"]=true;
   }
}

$disposal=new PluginFinancialreportsDisposalItem();
$report= new PluginFinancialreportsFinancialreport();

if (isset($_POST["add_date"])) {

   $disposal->add($_POST);
   Html::back();

} else if (isset($_POST["delete_date"])) {

   $disposal->delete($_POST);
   Html::back();

} else if ($report->canView() || Session::haveRight("config",UPDATE)) {
   
   Report::title();

   if(empty($_GET["date"])) $_GET["date"] = date("Y-m-d");
   if(empty($_GET["locations_id"])) $_GET["locations_id"] = 0;
   if(!isset($_POST["date"])) $_POST["date"]= $_GET["date"];
   if(!isset($_POST["locations_id"])) $_POST["locations_id"]= $_GET["locations_id"];

   echo "<div align='center'><form action=\"./financialreport.php\" method=\"post\">";
   echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2'>";
   echo "<td class='right'>".__('Report date', 'financialreports')." :</td>";
   echo "<td>";
   Html::showDateFormItem("date",$_POST["date"],true,true);
   echo "</td>";
   echo "<td width='60%'>";
   Dropdown::show('Location', array('name' => "locations_id",
                                    'value' => $_POST["locations_id"],
                                     'entity' => $_SESSION["glpiactive_entity"]));
   echo "</td>";
   echo "<td rowspan='2' class='center'>";
   echo "<input type=\"submit\" class='submit' name=\"choice_date\" value='". _sx('button', 'Post')."' />";
   echo "</td></tr>";
   echo "</table>";
   Html::closeForm();
   echo "</div>";
   echo "<div align='center'>";
   $display = array('displaypc' => $_SESSION["displaypc"],
                     'displaynotebook' => $_SESSION["displaynotebook"],
                     'displayserver' => $_SESSION["displayserver"],
                     'displaymonitor' => $_SESSION["displaymonitor"],
                     'displayprinter' => $_SESSION["displayprinter"],
                     'displaynetworking' => $_SESSION["displaynetworking"],
                     'displayperipheral' => $_SESSION["displayperipheral"],
                     'displayphone' => $_SESSION["displayphone"],
                     'displaydisposal' => $_SESSION["displaydisposal"]);

   $report->displayReport($_POST,$display);
   echo "</div>";

} else {
   Html::displayRightError();
}

Html::footer();

?>