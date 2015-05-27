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

$welcome = PluginMobileTab::getTitle($_REQUEST['glpi_tab'], $_REQUEST['itemtype'], $_REQUEST['id']);

$back = "item.php?itemtype=".$_GET['itemtype']
   ."&menu=".$_GET['menu']
   ."&ssmenu=".$_GET['ssmenu']
   ."computer&id=".$_GET['id'];

$common = new PluginMobileCommon;
$common->displayHeader($welcome, $back);

PluginMobileTab::displayTabBar();

//Stevenes Donato

$glpi_tab = $_REQUEST['glpi_tab'];
$itemtype = $_REQUEST['itemtype'];

//echo $glpi_tab."tab";

// Followup / acompanhamento

if($_REQUEST['glpi_tab'] == "TicketFollowup$1" ) {
global $DB;

//add followup	  
echo '<form action="followup.php?id='.$_REQUEST['id'].'" method="post">
<input type="submit" name="follow" value="'.$LANG['plugin_mobile']['common'][7] .'">';
Html::closeForm() ;
 
	
$sql_fup = "SELECT * 
FROM glpi_ticketfollowups
WHERE tickets_id = " . $_REQUEST['id'] ."
ORDER BY glpi_ticketfollowups.date DESC";

$result_fup = $DB->query($sql_fup) ; 

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_fup)) {
	
$sql_autor =
"SELECT DISTINCT glpi_users.firstname AS name, glpi_users.realname AS sname
FROM glpi_ticketfollowups, glpi_users, glpi_tickets
WHERE glpi_ticketfollowups.users_id = glpi_users.id
AND glpi_ticketfollowups.users_id = ".$row['users_id']."
AND glpi_ticketfollowups.tickets_id = glpi_tickets.id";


$result_autor = $DB->query($sql_autor);	
$row_autor = $DB->fetch_assoc($result_autor);

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['common'][27].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['date'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['common'][37].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'. $row_autor['name'] ." ".$row_autor['sname'] .'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][6].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['content'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"></li>';
echo '<li class="ui-li ui-li-static ui-body-c"></li>';
}
echo '</ul>';
}

//  Tasks


