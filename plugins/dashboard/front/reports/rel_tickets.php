<?php

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");
include (GLPI_ROOT . "/config/config.php");
//include "../inc/functions.php";

global $DB;

Session::checkLoginUser();
Session::checkRight("profile", READ);

if(!empty($_POST['submit']))
{	
	$data_ini =  $_POST['date1'];	
	$data_fin = $_POST['date2'];
}

else {	
	$data_ini = date("Y-m-01");
	$data_fin = date("Y-m-d");	
	}  

if(!isset($_POST["sel_ent"])) {
	$id_ent = $_GET["ent"];	
}

else {
	$id_ent = $_POST["sel_ent"];
}

function conv_data($data) {
	if($data != "") {
		$source = $data;
		$date = new DateTime($source);	
		return $date->format('d-m-Y');}
	else {
		return "";	
	}
}

function conv_data_hora($data) {
	if($data != "") {
		$source = $data;
		$date = new DateTime($source);	
		return $date->format('d-m-Y H:i:s');}
	else {
		return "";	
	}
}

function dropdown( $name, array $options, $selected=null )
{
    /*** begin the select ***/
    $dropdown = '<select style="width: 300px; height: 27px;" autofocus name="'.$name.'" id="'.$name.'">'."\n";

    $selected = $selected;
    /*** loop over the options ***/
    foreach( $options as $key=>$option )
    {
        /*** assign a selected value ***/
        $select = $selected==$key ? ' selected' : null;
        /*** add each option to the dropdown ***/
        $dropdown .= '<option value="'.$key.'"'.$select.'>'.$option.'</option>'."\n";
    }
    /*** close the select ***/
    $dropdown .= '</select>'."\n";

    /*** and return the completed dropdown ***/
    return $dropdown;
}


?>

<html> 
<head>
<title> GLPI - <?php echo __('Tickets', 'dashboard') ?> </title>
<!-- <base href= "<?php $_SERVER['SERVER_NAME'] ?>" > -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  <meta http-equiv="content-language" content="en-us" />
  <meta charset="utf-8">
  
  <link rel="icon" href="../img/dash.ico" type="image/x-icon" />
  <link rel="shortcut icon" href="../img/dash.ico" type="image/x-icon" />
  <link href="../css/styles.css" rel="stylesheet" type="text/css" />
  <link href="../css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="../css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />  
  <link href="../css/font-awesome.css" type="text/css" rel="stylesheet" />  
  <script language="javascript" src="../js/jquery.min.js"></script>  
  <link href="../inc/select2/select2.css" rel="stylesheet" type="text/css">
  <script src="../inc/select2/select2.js" type="text/javascript" language="javascript"></script>

  <script src="../js/bootstrap-datepicker.js"></script>
   <link href="../css/datepicker.css" rel="stylesheet" type="text/css">
   <link href="../less/datepicker.less" rel="stylesheet" type="text/css">
   
   <script src="../js/media/js/jquery.dataTables.min.js"></script>
	<link href="../js/media/css/dataTables.bootstrap.css" type="text/css" rel="stylesheet" />  
	<script src="../js/media/js/dataTables.bootstrap.js"></script> 
	<link href="../js/extensions/TableTools/css/dataTables.tableTools.css" type="text/css" rel="stylesheet" />
	<script src="../js/extensions/TableTools/js/dataTables.tableTools.js"></script>
	
	<script src="../js/extensions/ColVis/css/dataTables.colVis.min.cs"></script>
	<script src="../js/extensions/ColVis/js/dataTables.colVis.min.js"></script>
	<link href="//cdn.datatables.net/colvis/1.1.0/css/dataTables.colVis.min.css" rel="stylesheet">
	
<style type="text/css">	
	select { width: 60px; }
	table.dataTable { empty-cells: show; }
   a:link, a:visited, a:active { text-decoration: none;}
</style>

<?php echo '<link rel="stylesheet" type="text/css" href="../css/style-'.$_SESSION['style'].'">';  ?> 
   
</head>

<body style="background-color: #e5e5e5; margin-left:0%;">

<div id='content' >
<div id='container-fluid' style="margin: 0px 2% 0px 2%;"> 
<div id="charts" class="row-fluid chart"> 
<div id="pad-wrapper" >
<div id="head-lg" class="row-fluid" style="height: 610px;">

<style type="text/css">
a:link, a:visited, a:active {
	text-decoration: none
	}
