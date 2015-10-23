<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}
global $DB, $CFG_GLPI;

$userid =  $_SESSION['glpiID'];
$profid = $_SESSION['glpiactiveprofile']['id'];
$activeent = $_SESSION['glpiactive_entity'];

//if ( $profid == 4 ) {
if ($profid == 3 || $profid == 4 || $profid == 7) {

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
      AND glpi_tickets.status  NOT IN (1,4,5,6)
      ".$entidade." ";

$result_pro = $DB->query($sql_pro) or die ("erro");
$total_pro = $DB->result($result_pro,0,'total');


//total de chamados solucionados
$sql_solv =	"SELECT COUNT(glpi_tickets.id) as total
      FROM glpi_tickets
      LEFT JOIN glpi_entities ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.is_deleted = '0'
      AND glpi_tickets.status = 5
      ".$entidade." ";

$result_solv = $DB->query($sql_solv) or die ("erro");
$total_solv = $DB->result($result_solv,0,'total');


//count due tickets
$sql_due = "SELECT DISTINCT COUNT(glpi_tickets.id) AS due
FROM glpi_tickets
WHERE glpi_tickets.status NOT IN (4,5,6)
AND glpi_tickets.is_deleted = 0
AND glpi_tickets.due_date IS NOT NULL
AND glpi_tickets.due_date < NOW()
".$entidade." ";

$result_due = $DB->query($sql_due);
$total_due = $DB->result($result_due,0,'due');


//total de chamados pendentes
$sql_pend =	"SELECT COUNT(glpi_tickets.id) as total
      FROM glpi_tickets
      LEFT JOIN glpi_entities ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.is_deleted = '0'
      AND glpi_tickets.status = 4
      ".$entidade." ";

$result_pend = $DB->query($sql_pend) or die ("erro");
$total_pend = $DB->result($result_pend,0,'total');

//links para lista de chamados
$href_cham = $CFG_GLPI["root_doc"]."/front/ticket.php?is_deleted=0&criteria[0][field]=12&criteria[0][searchtype]=equals&criteria[0][value]=notclosed&itemtype=Ticket&start=0";
$href_new  = $CFG_GLPI["root_doc"]."/front/ticket.php?is_deleted=0&criteria[0][field]=12&criteria[0][searchtype]=equals&criteria[0][value]=1&itemtype=Ticket&start=0";
$href_pro  = $CFG_GLPI["root_doc"]."/front/ticket.php?is_deleted=0&criteria[0][field]=12&criteria[0][searchtype]=equals&criteria[0][value]=process&itemtype=Ticket&start=0";
$href_solv = $CFG_GLPI["root_doc"]."/front/ticket.php?is_deleted=0&criteria[0][field]=12&criteria[0][searchtype]=equals&criteria[0][value]=5&itemtype=Ticket&start=0";
$href_pend = $CFG_GLPI["root_doc"]."/front/ticket.php?is_deleted=0&criteria[0][field]=12&criteria[0][searchtype]=equals&criteria[0][value]=4&itemtype=Ticket&start=0";
$href_due  = $CFG_GLPI["root_doc"]."/front/ticket.php?is_deleted=0&criteria[0][field]=82&criteria[0][searchtype]=equals&criteria[0][value]=1&criteria[1][link]=AND&criteria[1][field]=12&criteria[1][searchtype]=equals&criteria[1][value]=notold&itemtype=Ticket&start=0";

//http://sos.mpro.gov/front/ticket.php?is_deleted=0&criteria[0][field]=82&criteria[0][searchtype]=equals&criteria[0][value]=1&criteria[1][link]=AND&criteria[1][field]=12&criteria[1][searchtype]=equals&criteria[1][value]=notold&search=Pesquisar&itemtype=Ticket&start=0&_glpi_csrf_token=4cf3b3e79a2c94ff89515db4ce2d8d30

//if ( $_SESSION['glpiactiveprofile']['id'] == 4 ) {
echo '
<!-- <div class="container center" style="margin-top:7px"> -->
<div class="row" style="margin-top:9px; ">
<div class="center col-xs-12 col-xs-offset-1 col-sm-12 col-sm-offset-1 col-md-12 col-md-offset-1">
<div class="panel panel-default panel-table">
<div class="panel-heading">
  <div class="tr">
      <div class="td"><span class="number"><a href='.$href_cham .'>' . $total_geral . '</a> </span> </p><span class="titlen"> '. _nx('ticket','Opened','Opened',2) . '</span></div>
      <div class="td"><span class="number cnew"><a href='.$href_new .'>' . $total_new . '</a> </span> </p><span class="titlen"> '. Ticket::getStatus(1) .' </span></div>
      <div class="td"><span class="number"><a href='.$href_pro .'>' . $total_pro . '</a></span> </p><span class="titlen"> '. __('Processing') . ' </span></div>
      <div class="td"><span class="number csolved"><a href='.$href_solv .'>' . $total_solv . '</a></span> </p><span class="titlen"> '. Ticket::getStatus(5) .'</span></div>
      <div class="td"><span class="number cpending"><a href='.$href_pend .'>' . $total_pend . '</a> </span> </p><span class="titlen"> '. Ticket::getStatus(4) .' </span></div>
      <div class="td"><span class="number cdue"> <a href='.$href_due .'>' . $total_due . '</a>  </span> </p><span class="titlen"> '. __('Late') . ' </span></div>
  </div>
</div>
<!-- <div class="panel-body">
  <div class="tr">
      <div class="td">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Similique facere necessitatibus quo laboriosam consequuntur</div>
      <div class="td">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Excepturi aliquam placeat odit quasi autem distinctio veritatis ex numquam nihil</div>
      <div class="td">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Excepturi aliquam placeat odit quasi autem distinctio veritatis ex numquam nihil nulla tempora a dolorem omnis beatae facilis perspiciatis doloribus? Error dolore!</div>
  </div>
</div>
<div class="panel-footer">
  <div class="tr">
      <div class="td">footer</div>
      <div class="td">footer</div>
      <div class="td">footer</div>
  </div>
</div> -->
</div>
</div>
</div>';
//}
?>