if($_REQUEST['glpi_tab'] == "TicketTask$1" ) {

global $DB;

$sql_task = "SELECT * 
FROM `glpi_tickettasks` 
WHERE `tickets_id` = " . $_REQUEST['id'] ."";

$result_task = $DB->query($sql_task) ; 

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_task)) {

//tarefa	

	$sql_cat = "SELECT completename FROM glpi_taskcategories WHERE id=".$row['taskcategories_id']."";		
	$result_cat = $DB->query($sql_cat);	
   $row_cat = $DB->fetch_assoc($result_cat);

//autor

/*	$sql_autor = "SELECT distinct glpi_users.firstname AS name, glpi_users.realname AS sname
FROM `glpi_ticketfollowups` , glpi_users, glpi_tickets
WHERE  glpi_ticketfollowups.`users_id` = ".$row['users_id']."
AND glpi_ticketfollowups.`users_id` = glpi_users.id";
*/

$sql_autor =
"SELECT DISTINCT glpi_users.firstname AS name, glpi_users.realname AS sname
FROM glpi_ticketfollowups, glpi_users, glpi_tickets
WHERE glpi_ticketfollowups.users_id = glpi_users.id
AND glpi_ticketfollowups.users_id = ".$row['users_id']."
AND glpi_ticketfollowups.tickets_id = glpi_tickets.id";

$result_autor = $DB->query($sql_autor);	
$row_autor = $DB->fetch_assoc($result_autor);

// executor
/*
	$sql_tec = "SELECT distinct glpi_users.firstname AS name, glpi_users.realname AS sname
FROM `glpi_ticketfollowups` , glpi_users, glpi_tickets
WHERE  glpi_ticketfollowups.`users_id` = ".$row['users_id_tech']."
AND glpi_ticketfollowups.`users_id` = glpi_users.id";
*/

$sql_tec =
"SELECT DISTINCT glpi_users.firstname AS name, glpi_users.realname AS sname
FROM glpi_ticketfollowups, glpi_users, glpi_tickets
WHERE glpi_ticketfollowups.users_id = glpi_users.id
AND glpi_ticketfollowups.users_id = ".$row['users_id_tech']."
AND glpi_ticketfollowups.tickets_id = glpi_tickets.id";

$result_tec = $DB->query($sql_tec);	
$row_tec = $DB->fetch_assoc($result_tec);


echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['job'][7].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'. $row_cat['completename'] .'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['common'][27].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['date'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['common'][37].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'. $row_autor['name'] ." ".$row_autor['sname'] .'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][6].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['content'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['financial'][43].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'. $row_tec['name'] ." ".$row_tec['sname'] .'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['buttons'][33].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['begin'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> Término: </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['end'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"></li>';
echo '<li class="ui-li ui-li-static ui-body-c"></li>';
	}
echo '</ul>';
	
}	


//solucao

if($_REQUEST['glpi_tab'] == "Ticket$2" &&  $_REQUEST['itemtype'] == "Ticket") {

global $DB;

$sql_sol =
"SELECT solution
FROM glpi_tickets
WHERE id = ".$_REQUEST['id']." ";

$result_sol = $DB->query($sql_sol);	
$sol = $DB->result($result_sol,0,'solution');
	

if($sol == '') {
	
	//add solution	  
	echo '<form action="solution.php?id='.$_REQUEST['id'].'" method="post">
	<input type="submit" name="solution" value="'. __('Solution') .'">';
	Html::closeForm() ;
}


$sql_sol = "SELECT closedate, `solvedate` , `solution` , `users_id_lastupdater`
FROM `glpi_tickets`
WHERE id = ".$_REQUEST['id']."";

$result_sol = $DB->query($sql_sol);	

while($row = $DB->fetch_assoc($result_sol)) {

if($row['solvedate'] != "")

  {

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';
	
//while($row = $DB->fetch_assoc($result_sol)) {	
	
echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['plugin_mobile']['common'][9].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['solvedate'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['plugin_mobile']['common'][10].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['closedate'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][6].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['solution'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"></li>';
echo '<li class="ui-li ui-li-static ui-body-c"></li>';
		
	}	
	echo '</ul>';
}
//}
}	


//Documents

if($_REQUEST['glpi_tab'] == "Document_Item$1") {

global $DB;

$sql_docid = "SELECT `documents_id` AS docs_id
FROM `glpi_documents_items` 
WHERE `items_id` = ".$_REQUEST['id']."
AND `itemtype` = '".$_REQUEST['itemtype']."'
";
		
$result_docid = $DB->query($sql_docid);

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row_id = $DB->fetch_assoc($result_docid)) {
	
	$sql_doc = "SELECT id, name, filename, mime, date_mod
FROM `glpi_documents`
WHERE `id` = ". $row_id['docs_id'] ."
AND is_deleted = 0 
ORDER BY date_mod DESC";

$result_doc = $DB->query($sql_doc) ; 
$row = $DB->fetch_assoc($result_doc);
	
echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['common'][43].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].' ('.$row['id'].')</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['document'][2].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c"><a href='.$CFG_GLPI["root_doc"].'/front/document.send.php?docid='.$row['id'].'&tickets_id='
. $_REQUEST['id'] .' target=_blank>'. $row['filename'] .' </a></li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['document'][4].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['mime'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['common'][27].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['date_mod'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"></li>';
echo '<li class="ui-li ui-li-static ui-body-c"></li>';
}
echo '</ul>';
}


//computador

//componentes

if($_REQUEST['glpi_tab'] == "Item_Devices$1" ) {

global $DB;	
	
$sql_proc = "SELECT glpi_items_deviceprocessors.id, items_id, deviceprocessors_id, 
glpi_items_deviceprocessors.frequency AS freq,  glpi_deviceprocessors.designation AS name
FROM glpi_items_deviceprocessors
LEFT JOIN glpi_deviceprocessors ON ( glpi_deviceprocessors.id =  glpi_items_deviceprocessors.deviceprocessors_id )
WHERE items_id = ".$_REQUEST['id']."
AND is_deleted = 0";

$result_proc = $DB->query($sql_proc) ; 

echo '

<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_proc)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['devices'][4].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['device_ram'][1].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'. $row['freq'] .'</li>';
}
echo '</ul>';

// memoria

$sql_mem = "
SELECT glpi_items_devicememories.id, items_id, devicememories_id, glpi_devicememories.frequence AS freq, glpi_devicememories.designation AS name, glpi_items_devicememories.size
FROM glpi_items_devicememories
LEFT JOIN glpi_devicememories ON ( glpi_devicememories.id = glpi_items_devicememories.devicememories_id )
WHERE items_id = ".$_REQUEST['id']."
AND is_deleted =0";

$result_mem = $DB->query($sql_mem) ; 

echo '

<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_mem)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['devices'][6].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['device_ram'][1].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'. $row['freq'] .'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['device_ram'][2].' (MB): </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'. $row['size'] .'</li>';
}
echo '</ul>';


// HD

$sql_hd = "
SELECT glpi_items_deviceharddrives.id, items_id, deviceharddrives_id, glpi_deviceharddrives.capacity_default, glpi_deviceharddrives.designation AS name
FROM glpi_items_deviceharddrives
LEFT JOIN glpi_deviceharddrives ON ( glpi_deviceharddrives.id = glpi_items_deviceharddrives.deviceharddrives_id )
WHERE items_id = ".$_REQUEST['id']."
AND is_deleted =0";

$result_hd = $DB->query($sql_hd) ; 

echo '

<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_hd)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['devices'][1].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['device_hdd'][4].' (MB): </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'. $row['capacity_default'] .'</li>';
}
echo '</ul>';



