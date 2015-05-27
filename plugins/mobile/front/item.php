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

/*
$item = new PluginMobileItem;
$item->displayItem($_REQUEST['id'], $_REQUEST['itemtype']);
*/

      if (!Session::haveRight("ticket",READ)) {
          return false;
      }

$itemtype = $_REQUEST['itemtype'];
 $welcome = "&nbsp;";
        
      if (class_exists($itemtype)) {
         $classname = ucfirst($itemtype);
         $obj = new $classname;
         $welcome = $obj->getTypeName()." (".$_REQUEST['id'].")";
      }
       
      $table = $obj->getTable();
      
      //saveActiveProfileAndApplyRead();
      
      $common = new PluginMobileCommon;
      $common->displayHeader($welcome, "search.php?menu=".$_GET['menu']."&ssmenu=".$_GET['ssmenu']."&itemtype=$itemtype", '', '', 'item');
   
   	PluginMobileTab::displayTabBar();   

//ticket	  

if($itemtype == "Ticket") {

$sql_tick = "SELECT * 
FROM glpi_tickets
WHERE id = " . $_REQUEST['id'] ." ";

$result_tick = $DB->query($sql_tick) ; 

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_tick)) {
	
	//requester
	$sql_autor =
	"SELECT DISTINCT glpi_users.id AS id
	FROM glpi_tickets_users, glpi_users, glpi_tickets
	WHERE glpi_users.id = glpi_tickets_users.users_id
	AND glpi_tickets.id = ".$_REQUEST['id']."
	AND glpi_tickets_users.tickets_id = glpi_tickets.id 
	AND  glpi_tickets_users.type = 1";
	
	$result_autor = $DB->query($sql_autor);	
	$row_autor = $DB->fetch_assoc($result_autor);
	
	//technician
   $sql_tech =
	"SELECT DISTINCT glpi_users.id AS id
	FROM glpi_tickets_users, glpi_users, glpi_tickets
	WHERE glpi_users.id = glpi_tickets_users.users_id
	AND glpi_tickets.id = ".$_REQUEST['id']."
	AND glpi_tickets_users.tickets_id = glpi_tickets.id 
	AND  glpi_tickets_users.type = 2";
	
	$result_tech = $DB->query($sql_tech);	
	$row_tech = $DB->fetch_assoc($result_tech);
	
	//observer
   $sql_obs =
	"SELECT DISTINCT glpi_users.id AS id
	FROM glpi_tickets_users, glpi_users, glpi_tickets
	WHERE glpi_users.id = glpi_tickets_users.users_id
	AND glpi_tickets.id = ".$_REQUEST['id']."
	AND glpi_tickets_users.tickets_id = glpi_tickets.id 
	AND  glpi_tickets_users.type = 3";
	
	$result_obs = $DB->query($sql_obs);	
	$row_obs = $DB->fetch_assoc($result_obs);
	
	//type
	if($row['type'] == "1" ) { $tipo = "Incident";}
	if($row['type'] == "2" ) { $tipo = "Request";}
	
	//category
   $sql_cat =
	"SELECT completename
	FROM glpi_itilcategories
	WHERE id = ".$row['itilcategories_id']." ";
	
	$result_cat = $DB->query($sql_cat);	
	$row_cat = $DB->fetch_assoc($result_cat);
	
	//get priority
	$sql_prio = "SELECT name, value
			FROM glpi_configs
			WHERE name LIKE 'priority_".$row['priority']."' ";
	
	$result_prio = $DB->query($sql_prio);	
	$row_prio = $DB->fetch_assoc($result_prio);	
	
	$priority = substr($row_prio['name'],9,10);
	
	if($priority == 1) {
		$prio_name = _x('priority', 'Very low'); }
	
	if($priority == 2) {
		$prio_name = _x('priority', 'Low'); }
		
	if($priority == 3) {
		$prio_name = _x('priority', 'Medium'); } 		
		
	if($priority == 4) {	
		$prio_name = _x('priority', 'High'); }
		
	if($priority == 5) {
		$prio_name = _x('priority', 'Very high'); } 	
		
	if($priority == 6) {
		$prio_name = _x('priority', 'Major'); } 			
		
		 					 	
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">ID:</li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'.$row['id'].'</li>';
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Title').':</li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['joblist'][6].': </li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'.$row['content'].'</li>';
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.__('Status').': </li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'. Ticket::getStatus($row['status']) .'</li>';	
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.__('Priority').': </li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'. $prio_name.'</li>';	
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.__('Type').': </li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'. __($tipo).'</li>';	
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.__('Category').': </li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'. $row_cat['completename'].'</li>';	
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '. __('Requester').': </li>';
	echo '<li class="ui-li ui-li-static ui-body-c"><a href="'.$CFG_GLPI["root_doc"].'/plugins/mobile/front/item.php?itemtype=user&menu=admin&ssmenu=user&id='.$row_autor['id'].'" target="_blank" >'. getUserName($row_autor['id']) .'</a></li>';	
	
	if($row_obs['id'] != '') {
		echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Watcher').':</li>';
		echo '<li class="ui-li ui-li-static ui-body-c"><a href="'.$CFG_GLPI["root_doc"].'/plugins/mobile/front/item.php?itemtype=user&menu=admin&ssmenu=user&id='.$row_obs['id'].'" target="_blank" >'. getUserName($row_obs['id']) .'</a></li>';
	}
	
	if($row_tech['id'] != '') {
		echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Assigned to').':</li>';
		echo '<li class="ui-li ui-li-static ui-body-c"><a href="'.$CFG_GLPI["root_doc"].'/plugins/mobile/front/item.php?itemtype=user&menu=admin&ssmenu=user&id='.$row_tech['id'].'" target="_blank" >'. getUserName($row_tech['id']) .'</a></li>';
	}
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Opening date').':</li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'.$row['date'].'</li>';
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Due date').':</li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'.$row['due_date'].'</li>';

	if($row['solvedate'] != '') {
		echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Resolution date').':</li>';
		echo '<li class="ui-li ui-li-static ui-body-c">'.$row['solvedate'].'</li>';
	}
	
	if($row['closedate'] != '') {
		echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Close date').':</li>';
		echo '<li class="ui-li ui-li-static ui-body-c">'.$row['closedate'].'</li>';
	}
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Last update').':</li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'.$row['date_mod'].'</li>';
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Last updater').':</li>';
	echo '<li class="ui-li ui-li-static ui-body-c"><a href="'.$CFG_GLPI["root_doc"].'/plugins/mobile/front/item.php?itemtype=user&menu=admin&ssmenu=user&id='.$row['users_id_lastupdater'].'" target="_blank" >'. getUserName($row['users_id_lastupdater']) .'</a></li>';

	if($row['solution'] != '') {	
		echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Solution').':</li>';
		echo '<li class="ui-li ui-li-static ui-body-c">'.$row['solution'].'</li>';
	}
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"></li>';
	echo '<li class="ui-li ui-li-static ui-body-c"></li>';
}