a:hover {
	color: #000099;
	}
</style>

<a href="../index.php"><i class="fa fa-home" style="font-size:14pt; margin-left:25px;"></i><span></span></a>

	<div id="titulo_graf"> <?php echo __('Tickets', 'dashboard') ?> </div>
	
		<div id="datas-tec3" class="span12 row-fluid" >
 
		<form id="form1" name="form1" class="form_rel" method="post" action="rel_tickets.php?con=1" style="margin-left: 29%;"> 
		<table border="0" cellspacing="0" cellpadding="3" bgcolor="#efefef" >
		<tr>			
			<td style="margin-top:2px; width:110px;"><?php echo __('Period'); ?>: </td>	
			<td style="width: 200px;">
			<?php
			$url = $_SERVER['REQUEST_URI']; 
			$arr_url = explode("?", $url);
			$url2 = $arr_url[0];
			    
				echo'
				<table>
					<tr>
						<td>
						   <div class="input-group date" id="dp1" data-date="'.$data_ini.'" data-date-format="yyyy-mm-dd">
						    	<input class="col-md-9 form-control" size="13" type="text" name="date1" value="'.$data_ini.'" >		    	
						    	<span class="input-group-addon add-on"><i class="fa fa-calendar"></i></span>	    	
					    	</div>
						</td>
						<td>&nbsp;</td>
						<td>
					   	<div class="input-group date" id="dp2" data-date="'.$data_fin.'" data-date-format="yyyy-mm-dd">
						    	<input class="col-md-9 form-control" size="13" type="text" name="date2" value="'.$data_fin.'" >		    	
						    	<span class="input-group-addon add-on"><i class="fa fa-calendar"></i></span>	    	
					    	</div>
						</td>
						<td>&nbsp;</td>
					</tr>
				</table> ';
				?>	
			
			<script language="Javascript">		
				$('#dp1').datepicker('update');
				$('#dp2').datepicker('update');		
			</script>
			</td>
		</tr>	
		<tr><td height="12px"></td></tr>		
		<tr>
			<td style="margin-top:2px; width:100px;"><?php echo __('Entity'); ?>: </td>		
			<td style="margin-top:2px;">
			<?php
			
			// lista de entidades		
			$sql_ent = "
			SELECT id , name
			FROM `glpi_entities`
			ORDER BY `name` ASC";
			
			$result_ent = $DB->query($sql_ent);
			
			$arr_ent = array();
			$arr_ent[0] = "" ;
			
			while ($row_ent = $DB->fetch_assoc($result_ent))		
				{ 
					$v_row_ent = $row_ent['id'];
					$arr_ent[$v_row_ent] = $row_ent['name'] ;			
				} 
				
			$name = 'sel_ent';
			$options = $arr_ent;
			$selected = "0";
			
			echo dropdown( $name, $options, $selected );		
			?>
			</td>
		</tr>	
		<tr><td height="12px"></td></tr>				
		<tr>
			<td style="margin-top:2px; width:100px;"><?php echo __('Status'); ?>:  </td>		
			<td style="margin-top:2px;">
			<?php
			
			// lista de status		
			$sql_sta = "
			SELECT DISTINCT status
			FROM glpi_tickets
			ORDER BY status ASC";
			
			$result_sta = $DB->query($sql_sta);
			
			$arr_sta = array();
			$arr_sta[0] = "-----";
			
			while ($row_sta = $DB->fetch_assoc($result_sta))		
				{ 
					$v_row_sta = $row_sta['status'];
					$arr_sta[$v_row_sta] = Ticket::getStatus($row_sta['status']) ;			
				} 
				
			$arr_sta['notold']    = _x('status', 'Not solved');
         $arr_sta['notclosed'] = _x('status', 'Not closed'); 	
				
			$name = 'sel_sta';
			$options = $arr_sta;
			$selected = "0";
						
			echo dropdown( $name, $options, $selected );
			?>
			</td>
		</tr>		
		<tr><td height="12px"></td></tr>	
		<tr>
			<td style="margin-top:2px; width:165px;"><?php echo __('Request source'); ?>: </td>		
			<td style="margin-top:2px;">
			<?php
			// lista de origem		
			$sql_req = "
			SELECT id, name
			FROM glpi_requesttypes
			ORDER BY id ASC ";
			
			$result_req = $DB->query($sql_req);
			
			$arr_req = array();
			$arr_req[0] = "-----";
			
			while ($row_req = $DB->fetch_assoc($result_req))		
				{ 
					$v_row_req = $row_req['id'];
					$arr_req[$v_row_req] = $row_req['name'] ;			
				} 
				
			$name = 'sel_req';
			$options = $arr_req;
			$selected = "0";
			
			echo dropdown( $name, $options, $selected );
			?>
			</td>
		</tr>
		<tr><td height="12px"></td></tr>			
		<tr>
			<td style="margin-top:2px; width:100px;"><?php echo __('Priority'); ?>:  </td>		
			<td style="margin-top:2px;">
			<?php
			// lista de tipos		
			$arr_pri = array();
			$arr_pri[0] = "-----" ;
			$arr_pri[1] = _x('priority', 'Very low');
			$arr_pri[2] = _x('priority', 'Low');
			$arr_pri[3] = _x('priority', 'Medium');
			$arr_pri[4] = _x('priority', 'High');
			$arr_pri[5] = _x('priority', 'Very high');
			$arr_pri[6] = _x('priority', 'Major');
						
			$name = 'sel_pri';
			$options = $arr_pri;
			$selected = "0";
			
			echo dropdown( $name, $options, $selected );
			?>
			</td>
		</tr>
		<tr><td height="12px"></td></tr>			
		<tr>
			<td style="margin-top:2px; width:100px;"><?php echo __('Category'); ?>:  </td>		
			<td style="margin-top:2px;">
			<?php
			
			// lista de categorias		
			$sql_cat = "
			SELECT id, completename AS name
			FROM glpi_itilcategories
			ORDER BY name ASC ";
			
			$result_cat = $DB->query($sql_cat);
			
			$arr_cat = array();
			$arr_cat[0] = "-----" ;
			
			while ($row_cat = $DB->fetch_assoc($result_cat))		
				{ 
					$v_row_cat = $row_cat['id'];
					$arr_cat[$v_row_cat] = $row_cat['name'] ;			
				} 
				
			$name = 'sel_cat';
			$options = $arr_cat;
			$selected = "0";
			
			echo dropdown( $name, $options, $selected );
			?>
			</td>
		</tr>		
		<tr><td height="12px"></td></tr>	
		<tr>
			<td style="margin-top:2px; width:100px;"><?php echo __('Type'); ?>:  </td>		
			<td style="margin-top:2px;">
			<?php
			// lista de tipos		
			$arr_tip = array();
			$arr_tip[0] = "-----" ;
			$arr_tip[1] = __('Incident') ;
			$arr_tip[2] = __('Request');			
			$name = 'sel_tip';
			$options = $arr_tip;
			$selected = "0";
			
			echo dropdown( $name, $options, $selected );
			?>
			</td>
		</tr>
		<tr><td height="12px"></td></tr>			
		<tr>
			<td style="margin-top:2px; width:100px;"><?php echo __('Solution'); ?>:  </td>		
			<td style="margin-top:2px;">
			<?php
			// solution		
			$arr_sol = array();
			$arr_sol[0] = "-----" ;
			$arr_sol[1] = __('Yes');
			$arr_sol[2] = __('No');			
			$name = 'sel_sol';
			$options = $arr_sol;
			$selected = "0";
			
			echo dropdown( $name, $options, $selected );
			?>
			</td>
		</tr>	
		<tr><td height="20px"></td></tr>
		<tr>
			<td colspan="2" align="center">		 
				<button class="btn btn-primary btn-sm" type="submit" name="submit" value="Atualizar" ><i class="fa fa-search"></i>&nbsp; <?php echo __('Consult', 'dashboard'); ?></button>
				<button class="btn btn-primary btn-sm" type="button" name="Limpar" value="Limpar" onclick="location.href='<?php echo $url2 ?>'" > <i class="fa fa-trash-o"></i>&nbsp; <?php echo __('Clean', 'dashboard'); ?> </button></td>
			</td>
		</tr>
			
			</table>
		<?php Html::closeForm(); ?>

		</div>
	</div>	