// rede

$sql_net = "SELECT glpi_items_devicenetworkcards.id, items_id, devicenetworkcards_id, 
glpi_items_devicenetworkcards.mac AS mac,  glpi_devicenetworkcards.designation AS name,  glpi_devicenetworkcards.bandwidth
FROM glpi_items_devicenetworkcards
LEFT JOIN glpi_devicenetworkcards ON ( glpi_devicenetworkcards.id =  glpi_items_devicenetworkcards.devicenetworkcards_id )
WHERE items_id = ".$_REQUEST['id']."
AND is_deleted = 0";

$result_net = $DB->query($sql_net);	


echo '<ul class="ui-listview" data-theme="c" data-role="listview">';
	
while($row = $DB->fetch_assoc($result_net)) {	
	
echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> Drive: </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

	}		
	echo '</ul>';
	
	

//drives

$sql_drive = "
SELECT glpi_items_devicedrives.id, items_id, devicedrives_id, glpi_devicedrives.designation AS name
FROM glpi_items_devicedrives
LEFT JOIN glpi_devicedrives ON ( glpi_devicedrives.id = glpi_items_devicedrives.devicedrives_id )
WHERE items_id = ".$_REQUEST['id']."
AND is_deleted =0";

$result_drive = $DB->query($sql_drive) ; 

echo '

<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_drive)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">Drive:</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

}
echo '</ul>';


//video

$sql_video = "
SELECT glpi_items_devicegraphiccards.id, items_id, devicegraphiccards_id, glpi_items_devicegraphiccards.memory, glpi_devicegraphiccards.designation AS name
FROM glpi_items_devicegraphiccards
LEFT JOIN glpi_devicegraphiccards ON ( glpi_devicegraphiccards.id = glpi_items_devicegraphiccards.devicegraphiccards_id )
WHERE items_id = ".$_REQUEST['id']."
AND is_deleted =0";

$result_video = $DB->query($sql_video) ; 

echo '

<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_video)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['devices'][2].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['devices'][6].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['memory'].'</li>';
}
echo '</ul>';


//som

$sql_som = "
SELECT glpi_items_devicesoundcards.id, items_id, devicesoundcards_id, glpi_devicesoundcards.designation AS name
FROM glpi_items_devicesoundcards
LEFT JOIN glpi_devicesoundcards ON ( glpi_devicesoundcards.id = glpi_items_devicesoundcards.devicesoundcards_id )
WHERE items_id = ".$_REQUEST['id']."
AND is_deleted =0";

$result_som = $DB->query($sql_som) ; 

echo '

<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_som)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['devices'][7].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

}
echo '</ul>';


//outros

$sql_out = "
SELECT glpi_items_devicepcis.id, items_id, devicepcis_id, glpi_devicepcis.designation AS name
FROM glpi_items_devicepcis
LEFT JOIN glpi_devicepcis ON ( glpi_devicepcis.id = glpi_items_devicepcis.devicepcis_id )
WHERE items_id = ".$_REQUEST['id']."
AND is_deleted =0";

$result_out = $DB->query($sql_out) ; 

echo '

<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_out)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['devices'][27].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

}
echo '</ul>';

	}
//fim componentes


// disks

if($_REQUEST['glpi_tab'] == "ComputerDisk$1" ) {
	
global $DB;
	
$sql_disk = "SELECT `glpi_filesystems`.`name` AS fsname, `glpi_computerdisks`.*
                FROM `glpi_computerdisks`
                LEFT JOIN `glpi_filesystems`
                          ON (`glpi_computerdisks`.`filesystems_id` = `glpi_filesystems`.`id`)
                WHERE `computers_id` = ".$_REQUEST['id']."
                      AND `is_deleted` = '0'";

$result_disk = $DB->query($sql_disk) ; 

echo '

<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_disk)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['common'][16].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['computers'][5].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'. $row['mountpoint'] .'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['computers'][4].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['fsname'].'</li>';

$total = $row['totalsize'] / 1000;
echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['computers'][3].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$total.' GB</li>';

$livre = $row['freesize'] / 1000;
echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['computers'][2].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$livre.' GB</li>';


if ($row['totalsize'] > 0) {$percent = round(100*$row['freesize']/$row['totalsize']);}               

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['computers'][1].': </li>';

echo '<li class="ui-li ui-li-static ui-body-c">'.Html::displayProgressBar('100', $percent, array('simple' => true, 'forcepadding' => false)).'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"></li>';
echo '<li class="ui-li ui-li-static ui-body-c"></li>';
}

echo '</ul>';
}

// conexoes computador

