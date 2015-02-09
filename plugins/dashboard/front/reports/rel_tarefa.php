<?php

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");
include (GLPI_ROOT . "/config/config.php");
include "../inc/functions.php";

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

if(!isset($_POST["sel_tec"])) {
    $id_tec = $_GET["tec"];
}

else {
    $id_tec = $_POST["sel_tec"];
}



# entity
$sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$_SESSION['glpiID']."";
$result_e = $DB->query($sql_e);
$sel_ent = $DB->result($result_e,0,'value');

if($sel_ent == '' || $sel_ent == -1) {
	$sel_ent = 0;
	$entidade = "";
	$entidade_t = "";
	$entidade_tw = "";
	$entidade_u = "";
}
else {
	$entidade = "AND glpi_tickets.entities_id = ".$sel_ent." ";
	$entidade_t = "AND entities_id = ".$sel_ent." ";
	$entidade_tw = "WHERE entities_id = ".$sel_ent." ";
	$entidade_u = "AND glpi_users.entities_id = ".$sel_ent." ";
}

?>

<html>
<head>
<title> GLPI - <?php echo _n('Task','Tasks',2) .'  '. __('by Technician', 'dashboard') ?> </title>
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

<style type="text/css">	
	select { width: 60px; }
	table.dataTable { empty-cells: show; }
   a:link, a:visited, a:active { text-decoration: none;}
</style>

<?php echo '<link rel="stylesheet" type="text/css" href="../css/style-'.$_SESSION['style'].'">';  ?> 

</head>

<body style="background-color: #e5e5e5; margin-left:0%;">
<?php

$sql_tec = "
SELECT  glpi_users.`id` AS id , glpi_users.`firstname` AS name, glpi_users.`realname` AS sname
FROM `glpi_users`, glpi_tickets_users
WHERE glpi_tickets_users.users_id = glpi_users.id
AND glpi_tickets_users.type = 2
AND glpi_users.is_deleted = 0
AND glpi_users.is_active = 1
".$entidade_u."
ORDER BY name ASC ";

$result_tec = $DB->query($sql_tec);
$tec = $DB->fetch_assoc($result_tec);

?>
<div id='content' >
<div id='container-fluid' style="margin: 0px 5% 0px 5%;">

<div id="charts" class="row-fluid chart" >
<div id="pad-wrapper" >
<div id="head-rel" class="row-fluid">

<style type="text/css">
a:link, a:visited, a:active {
    text-decoration: none
    }
a:hover {
    color: #000099;
    }
</style>

<a href="../index.php"><i class="fa fa-home" style="font-size:14pt; margin-left:25px;"></i><span></span></a>

    <div id="titulo"> <?php echo _n('Task','Tasks',2) .'  '. __('by Technician','dashboard') ?>  </div>

    <div id="datas-tec" class="span12 row-fluid" >
    <form id="form1" name="form1" class="form_rel" method="post" action="rel_tarefa.php?con=1">
    <table border="0" cellspacing="0" cellpadding="3" bgcolor="#efefef">
    <tr>
<td style="width: 310px;">
<?php
$url = $_SERVER['REQUEST_URI'];
$arr_url = explode("?", $url);
$url2 = $arr_url[0];

echo '
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

<td style="margin-top:2px;">
<?php

// lista de técnicos
$res_tec = $DB->query($sql_tec);
$arr_tec = array();
$arr_tec[0] = "-- ". __('Select a technician', 'dashboard') . " --" ;

$DB->data_seek($result_tec, 0);

while ($row_result = $DB->fetch_assoc($result_tec))
    {
    $v_row_result = $row_result['id'];
    $arr_tec[$v_row_result] = $row_result['name']." ".$row_result['sname'] ;
    }

$name = 'sel_tec';
$options = $arr_tec;
$selected = 0;

echo dropdown( $name, $options, $selected );

?>
</td>
</tr>
<tr><td height="15px"></td></tr>
<tr>
	<td colspan="2" align="center">
		<button class="btn btn-primary btn-sm" type="submit" name="submit" value="Atualizar" ><i class="fa fa-search"></i>&nbsp; <?php echo __('Consult', 'dashboard'); ?></button>
		<button class="btn btn-primary btn-sm" type="button" name="Limpar" value="Limpar" onclick="location.href='<?php echo $url2 ?>'" > <i class="fa fa-trash-o"></i>&nbsp; <?php echo __('Clean', 'dashboard'); ?> </button></td>
	</td>