<?php 

//entidades
$con = $_GET['con'];

if($con == "1") {

if(!isset($_POST['date1']))
{	
	$data_ini2 = $_GET['date1'];	
	$data_fin2 = $_GET['date2'];
}

else {	
	$data_ini2 = $_POST['date1'];	
	$data_fin2 = $_POST['date2'];	
}  

if(!isset($_REQUEST["sel_ent"])) { $id_ent = 0; }
else { $id_ent = $_REQUEST["sel_ent"]; }

if(isset($_REQUEST["sel_sta"]) && $_REQUEST["sel_sta"] != '0') { 

	if($_REQUEST["sel_sta"] == 'notclosed') {
	$id_sta = "AND glpi_tickets.status <> 6"; 
	}
	elseif($_REQUEST["sel_sta"] == 'notold') {
	$id_sta = "AND glpi_tickets.status NOT IN ('5','6')"; 
	}
	else {
	$id_sta = "AND glpi_tickets.status = ".$_REQUEST["sel_sta"] ;
	}
}
else { $id_sta = ''; }

//AND glpi_tickets.status LIKE '%".$id_sta."'

if(isset($_REQUEST["sel_req"]) && $_REQUEST["sel_req"] != '0') { $id_req = $_REQUEST["sel_req"]; }
else { $id_req = ''; }

if(isset($_REQUEST["sel_pri"]) && $_REQUEST["sel_pri"] != '0') { $id_pri = $_REQUEST["sel_pri"]; }
else { $id_pri = ''; }

if(isset($_REQUEST["sel_cat"]) && $_REQUEST["sel_cat"] != '0') { $id_cat = $_REQUEST["sel_cat"]; }
else { $id_cat = ''; }

if(isset($_REQUEST["sel_tip"]) && $_REQUEST["sel_tip"] != '0') { $id_tip = $_REQUEST["sel_tip"]; }
else { $id_tip = ''; }

if(isset($_REQUEST["sel_sol"]) && $_REQUEST["sel_sol"] != '0') { $id_sol1 = $_REQUEST["sel_sol"]; }
else { $id_sol1 = ''; }

if($id_sol1 == '1') { 
	$id_sol = ", solution"; 
	$th_sol = "<th style='font-size: 12px; font-weight:bold; text-align: center; cursor:pointer;'> ".__('Solution')." </th>";	
	$targ = '9' ;	
	}
else {
	$id_sol = ''; 
	$th_sol = '';
	$td_sol = '';
	$targ = '';
	}


$arr_param = array($id_ent, $id_sta, $id_req, $id_pri, $id_cat, $id_tip, $id_sol);

if($data_ini2 == $data_fin2) {
	$datas2 = "LIKE '%".$data_ini2."%'";	
}	

else {
	$datas2 = "BETWEEN '".$data_ini2." 00:00:00' AND '".$data_fin2." 23:59:59'";	
}	

//print_r($arr_param);
//echo "data:".$datas2;

if($id_sta == 5) {
	$period = "AND glpi_tickets.solvedate ".$datas2." ";	
}

elseif($id_sta == 6) {
	$period = "AND glpi_tickets.closedate ".$datas2." ";	
}	

else {
	$period = "AND glpi_tickets.date ".$datas2." ";	 
}


// Chamados
$sql_cham = 
"SELECT id, entities_id, name, date, closedate, solvedate, status, users_id_recipient, requesttypes_id, itemtype, priority, itilcategories_id, type " .$id_sol. "   
FROM glpi_tickets
WHERE glpi_tickets.entities_id = ".$id_ent."
AND glpi_tickets.is_deleted = 0
" .$period. "
".$id_sta."
AND glpi_tickets.requesttypes_id LIKE '%".$id_req."'
AND glpi_tickets.priority LIKE '%".$id_pri."'
AND glpi_tickets.itilcategories_id LIKE '%".$id_cat."'
AND glpi_tickets.type LIKE '%".$id_tip."'
ORDER BY id DESC ";

$result_cham = $DB->query($sql_cham);


$consulta1 = 
"SELECT glpi_tickets.id AS total
FROM glpi_tickets
WHERE glpi_tickets.entities_id = ".$id_ent."
AND glpi_tickets.is_deleted = 0
" .$period. "
".$id_sta."
AND glpi_tickets.requesttypes_id LIKE '%".$id_req."'
AND glpi_tickets.priority LIKE '%".$id_pri."'
AND glpi_tickets.itilcategories_id LIKE '%".$id_cat."'
AND glpi_tickets.type LIKE '%".$id_tip."'
";

$result_cons1 = $DB->query($consulta1);

$conta_cons = $DB->numrows($result_cons1);

$consulta = $conta_cons;


if($consulta > 0) {

// nome da entidade
$sql_nm = "
SELECT name
FROM `glpi_entities`
WHERE id = ".$id_ent."";

$result_nm = $DB->query($sql_nm);
$ent_name = $DB->fetch_assoc($result_nm);


//listar chamados
echo "
<div class='well info_box row-fluid col-md-12 report-tic' style='margin-left: -1px;'>

<table class='row-fluid'  style='font-size: 18px; font-weight:bold;  margin-bottom:25px;  margin-top:20px; ' cellpadding = 1px>
	<td  style='font-size: 16px; font-weight:bold; vertical-align:middle;'><span style='color:#000;'> ".__('Entity', 'dashboard').": </span>".$ent_name['name']." </td>
	<td  style='font-size: 16px; font-weight:bold; vertical-align:middle;'><span style='color:#000;'> ".__('Tickets', 'dashboard').": </span>".$consulta." </td>
	<td colspan='3' style='font-size: 16px; font-weight:bold; vertical-align:middle; width:200px;'><span style='color:#000;'>
	".__('Period', 'dashboard') .": </span> " . conv_data($data_ini2) ." a ". conv_data($data_fin2)." 
	</td>
	<td>&nbsp;</td>
</table>

<table id='ticket' class='display'  style='font-size: 11px; font-weight:bold;' cellpadding = 2px>
	<thead>
		<tr>
			<th style='font-size: 12px; font-weight:bold; text-align: center; cursor:pointer;'> ".__('Tickets', 'dashboard')." </th>
			<th style='font-size: 12px; font-weight:bold; text-align: center; cursor:pointer;'> ".__('Status')." </th>
			<th style='font-size: 12px; font-weight:bold; text-align: center; cursor:pointer;'> ".__('Type')." </th>
			<th style='font-size: 12px; font-weight:bold; text-align: center; cursor:pointer;'> ".__('Source')." </th>
			<th style='font-size: 12px; font-weight:bold; text-align: center; cursor:pointer;'> ".__('Priority')." </th>
			<th style='font-size: 12px; font-weight:bold; text-align: center; cursor:pointer;'> ".__('Category')." </th>
			<th style='font-size: 12px; font-weight:bold; text-align: center; cursor:pointer;'> ".__('Title')." </th>
			<th style='font-size: 12px; font-weight:bold; text-align: center; cursor:pointer;'> ".__('Requester')." </th>
			<th style='font-size: 12px; font-weight:bold; text-align: center; cursor:pointer;'> ".__('Technician')." </th>
			".$th_sol."
			<th style='font-size: 12px; font-weight:bold; text-align: center; cursor:pointer;'> ".__('Opened','dashboard')."</th>
			<th style='font-size: 12px; font-weight:bold; text-align: center; cursor:pointer;'> ".__('Closed')." </th>
		</tr>
	</thead>
<tbody>";


while($row = $DB->fetch_assoc($result_cham)){
	
	$status1 = $row['status']; 

	if($status1 == "1" ) { $status1 = "new";} 
	if($status1 == "2" ) { $status1 = "assign";} 
	if($status1 == "3" ) { $status1 = "plan";} 
	if($status1 == "4" ) { $status1 = "waiting";} 
	if($status1 == "5" ) { $status1 = "solved";}  	            
	if($status1 == "6" ) { $status1 = "closed";}	
	
	//type
	if($row['type'] == 1) { $type = __('Incident'); }
	else { $type = __('Request'); }
	
	//priority
	$prio = $row['priority'];
	
	if($prio == "1" ) { $pri = _x('priority', 'Very low');} 
	if($prio == "2" ) { $pri = _x('priority', 'Low');} 
	if($prio == "3" ) { $pri = _x('priority', 'Medium');} 
	if($prio == "4" ) { $pri = _x('priority', 'High');} 
	if($prio == "5" ) { $pri = _x('priority', 'Very high');} 
	if($prio == "6" ) { $pri = _x('priority', 'Major');} 
	
	//requerente	
	$sql_user = "SELECT glpi_tickets.id AS id, glpi_tickets.name AS descr, glpi_users.firstname AS name, glpi_users.realname AS sname
	FROM `glpi_tickets_users` , glpi_tickets, glpi_users
	WHERE glpi_tickets.id = glpi_tickets_users.`tickets_id`
	AND glpi_tickets.id = ". $row['id'] ."
	AND glpi_tickets_users.`users_id` = glpi_users.id
	AND glpi_tickets_users.type = 1
	";
	$result_user = $DB->query($sql_user);
			
	$row_user = $DB->fetch_assoc($result_user);
				
	//tecnico	
	$sql_tec = "SELECT glpi_tickets.id AS id, glpi_users.firstname AS name, glpi_users.realname AS sname
	FROM `glpi_tickets_users` , glpi_tickets, glpi_users
	WHERE glpi_tickets.id = glpi_tickets_users.`tickets_id`
	AND glpi_tickets.id = ". $row['id'] ."
	AND glpi_tickets_users.`users_id` = glpi_users.id
	AND glpi_tickets_users.type = 2 ";
	
	$result_tec = $DB->query($sql_tec);	
	
	$row_tec = $DB->fetch_assoc($result_tec);
		
		
	//origem	
	$sql_req = "SELECT glpi_tickets.id AS id, glpi_requesttypes.name AS name
	FROM `glpi_tickets` , glpi_requesttypes
	WHERE glpi_tickets.requesttypes_id = glpi_requesttypes.`id`
	AND glpi_tickets.id = ". $row['id'] ." ";
	
	$result_req = $DB->query($sql_req);	
	
	$row_req = $DB->fetch_assoc($result_req);
		
		
	//categoria	
	$sql_cat = "SELECT glpi_tickets.id AS id, glpi_itilcategories.name AS name
	FROM `glpi_tickets` , glpi_itilcategories
	WHERE glpi_tickets.itilcategories_id = glpi_itilcategories.`id`
	AND glpi_tickets.id = ". $row['id'] ." ";
	
	$result_cat = $DB->query($sql_cat);	
	
	$row_cat = $DB->fetch_assoc($result_cat);

if($id_sol1 == '1') { 
		
echo "	
	<tr>
		<td style='vertical-align:middle; text-align:center;'><a href=".$CFG_GLPI['root_doc']."/front/ticket.form.php?id=". $row['id'] ." target=_blank >" . $row['id'] . "</a></td>
		<td style='vertical-align:middle;'><img src=".$CFG_GLPI['root_doc']."/pics/".$status1.".png title='".Ticket::getStatus($row['status'])."' style=' cursor: pointer; cursor: hand;'/>&nbsp; ".Ticket::getStatus($row['status'])."</td>
		<td style='vertical-align:middle;'> ". $type ." </td>
		<td style='vertical-align:middle;'> ". $row_req['name'] ." </td>
		<td style='vertical-align:middle;'> ". $pri ." </td>
		<td style='vertical-align:middle;'> ". $row_cat['name'] ." </td>		
		<td style='vertical-align:middle;'> ". substr($row_user['descr'],0,55) ." </td>
		<td style='vertical-align:middle;'> ". $row_user['name'] ." ".$row_user['sname'] ." </td>
		<td style='vertical-align:middle;'> ". $row_tec['name'] ." ".$row_tec['sname'] ." </td>
		<td style='vertical-align:middle;'> ". strip_tags(htmlspecialchars_decode($row['solution'])) ."</td>
		<td style='vertical-align:middle;'> ". conv_data_hora($row['date']) ." </td>
		<td style='vertical-align:middle;'> ". conv_data_hora($row['solvedate']) ." </td>		
	</tr>";
	}

else{ 
		
echo "	
	<tr>
		<td style='vertical-align:middle; text-align:center;'><a href=".$CFG_GLPI['root_doc']."/front/ticket.form.php?id=". $row['id'] ." target=_blank >" . $row['id'] . "</a></td>
		<td style='vertical-align:middle;'><img src=".$CFG_GLPI['root_doc']."/pics/".$status1.".png title='".Ticket::getStatus($row['status'])."' style=' cursor: pointer; cursor: hand;'/>&nbsp; ".Ticket::getStatus($row['status'])." </td>
		<td style='vertical-align:middle;'> ". $type ." </td>
		<td style='vertical-align:middle;'> ". $row_req['name'] ." </td>
		<td style='vertical-align:middle;text-align:center;'> ". $pri ." </td>
		<td style='vertical-align:middle;'> ". $row_cat['name'] ." </td>		
		<td style='vertical-align:middle;'> ". substr($row_user['descr'],0,55) ." </td>
		<td style='vertical-align:middle;'> ". $row_user['name'] ." ".$row_user['sname'] ." </td>
		<td style='vertical-align:middle;'> ". $row_tec['name'] ." ".$row_tec['sname'] ." </td>
		<td style='vertical-align:middle;'> ". conv_data_hora($row['date']) ." </td>
		<td style='vertical-align:middle;'> ". conv_data_hora($row['solvedate']) ." </td>		
	</tr>";
	}

}	

echo "</tbody>
		</table>
		</div>"; 	
?>

<script type="text/javascript" charset="utf-8">

$('#ticket')
	.removeClass( 'display' )
	.addClass('table table-striped table-bordered table-hover');

$(document).ready(function() {
    oTable = $('#ticket').dataTable({
        "bJQueryUI": true,
        "sPaginationType": "full_numbers",
        "bFilter": false,
        "aaSorting": [[0,'desc']], 
        "iDisplayLength": 25,
    	  "aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]], 

        "sDom": 'CT<"clear">lfrtip',
                
			"aoColumnDefs": [
         	{ "bVisible": false, "aTargets": [ <?php echo $targ; ?> ] }
            ],         
        
         "oTableTools": {
         "aButtons": [
             {
                 "sExtends": "copy",
                 "sButtonText": "<?php echo __('Copy'); ?>"
             },
             {
                 "sExtends": "print",
                 "sButtonText": "<?php echo __('Print','dashboard'); ?>",
                 "sMessage": "<div id='print' class='info_box row-fluid span12' style='margin-bottom:35px; margin-left: -1px;'><table id='print_tb' class='row-fluid'  style='width: 80%; margin-left: 10%; font-size: 18px; font-weight:bold;' cellpadding = '1px'><td colspan='2' style='font-size: 16px; font-weight:bold; vertical-align:middle;'><span style='color:#000;'> <?php echo __('Entity'); ?> : </span><?php echo $ent_name['name']; ?> </td> <td colspan='2' style='font-size: 16px; font-weight:bold; vertical-align:middle;'><span style='color:#000;'> <?php echo  __('Tickets','dashboard'); ?> : </span><?php echo $consulta ; ?></td><td colspan='2' style='font-size: 16px; font-weight:bold; vertical-align:middle; width:200px;'><span style='color:#000;'> <?php echo  __('Period','dashboard'); ?> : </span> <?php echo conv_data($data_ini2); ?> a <?php echo conv_data($data_fin2); ?> </td> </table></div>"
             },
             {
                 "sExtends":    "collection",
                 "sButtonText": "<?php echo __('Export'); ?>",
                 "aButtons":    [ "csv", "xls",
                  {
                 "sExtends": "pdf",
                 "sPdfOrientation": "landscape",
                 "sPdfMessage": ""
                  } 
                  ]
             }
         	 ]
        },
                   "oLanguage": {
                     "sSearch": "<?php echo __('Search all columns:'); ?>"
                 		},
                   colVis: {
                   	"buttonText": "<?php echo __('Show/hide columns','dashboard'); ?>",
        				 	"restore": "<?php echo __('Restore'); ?>",
         				"showAll": "<?php echo __('Show all'); ?>",
         				"exclude": [0]     
     						},
                 "bSortCellsTop": true,
                 "sAlign": "right"
		  
    });    
} );		
</script>  

<?php

echo '</div><br>';
}

else {
	
	echo "
	<div id='nada_rel' class='well info_box row-fluid col-md-12'>
	<table class='table' style='font-size: 18px; font-weight:bold;' cellpadding = 1px>
	<tr><td style='vertical-align:middle; text-align:center;'> <span style='color: #000;'>" . __('No ticket found', 'dashboard') . "</td></tr>
	<tr></tr>
	</table></div>";	

}	
}
?>

<script type='text/javascript' >
	$(document).ready(function() { $("#sel_ent").select2(); });
	$(document).ready(function() { $("#sel_sta").select2(); });
	$(document).ready(function() { $("#sel_req").select2(); });
	$(document).ready(function() { $("#sel_pri").select2(); });
	$(document).ready(function() { $("#sel_cat").select2(); });
	$(document).ready(function() { $("#sel_tip").select2(); });
	$(document).ready(function() { $("#sel_sol").select2(); });
</script>	

</div>
</div>
</div>
</div>

</body> 
</html>