if($_REQUEST['glpi_tab'] == "Computer_Item$1" &&  $_REQUEST['itemtype'] == "Computer") {
	
global $DB;	
	
$sql_type = "SELECT DISTINCT itemtype
FROM `glpi_computers_items`
WHERE `computers_id` = ".$_REQUEST['id']."";	

$result_type = $DB->query($sql_type) ; 

while($row_type = $DB->fetch_assoc($result_type)) {
	
$itemtype = $row_type['itemtype'];		
$tabletype = "glpi_".strtolower($row_type['itemtype'])."s";
	
$sql_item = "SELECT `glpi_computers_items`.`id` as assoc_id,
                      `glpi_computers_items`.`computers_id` as assoc_computers_id,
                      `glpi_computers_items`.`itemtype` as assoc_itemtype,
                      `glpi_computers_items`.`items_id` as assoc_items_id,
                      `glpi_computers_items`.`is_dynamic` as assoc_is_dynamic,
                      ".$tabletype.".*
                      FROM `glpi_computers_items`
                      LEFT JOIN `".$tabletype."`
                        ON (`".$tabletype."`.`id`
                              = `glpi_computers_items`.`items_id`)
                      WHERE `computers_id` = '".$_REQUEST['id']."'
                            AND `itemtype` = '".$itemtype."'
                            AND `glpi_computers_items`.`is_deleted` = '0'";

$result_item = $DB->query($sql_item) ; 

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_item)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['common'][16].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['assoc_itemtype'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['common'][16].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'. $row['name'] .'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['common'][19].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['serial'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['common'][20].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['otherserial'].' </li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"></li>';
echo '<li class="ui-li ui-li-static ui-body-c"></li>';
}

echo '</ul>';
}
}


//softwares

if($_REQUEST['glpi_tab'] == "Computer_SoftwareVersion$1" &&  $_REQUEST['itemtype'] == "Computer") {
	
global $DB;

$sql_soft = "SELECT glpi_computers_softwareversions.softwareversions_id AS soft_id
FROM `glpi_computers_softwareversions` 
LEFT JOIN `glpi_softwareversions` ON ( `glpi_softwareversions`.id = glpi_computers_softwareversions.id)
WHERE `computers_id` = ".$_REQUEST['id']."
AND glpi_computers_softwareversions.is_deleted = 0
AND glpi_computers_softwareversions.is_deleted_computer = 0";

$result_soft = $DB->query($sql_soft);	

while($row_soft = $DB->fetch_assoc($result_soft)) {	


$sql_soft2 = "SELECT  glpi_softwares.name AS name,  glpi_softwareversions.name AS version
FROM `glpi_softwareversions` 
LEFT JOIN `glpi_softwares` ON ( `glpi_softwareversions`.softwares_id = glpi_softwares.id)
WHERE glpi_softwareversions.`id` = " .$row_soft['soft_id']. " ";

$result_soft2 = $DB->query($sql_soft2);	

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';
	
while($row = $DB->fetch_assoc($result_soft2)) {	
	
echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['common'][16].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['rulesengine'][78].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['version'].'</li>';
	
	}	
	echo '</ul>';

}	
}


//network

if($_REQUEST['glpi_tab'] == "NetworkPort$1" ) {
	
	global $DB;

$sql_net = "SELECT glpi_items_devicenetworkcards.id, items_id, devicenetworkcards_id, 
glpi_items_devicenetworkcards.mac AS mac,  glpi_devicenetworkcards.designation AS name,  glpi_devicenetworkcards.bandwidth
FROM glpi_items_devicenetworkcards
LEFT JOIN glpi_devicenetworkcards ON ( glpi_devicenetworkcards.id =  glpi_items_devicenetworkcards.devicenetworkcards_id )
WHERE items_id = ".$_REQUEST['id']."
AND is_deleted = 0";

$result_net = $DB->query($sql_net);	


echo '<ul class="ui-listview" data-theme="c" data-role="listview">';
	
while($row = $DB->fetch_assoc($result_net)) {	
	
echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['common'][16].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['device_iface'][2].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['mac'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['device_drive'][1].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['bandwidth'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"></li>';
echo '<li class="ui-li ui-li-static ui-body-c"></li>';
	
	
	}	
	
	echo '</ul>';
}


//chamados computador

if($_REQUEST['glpi_tab'] == "Ticket$1" &&  $_REQUEST['itemtype'] == "Computer") {
	
global $DB;

$sql_tcomp = "
SELECT id, name, date, solvedate, users_id_recipient, status , solution
FROM `glpi_tickets`
WHERE items_id = ".$_REQUEST['id']."
AND itemtype = '".$_REQUEST['itemtype']."'
AND is_deleted = 0
ORDER BY `glpi_tickets`.`id` DESC";

$result_tcomp = $DB->query($sql_tcomp) ; 

echo '

<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_tcomp)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">ID:</li>';
echo '<li class="ui-li ui-li-static ui-body-c"><a href="item.php?itemtype=Ticket&menu=maintain&ssmenu=ticket&id='.$row['id'].'" target=_blank>'.$row['id'].'</a></li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['joblist'][6].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['state'][0].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.Ticket::getStatus($row['status']).'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][11].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['date'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][12].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['solvedate'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> </li>';
echo '<li class="ui-li ui-li-static ui-body-c"> </li>';
}
echo '</ul>';

	}


