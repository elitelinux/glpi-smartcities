<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}
global $DB, $CFG_GLPI;

$userid =  $_SESSION['glpiID'];
$profid = $_SESSION['glpiactiveprofile']['id'];
$activeent = $_SESSION['glpiactive_entity'];

if ( $profid == 4 ) {

	//get user entities for admins
	$entities = Profile_User::getUserEntities($_SESSION['glpiID'], true);
	$ent = implode(",",$entities);
	$entidade = "AND glpi_tickets.entities_id IN (".$ent.")";
  $getuser = "";
}
else {
  //technician
  $entidade = "AND glpi_tickets.entities_id IN (".$activeent.")";
  $getuser = "AND glpi_users.id IN = " . $userid ."" ;
}

//total de chamados abertos
$sql_geral =	"SELECT COUNT(glpi_tickets.id) as total
      FROM glpi_tickets
      LEFT JOIN glpi_entities ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.is_deleted = '0'
      AND glpi_tickets.status NOT IN (6)
      ".$entidade." ";

$result_geral = $DB->query($sql_geral) or die ("erro");
$total_geral = $DB->result($result_geral,0,'total');


//total de chamados novos
$sql_new =	"SELECT COUNT(glpi_tickets.id) as total
      FROM glpi_tickets
      LEFT JOIN glpi_entities ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.is_deleted = '0'
      AND glpi_tickets.status  = 1
      ".$entidade." ";

$result_new = $DB->query($sql_new) or die ("erro");
$total_new = $DB->result($result_new,0,'total');


//total de chamados atribuidos
$sql_pro =	"SELECT COUNT(glpi_tickets.id) as total
      FROM glpi_tickets
      LEFT JOIN glpi_entities ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.is_deleted = '0'
      AND glpi_tickets.status  NOT IN (1,5,6)
      ".$entidade." ";

$result_pro = $DB->query($sql_pro) or die ("erro");
$total_pro = $DB->result($result_pro,0,'total');


//total de chamados solved
$sql_solv =	"SELECT COUNT(glpi_tickets.id) as total
      FROM glpi_tickets
      LEFT JOIN glpi_entities ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.is_deleted = '0'
      AND glpi_tickets.status = 5
      ".$entidade." ";

$result_solv = $DB->query($sql_solv) or die ("erro");
$total_solv = $DB->result($result_solv,0,'total');

//if ( $_SESSION['glpiactiveprofile']['id'] == 4 ) {
echo '
<!-- <div class="container center" style="margin-top:8px"> -->
<div class="row" style="margin-top:9px; margin-bottom:20px; ">
<div class="center col-xs-12 col-xs-offset-1 col-sm-12 col-sm-offset-1 col-md-12 col-md-offset-1">
<table border="1" class="panel-table" style="table-layout: fixed;">
<tr>
<td><span class="number"> ' . $total_geral . '  </span> </p><span class="titlen"> Open Tickets</span></td>
<td><span class="number"> ' . $total_new . ' </span> </p><span class="titlen"> New Tickets</span></td>
<td><span class="number"> ' . $total_pro . ' </span> </p><span class="titlen"> Processing </span></td>
<td><span class="number"> ' . $total_solv . ' </span> </p><span class="titlen"> Solved</span></td>
<td><span class="number"> 12649 </span> </p><span class="titlen"> Tickets</span></td>
<td><span class="number"> 91% </span> </p><span class="titlen"> Satisfaction</span></td>
</tr>
</table>
</div>
</div>';
//}
?>
