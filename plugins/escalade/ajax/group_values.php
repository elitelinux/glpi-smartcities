<?php 
$AJAX_INCLUDE = 1;
include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();
Session::checkLoginUser();

$ticket_id = (isset($_REQUEST['ticket_id'])) ? $_REQUEST['ticket_id'] : 0;

$PluginEscaladeGroup_Group = new PluginEscaladeGroup_Group();

$groups_id_filtred = $PluginEscaladeGroup_Group->getGroups($ticket_id);

if (count($groups_id_filtred) > 0) {
   $myarray = array();
   foreach ($groups_id_filtred as $groups_id => $groups_name) {
      $myarray[] = $groups_id;
   }
   $newarray = implode(", ", $myarray);
   $condition = " id IN ($newarray)";
   
} else {
   $condition = "1=0";
}

$rand = mt_rand();
$_SESSION['glpicondition'][$rand] = $condition;

$_GET["condition"] = $rand;

require ("../../../ajax/getDropdownValue.php");