//softwares versions

if($_REQUEST['glpi_tab'] == "SoftwareVersion$1" &&  $_REQUEST['itemtype'] == "Software") {
	
	global $DB;

$sql = "SELECT id, name
FROM `glpi_softwareversions`
WHERE `softwares_id` = ".$_REQUEST['id']."
ORDER BY name ASC";

$result = $DB->query($sql);	

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';
	
while($row = $DB->fetch_assoc($result)) {	

$sql_inst = "SELECT COUNT( `glpi_computers_softwareversions`.`id` ) AS inst
FROM `glpi_computers_softwareversions`
INNER JOIN `glpi_computers` ON ( `glpi_computers_softwareversions`.`computers_id` = `glpi_computers`.`id` )
WHERE `glpi_computers_softwareversions`.`softwareversions_id` = ".$row['id']."
AND `glpi_computers`.`is_deleted` = '0'
AND `glpi_computers`.`is_template` = '0'
AND `glpi_computers_softwareversions`.`is_deleted` = '0'";

$result_inst = $DB->query($sql_inst);

	while($row_inst = $DB->fetch_assoc($result_inst)) {
	
echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['rulesengine'][78].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['software'][19].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row_inst['inst'].'</li>';
	
	}
	}	
	echo '</ul>';
}	



//softwares computadores

if($_REQUEST['glpi_tab'] == "Computer_SoftwareVersion$1" &&  $_REQUEST['itemtype'] == "Software") {
	
	global $DB;

$sql = "SELECT id, name
FROM `glpi_softwareversions`
WHERE `softwares_id` = ".$_REQUEST['id']."
ORDER BY name ASC";

$result = $DB->query($sql);	

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';
	
while($row = $DB->fetch_assoc($result)) {	

$sql_inst = "SELECT glpi_computers.id, glpi_computers.name AS cname, glpi_computers.serial
FROM `glpi_computers_softwareversions` , glpi_computers
WHERE `softwareversions_id` = ".$row['id']."
AND glpi_computers.id = glpi_computers_softwareversions.computers_id
AND `is_deleted_computer` = 0
ORDER BY `glpi_computers`.`name` ASC";

$result_inst = $DB->query($sql_inst);

	while($row_inst = $DB->fetch_assoc($result_inst)) {
	
echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['rulesengine'][78].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['help'][25].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c"><a href="item.php?itemtype=Computer&menu=inventory&ssmenu=computer&id='.$row_inst['id'].'" target=_blank>'.$row_inst['cname'].'</a></li>';
	
	}
	}	
	echo '</ul>';
}	


//softwares licença

if($_REQUEST['glpi_tab'] == "SoftwareLicense$1") {
	
	global $DB;

$sql_lic = "SELECT id AS lic_id, serial, number, softwareversions_id_buy AS vbuy, softwareversions_id_use AS vuse 
FROM glpi_softwarelicenses
WHERE softwares_id = ".$_REQUEST['id']."";

$result_lic = $DB->query($sql_lic);	

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_lic)) {	

	$sql_lic2 = "SELECT  glpi_softwareversions.name AS vname
	FROM `glpi_softwareversions` 
	WHERE id = " .$row['vbuy']. " ";

	$result_lic2 = $DB->query($sql_lic2);	

	$sql_use = "SELECT COUNT(`glpi_computers_softwarelicenses`.softwarelicenses_id) AS in_use, glpi_computers_softwarelicenses.computers_id
	FROM glpi_softwarelicenses, `glpi_computers_softwarelicenses`
	WHERE softwares_id = ".$_REQUEST['id']."
	AND glpi_softwarelicenses.id = `glpi_computers_softwarelicenses`.softwarelicenses_id";

	$result_use = $DB->query($sql_use);
	

	while($row_lic2 = $DB->fetch_assoc($result_lic2)) {
		
			while($row_use = $DB->fetch_assoc($result_use)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> Serial: </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['serial'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['tracking'][29].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['number'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['software'][1].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row_lic2['vname'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['plugin_mobile']['common'][11].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row_use['in_use'].'</li>';

//echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> Versão em Uso: </li>';
//echo '<li class="ui-li ui-li-static ui-body-c">'.$row['vuse'].'</li>';
	
	}
	}
}
		
	echo '</ul>';
}	


//conexoes monitor

if($_REQUEST['glpi_tab'] == "Computer_Item$1" &&  $_REQUEST['itemtype'] == "Monitor") {
	
	global $DB;
	
$sql_mon = "SELECT glpi_computers.name, glpi_computers.serial, glpi_computers.contact
              FROM `glpi_computers_items`, glpi_computers
              WHERE `itemtype` = '".$_REQUEST['itemtype']."'
              AND `items_id` = '" . $_REQUEST['id']."'
				  AND glpi_computers_items.`computers_id` = glpi_computers.`id`              
              ";

$result_mon = $DB->query($sql_mon) ; 


echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_mon)) {
	
	
	$sql_type = "SELECT glpi_users.firstname, glpi_users.realname
              FROM glpi_users
              WHERE name = ".$row['contact']."
              AND is_deleted = 0";

	$result_type = $DB->query($sql_type) ; 	
	
	while($row2 = $DB->fetch_assoc($result_type)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['common'][16].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['common'][19].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'. $row['serial'] .'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['stats'][20].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row2['firstname'].' '.$row2['realname'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"></li>';
echo '<li class="ui-li ui-li-static ui-body-c"></li>';
}
}
echo '</ul>';
}


//chamados monitor

if($_REQUEST['glpi_tab'] == "Ticket$1" &&  $_REQUEST['itemtype'] == "Monitor") {
	
	global $DB;

$sql_tcomp = "
SELECT id, name, date, solvedate, users_id_recipient, status , solution
FROM `glpi_tickets`
WHERE items_id = ".$_REQUEST['id']."
AND itemtype = '".$_REQUEST['itemtype']."'
AND is_deleted = 0
ORDER BY `glpi_tickets`.`id` DESC";

$result_tcomp = $DB->query($sql_tcomp) ; 

echo '

<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_tcomp)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">ID:</li>';
echo '<li class="ui-li ui-li-static ui-body-c"><a href="item.php?itemtype=Ticket&menu=maintain&ssmenu=ticket&id='.$row['id'].'" target=_blank>'.$row['id'].'</a></li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['joblist'][6].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['state'][0].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.Ticket::getStatus($row['status']).'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][11].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['date'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][12].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['solvedate'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> </li>';
echo '<li class="ui-li ui-li-static ui-body-c"> </li>';
}
echo '</ul>';

	}