</tr>

    </table>
<?php Html::closeForm(); ?>
<!-- </form> -->

        </div>
    </div>
</div>

<script type="text/javascript" >
	$(document).ready(function() { $("#sel1").select2(); });
</script>

<?php

if(isset($_GET['con'])) {

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

if(!isset($_POST["sel_tec"])) {
    $id_tec = $_GET["tec"];
}

else {
    $id_tec = $_POST["sel_tec"];
}

if($id_tec == 0) {
	echo '<script language="javascript"> alert(" ' . __('Select a technician', 'dashboard') . ' "); </script>';
	echo '<script language="javascript"> location.href="rel_tarefa.php"; </script>';
}

if($data_ini2 === $data_fin2) {
    $datas2 = "LIKE '".$data_ini2."%'";
}

else {
    $datas2 = "BETWEEN '".$data_ini2." 00:00:00' AND '".$data_fin2." 23:59:59'";
}

// Chamados

$sql_cham =
"SELECT glpi_tickets.id AS id, glpi_tickettasks.taskcategories_id AS tipo, glpi_tickettasks.date AS date, glpi_tickettasks.content,
glpi_tickettasks.users_id, glpi_tickettasks.actiontime, glpi_tickettasks.begin AS begin, glpi_tickettasks.end
FROM `glpi_tickets` , glpi_tickettasks
WHERE glpi_tickets.id = glpi_tickettasks.`tickets_id`
AND glpi_tickettasks.users_id_tech = ". $id_tec ."
AND glpi_tickets.is_deleted = 0
AND glpi_tickettasks.date ". $datas2 ."
".$entidade."

ORDER BY id DESC, begin ASC ";

$result_cham = $DB->query($sql_cham);


$consulta1 =
"SELECT glpi_tickets.id AS id, glpi_tickettasks.taskcategories_id AS tipo, glpi_tickettasks.date AS date, glpi_tickettasks.content,
glpi_tickettasks.users_id, glpi_tickettasks.actiontime, glpi_tickettasks.begin, glpi_tickettasks.end
FROM `glpi_tickets` , glpi_tickettasks
WHERE glpi_tickets.id = glpi_tickettasks.`tickets_id`
AND glpi_tickettasks.users_id_tech = ". $id_tec ."
AND glpi_tickets.is_deleted =0
AND glpi_tickettasks.date ". $datas2 ."
".$entidade."

ORDER BY id DESC
";

$result_cons1 = $DB->query($consulta1);
$conta_cons = $DB->numrows($result_cons1);
$consulta = $conta_cons;


if($consulta > 0) {

//nome e total
$sql_nome = "
SELECT `firstname` , `realname`, `name`
FROM `glpi_users`
WHERE `id` = ".$id_tec." ";

$result_nome = $DB->query($sql_nome) ;


//total time of tasks
while($row = $DB->fetch_assoc($result_cons1)){
    $tempo_total += $row['actiontime'];
}

//table thread
while($row = $DB->fetch_assoc($result_nome)){

	$tech = $row['firstname'] ." ". $row['realname'];
	
	echo "
	<div class='well info_box row-fluid col-md-12 report' style='margin-left: -1px;'>
	
	<table class='row-fluid'  style='font-size: 18px; font-weight:bold; margin-bottom: 30px;' cellpadding = 1px>
	<tr>
	<td style='vertical-align:middle; width:40%;'> <span style='color: #000;'>".__('Technician', 'dashboard').": </span>  ". $row['firstname'] ." ". $row['realname']. "</td>
	<td style='vertical-align:middle;'> <span style='color: #000;'>"._n('Task', 'Tasks',2).": </span>". $conta_cons ."</td>
	<td style='vertical-align:middle;'> <span style='color: #000;'>".__('Time').": </span>". time_ext($tempo_total) ."</td>
	<td colspan='3' style='font-weight:bold; vertical-align:middle; width:200px;'><span style='color:#000;'>
".__('Period', 'dashboard') .": </span> " . conv_data($data_ini2) ." a ". conv_data($data_fin2)."
	</td>
	</tr>
	</table>
	
	<table id='tarefa' class='display' style='font-size: 13px; font-weight:bold;' cellpadding = 2px>
		<thead>
			<tr>
				<th style='text-align:center; cursor:pointer;'> ". __('Ticket') ."  </th>
				<th style='text-align:center; cursor:pointer;'> ". __('Date') ." </th>
				<th style='text-align:center; cursor:pointer;'> ". __('Description') ."</th>
				<th style='text-align:center; cursor:pointer;'> ". __('Duration') ." </th>
				<th style='text-align:center; cursor:pointer;'> ". __('Begin') ." </th>
				<th style='text-align:center; cursor:pointer;'> ". __('End') ."  </th>
			</tr>
		</thead>
	<tbody>
	";
}

//listar chamados

$DB->data_seek($result_cham, 0);
while($row = $DB->fetch_assoc($result_cham)){
	
	echo "
	<tr>
	<td style='text-align:center;'><a href=".$CFG_GLPI['root_doc']."/front/ticket.form.php?id=". $row['id'] ." target=_blank >" . $row['id'] . "</a></td>
	<td style='text-align:center;'> ". conv_data_hora($row['date']) ." </td>
	<td style='max-width:450px;'> ". $row['content'] ." </td>
	<td> ". time_ext($row['actiontime']) ."</td>
	<td style='text-align:center;'> ". conv_data_hora($row['begin']) ."</td>
	<td style='text-align:center;'> ". conv_data_hora($row['end']) ."</td>
	</tr>";
}

echo "</tbody>
		</table>
		</div>"; ?>

<script type="text/javascript" charset="utf-8">

$('#tarefa')
	.removeClass( 'display' )
	.addClass('table table-striped table-bordered table-hover');

$(document).ready(function() {
    oTable = $('#tarefa').dataTable({
        "bJQueryUI": true,
        "sPaginationType": "full_numbers",
        "bFilter": false,
        "aaSorting": [[0,'desc']], 
        "iDisplayLength": 25,
    	  "aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]], 

        "sDom": 'T<"clear">lfrtip',
         "oTableTools": {
         "aButtons": [
             {
                 "sExtends": "copy",
                 "sButtonText": "<?php echo __('Copy'); ?>"
             },
             {
                 "sExtends": "print",
                 "sButtonText": "<?php echo __('Print','dashboard'); ?>",
                 "sMessage": "<div id='print' class='info_box row-fluid span12' style='margin-bottom:35px; margin-left: -1px;'><table id='print_tb' class='row-fluid'  style='width: 80%; margin-left: 10%; font-size: 18px; font-weight:bold;' cellpadding = '1px'><td colspan='2' style='font-size: 16px; font-weight:bold; vertical-align:middle;'><span style='color:#000;'> <?php echo __('Technician','dashboard'); ?> : </span><?php echo $tech; ?> </td> <td colspan='2' style='font-size: 16px; font-weight:bold; vertical-align:middle;'><span style='color:#000;'> <?php echo  _n('Task','Tasks',2); ?> : </span><?php echo $conta_cons ; ?></td><td colspan='2' style='font-size: 16px; font-weight:bold; vertical-align:middle; width:200px;'><span style='color:#000;'><?php echo __('Time'); ?></span> : <?php echo time_ext($tempo_total); ?></td><td colspan='2' style='font-size: 16px; font-weight:bold; vertical-align:middle; width:200px;'><span style='color:#000;'> <?php echo  __('Period','dashboard'); ?> : </span> <?php echo conv_data($data_ini2); ?> a <?php echo conv_data($data_fin2); ?> </td> </table></div>"
             },
             {
                 "sExtends":    "collection",
                 "sButtonText": "<?php echo __('Export'); ?>",
                 "aButtons":    [ "csv", "xls",
                  {
                 "sExtends": "pdf",
                 "sPdfOrientation": "landscape",
                 "sPdfMessage": ""
                  } ]
             }
         ]
        }
		  
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
		</table>
	</div>";

}
}
}
?>

</div>
</div>
</div>
</body>
</html>