echo '</ul>';
}


//user	  

if($itemtype == "user") {

$sql_user = "SELECT * 
FROM glpi_users
WHERE id = " . $_REQUEST['id'] ." ";

$result_user = $DB->query($sql_user) ; 

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';

while($row = $DB->fetch_assoc($result_user)) {
	
//active
if($row['is_active'] == 1) { $active = "Yes";}
else { $active = "No";}

//location
$sql_loc =
"SELECT completename
FROM glpi_locations
WHERE id = ".$row['locations_id']." ";

$result_loc = $DB->query($sql_loc);	
$row_loc = $DB->fetch_assoc($result_loc);
	
			 					 	
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">ID:</li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'.$row['id'].'</li>';
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Login').':</li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'.$row['name'].'</li>';
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '. __('Name').': </li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'.$row['firstname'].'</li>';
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.__('Surname').': </li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'. $row['realname'] .'</li>';	
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.__('Active').': </li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'. __($active).'</li>';	
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.__('Phone').': </li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'. $row['phone'] .'</li>';	
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.__('Phone').' 2: </li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'. $row['phone2'].'</li>';	
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '. __('Mobile phone').': </li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'. $row['mobile'] .'</li>';	
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '. __('Location').': </li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'. $row_loc['completename'] .'</li>';

	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Last login').':</li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'. $row['last_login'] .'</li>';	

	//echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Authentication').':</li>';
	//echo '<li class="ui-li ui-li-static ui-body-c">'. $row['authtype'] .'</li>';	
	
	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Last synchronization').':</li>';
	echo '<li class="ui-li ui-li-static ui-body-c">'. $row['date_sync'] .'</li>';	
	
	if($row['comment'] != '') {	
		echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'. __('Comments').':</li>';
		echo '<li class="ui-li ui-li-static ui-body-c">'.$row['comment'].'</li>';
	}

	echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"></li>';
	echo '<li class="ui-li ui-li-static ui-body-c"></li>';
}

echo '</ul>';
}


$common->displayFooter();
?>