//historico monitor

if($_REQUEST['glpi_tab'] == "Log$1" &&  $_REQUEST['itemtype'] == "Monitor") {

PluginMobileTab::showLog($glpi_tab, $itemtype) ;

}	


//historico software

if($_REQUEST['glpi_tab'] == "Log$1" &&  $_REQUEST['itemtype'] == "Software") {

PluginMobileTab::showLog($glpi_tab, $itemtype) ;

}	

//historico chamado

if($_REQUEST['glpi_tab'] == "Log$1" &&  $_REQUEST['itemtype'] == "Ticket") {

PluginMobileTab::showLog($glpi_tab, $itemtype) ;

}


//historico computador

if($_REQUEST['glpi_tab'] == "Log$1" &&  $_REQUEST['itemtype'] == "Computer") {

PluginMobileTab::showLog($glpi_tab, $itemtype) ;

}	


//historico periferico

if($_REQUEST['glpi_tab'] == "Log$1" &&  $_REQUEST['itemtype'] == "Peripheral") {

PluginMobileTab::showLog($glpi_tab, $itemtype) ;

}


//historico telefone

if($_REQUEST['glpi_tab'] == "Log$1" &&  $_REQUEST['itemtype'] == "Phone") {

PluginMobileTab::showLog($glpi_tab, $itemtype) ;

}

//historico impressora

if($_REQUEST['glpi_tab'] == "Log$1" &&  $_REQUEST['itemtype'] == "Printer") {

PluginMobileTab::showLog($glpi_tab, $itemtype) ;

}


//historico entidade

if($_REQUEST['glpi_tab'] == "Log$1" &&  $_REQUEST['itemtype'] == "Entity") {

PluginMobileTab::showLog($glpi_tab, $itemtype) ;

}


//historico perfil

if($_REQUEST['glpi_tab'] == "Log$1" &&  $_REQUEST['itemtype'] == "Profile") {

PluginMobileTab::showLog($glpi_tab, $itemtype) ;

}

//historico rede

if($_REQUEST['glpi_tab'] == "Log$1" &&  $_REQUEST['itemtype'] == "Networkequipment") {

PluginMobileTab::showLog($glpi_tab, $itemtype) ;

}

//historico grupo

if($_REQUEST['glpi_tab'] == "Log$1" &&  $_REQUEST['itemtype'] == "Group") {

PluginMobileTab::showLog($glpi_tab, $itemtype) ;

}


//historico user

if($_REQUEST['glpi_tab'] == "Log$1" &&  $_REQUEST['itemtype'] == "user") {

PluginMobileTab::showLog($glpi_tab, $itemtype) ;

}


// Grupos grupo

if($_REQUEST['glpi_tab'] == "Group$4" &&  $_REQUEST['itemtype'] == "Group") {
	
	global $DB;

$sql_grp = "
SELECT name 
FROM `glpi_groups` 
WHERE groups_id = ".$_REQUEST['id']."";

$result_grp = $DB->query($sql_grp) ; 

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_grp)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['Menu'][36].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

}
echo '</ul>';
}


// usuarios Grupos 

if($_REQUEST['glpi_tab'] == "Group_User$1" &&  $_REQUEST['itemtype'] == "Group") {
	
	global $DB;

$sql_user = "
SELECT glpi_users.id, glpi_users.firstname AS name, glpi_users.realname AS sname
FROM glpi_groups_users, glpi_users
WHERE groups_id = ".$_REQUEST['id']."
AND glpi_users.id = glpi_groups_users.users_id
AND glpi_users.is_deleted = 0
ORDER BY `glpi_users`.`firstname` ASC";

$result_user = $DB->query($sql_user) ; 


echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['Menu'][14].':</li>';

while($row = $DB->fetch_assoc($result_user)) {

echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].' '.$row['sname'].' ('.$row['id'].')</li>';

}
echo '</ul>';
}


//chamados grupo

if($_REQUEST['glpi_tab'] == "Ticket$1" &&  $_REQUEST['itemtype'] == "Group") {
	
	global $DB;

$sql = "
SELECT glpi_tickets.id, glpi_tickets.name, glpi_tickets.date, glpi_tickets.solvedate, glpi_tickets.users_id_recipient, glpi_tickets.status, glpi_tickets.solution
FROM `glpi_tickets` , glpi_groups_tickets
WHERE glpi_groups_tickets.groups_id = ".$_REQUEST['id']."
AND glpi_groups_tickets.type =1
AND glpi_groups_tickets.tickets_id = glpi_tickets.id
AND glpi_tickets.is_deleted = 0
ORDER BY `glpi_tickets`.`id` ASC";

$result = $DB->query($sql) ; 

echo '

<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">ID:</li>';
echo '<li class="ui-li ui-li-static ui-body-c"><a href="item.php?itemtype=Ticket&menu=maintain&ssmenu=ticket&id='.$row['id'].'" target=_blank>'.$row['id'].'</a></li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['joblist'][6].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['state'][0].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.Ticket::getStatus($row['status']).'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][11].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['date'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][12].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['solvedate'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> </li>';
echo '<li class="ui-li ui-li-static ui-body-c"> </li>';
}
echo '</ul>';
	}


// grupos ldap 

if($_REQUEST['glpi_tab'] == "Group$3" &&  $_REQUEST['itemtype'] == "Group") {
	
	global $DB;

$sql = "
SELECT `ldap_field` , `ldap_value`
FROM `glpi_groups`
WHERE `id` =  ".$_REQUEST['id']."";

$result = $DB->query($sql) ; 

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['plugin_mobile']['common'][12].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['ldap_field'].' </li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['setup'][601].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['ldap_value'].' </li>';

}
echo '</ul>';
}


// usuarios entidade 

if($_REQUEST['glpi_tab'] == "Profile_User$1" &&  $_REQUEST['itemtype'] == "Entity") {
	
	global $DB;

$sql_user = "
SELECT glpi_users.id, glpi_users.firstname AS name, glpi_users.realname AS sname
FROM glpi_groups_users, glpi_users
WHERE groups_id = ".$_REQUEST['id']."
AND glpi_users.id = glpi_groups_users.users_id
AND glpi_users.is_deleted = 0
ORDER BY `glpi_users`.`firstname` ASC";

$result_user = $DB->query($sql_user) ; 

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['Menu'][14].':</li>';

while($row = $DB->fetch_assoc($result_user)) {

echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].' '.$row['sname'].' ('.$row['id'].')</li>';

}
echo '</ul>';
}


// entidades entidade 

if($_REQUEST['glpi_tab'] == "Entity$1" &&  $_REQUEST['itemtype'] == "Entity") {
	
	global $DB;

$sql_user = "
SELECT `glpi_entities`.`id` AS `entity` , `glpi_entities`.`entities_id` AS `parent` , glpi_entities.name AS name
FROM `glpi_entities`
WHERE `glpi_entities`.`entities_id` = ".$_REQUEST['id']."
ORDER BY `glpi_entities`.`level` ASC";

$result_user = $DB->query($sql_user) ; 

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['Menu'][37].':</li>';

while($row = $DB->fetch_assoc($result_user)) {

echo '<li class="ui-li ui-li-static ui-body-c"><a href=item.php?itemtype=Entity&menu=admin&ssmenu=entity&id='.$row['entity'].' target=_blank>'.$row['name'].' ('.$row['entity'].')</a></li>';

}
echo '</ul>';
}


// usuarios perfil 

if($_REQUEST['glpi_tab'] == "Profile_User$1" &&  $_REQUEST['itemtype'] == "Profile") {
	
	global $DB;

$sql_user = "
SELECT glpi_users.id, glpi_users.firstname AS name, glpi_users.realname AS sname
FROM glpi_profiles_users, glpi_users
WHERE glpi_profiles_users.profiles_id = ".$_REQUEST['id']."
AND glpi_users.id = glpi_profiles_users.users_id
AND glpi_users.is_deleted = 0
ORDER BY `glpi_users`.`firstname` ASC";

$result_user = $DB->query($sql_user) ; 

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['Menu'][14].':</li>';

while($row = $DB->fetch_assoc($result_user)) {

echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].' '.$row['sname'].' ('.$row['id'].')</li>';

}
echo '</ul>';
}


//chamados rede

if($_REQUEST['glpi_tab'] == "Ticket$1" &&  $_REQUEST['itemtype'] == "Networkequipment") {
	
	global $DB;

$sql_net = "
SELECT id, name, date, solvedate, users_id_recipient, status , solution
FROM `glpi_tickets`
WHERE items_id = ".$_REQUEST['id']."
AND itemtype = '".$_REQUEST['itemtype']."'
AND is_deleted = 0
ORDER BY `glpi_tickets`.`id` DESC";

$result_net = $DB->query($sql_net) ; 

echo '

<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_net)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">ID:</li>';
echo '<li class="ui-li ui-li-static ui-body-c"><a href="item.php?itemtype=Ticket&menu=maintain&ssmenu=ticket&id='.$row['id'].'" target=_blank>'.$row['id'].'</a></li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['joblist'][6].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['state'][0].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.Ticket::getStatus($row['status']).'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][11].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['date'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][12].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['solvedate'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> </li>';
echo '<li class="ui-li ui-li-static ui-body-c"> </li>';
}
echo '</ul>';

	}


//autorizações usuario

if($_REQUEST['glpi_tab'] == "Profile_User$1" &&  $_REQUEST['itemtype'] == "user") {
	
	global $DB;
	
$sql = "SELECT  gpu.users_id, gpu.profiles_id, gpu.entities_id, gpu.is_recursive, gpu.is_dynamic, glpi_entities.completename
FROM `glpi_profiles_users`gpu,  glpi_entities
WHERE `users_id` = ".$_REQUEST['id']."
AND gpu.entities_id = glpi_entities.id";

$result = $DB->query($sql) ; 

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result)) {
	
	$sql_prof = "SELECT glpi_profiles.name
              FROM glpi_profiles
              WHERE id = ".$row['profiles_id']."";

	$result_prof = $DB->query($sql_prof); 	
	
	if($row['is_recursive'] == 1 && $row['is_dynamic'] == 0) {
		
		$prof_type = "(R)";				
		}
	else {
		$prof_type = "(D)";
		}
	
	while($row2 = $DB->fetch_assoc($result_prof)) {
		
echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['Menu'][37].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['completename'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['Menu'][35].' (D='.$LANG['profiles'][29].', R='.$LANG['profiles'][28].')  </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row2['name'].' '.$prof_type.'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"></li>';
echo '<li class="ui-li ui-li-static ui-body-c"></li>';
}
}
echo '</ul>';
}


// grupos usuario

if($_REQUEST['glpi_tab'] == "Group_User$1" &&  $_REQUEST['itemtype'] == "user") {
	
	global $DB;
	
$sql = "SELECT glpi_groups_users.`groups_id` , glpi_groups.name
FROM `glpi_groups_users` , glpi_groups
WHERE `users_id` = ".$_REQUEST['id']."
AND glpi_groups_users.`groups_id` = glpi_groups.id";

$result = $DB->query($sql) ; 

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';
echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['Menu'][36].':</li>';

while($row = $DB->fetch_assoc($result)) {

echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

}
echo '</ul>';
}


//chamados usuário

if($_REQUEST['glpi_tab'] == "Ticket$1" &&  $_REQUEST['itemtype'] == "user") {
	
	global $DB;

$sql = "
SELECT glpi_tickets.id, glpi_tickets.name, glpi_tickets.date, glpi_tickets.solvedate, glpi_tickets.users_id_recipient, glpi_tickets.status , glpi_tickets.solution
FROM `glpi_tickets_users` , glpi_tickets
WHERE glpi_tickets_users.`users_id` = ".$_REQUEST['id']."
AND glpi_tickets_users.`type` = 1
AND glpi_tickets.id = glpi_tickets_users.tickets_id
AND glpi_tickets.is_deleted = 0
ORDER BY `glpi_tickets`.`id` DESC";

$result = $DB->query($sql) ; 

echo '

<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result)) {

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">ID:</li>';
echo '<li class="ui-li ui-li-static ui-body-c"><a href="item.php?itemtype=Ticket&menu=maintain&ssmenu=ticket&id='.$row['id'].'" target=_blank>'.$row['id'].'</a></li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['joblist'][6].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['state'][0].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.Ticket::getStatus($row['status']).'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][11].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['date'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][12].':</li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['solvedate'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> </li>';
echo '<li class="ui-li ui-li-static ui-body-c"> </li>';
}
echo '</ul>';
	}

?>
