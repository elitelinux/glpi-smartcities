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
    $data_ini = date("Y-01-01");
    $data_fin = date("Y-m-d");
    }

if(!isset($_POST["sel_pro"])) {
    $id_pro = $_GET["pro"];
}

else {
    $id_pro = $_POST["sel_pro"];
}

# entity
$sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$_SESSION['glpiID']."";
$result_e = $DB->query($sql_e);
$sel_ent = $DB->result($result_e,0,'value');

if($sel_ent == '' || $sel_ent == -1) {
	$sel_ent = 0;
	$entidade = "";	
}
else {
	$entidade = "AND glpi_projects.entities_id IN (".$sel_ent.") ";
}

?>

<html>
<head>
<title> GLPI - <?php echo _n('Project task', 'Project tasks',2); ?> </title>
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

    <div id="titulo"> <?php echo _n('Project task', 'Project tasks',2); ?>  </div>

    <div id="datas-tec3" class="span12 row-fluid" >
    <form id="form1" name="form1" class="form_rel" method="post" action="./rel_projects.php?con=1" style="margin-left: 37%;">
	    <table border="0" cellspacing="0" cellpadding="3" bgcolor="#efefef">
		    <tr>
				<td style="width: 310px;">
				<?php
				$url = $_SERVER['REQUEST_URI'];
				$arr_url = explode("?", $url);
				$url2 = $arr_url[0];
				?>				
				<script language="Javascript">
					$('#dp1').datepicker('update');
					$('#dp2').datepicker('update');
				</script>				
				</td>				
				<td style="margin-top:2px;"></td>
			</tr>
			<tr><td height="15px"></td></tr>
			<tr></tr>	
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

if(isset($_GET['sel_pro'])) {

$pro_id = $_GET['sel_pro'];

//if($con == "1") {

if(!isset($_POST['date1']))
{
    $data_ini2 = $_GET['date1'];
    $data_fin2 = $_GET['date2'];
}

else {
    $data_ini2 = $_POST['date1'];
    $data_fin2 = $_POST['date2'];
}

if($data_ini2 === $data_fin2) {
    $datas2 = "LIKE '".$data_ini2."%'";
}

else {
    $datas2 = "BETWEEN '".$data_ini2." 00:00:00' AND '".$data_fin2." 23:59:59'";
}


//Project
$sql_pro =
"SELECT id, name
FROM glpi_projects
WHERE glpi_projects.id = ".$pro_id."
".$entidade."
ORDER BY date DESC ";

$result_pro = $DB->query($sql_pro);
$project = $DB->result($result_pro,0,'name');

// tasks
$sql_cham =
"SELECT * 
FROM glpi_projecttasks
WHERE glpi_projecttasks.projects_id = ".$pro_id."
".$entidade."
ORDER BY date DESC ";

$result_cham = $DB->query($sql_cham);

$conta_cons = $DB->numrows($result_cham);
$consulta = $conta_cons;

//tempo de todas as tarefas
while($row = $DB->fetch_assoc($result_cham)){
    $tempo_total += $row['effective_duration'];
}
	
	echo "
	<div class='well info_box row-fluid col-md-12 report' style='margin-left: -1px;'>
	
	<table class='row-fluid' style='font-size: 18px; font-weight:bold; margin-bottom: 30px;' cellpadding = 1px>
		<tr>
			<td style='color: #000;'>". __('Project') .":  ". $project ." </td>			
		</tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td style='vertical-align:middle; width:350px;'> <span style='color: #000;'>"._n('Task', 'Tasks',2).": </span>". $conta_cons ."</td>
			<td style='vertical-align:middle; width:350px;'> <span style='color: #000;'>".__('Time').": </span>". time_ext($tempo_total) ."</td>			
		</tr>
	</table>";

	echo "
	<table id='tarefa' class='display' style='font-size: 13px; font-weight:bold;' cellpadding = 2px>
		<thead>
			<tr>
				<th style='text-align:center; cursor:pointer;'> ". __('ID') ."  </th>				
				<th style='text-align:center; cursor:pointer;'> ". __('Name') ."  </th>
				<th style='text-align:center; cursor:pointer;'> ". __('Technician') ."  </th>
				<th style='text-align:center; cursor:pointer;'> ". __('Creation date') ." </th>				
				<th style='text-align:center; cursor:pointer;'> ". __('Begin') ."</th>
				<th style='text-align:center; cursor:pointer;'> ". __('End') ." </th>
				<th style='text-align:center; cursor:pointer;'> ". __('Duration') ." </th>		
				<th style='text-align:center; cursor:pointer;'> ". __('Progress') ."</th>						
			</tr>
		</thead>
	<tbody>
	";

//listar projetos

$DB->data_seek($result_cham, 0);
while($row = $DB->fetch_assoc($result_cham)){
	
	//percent done		
	$barra = $row['percent_done'];
	
	// cor barra
	if($barra == 100) { $cor = "progress-bar-success"; }
	if($barra >= 80 and $barra < 100) { $cor = " "; }
	if($barra > 51 and $barra < 80) { $cor = "progress-bar-warning"; }
	if($barra > 0 and $barra <= 50) { $cor = "progress-bar-danger"; }
	if($barra < 0) { $cor = "progress-bar-danger"; $barra = 0; }
	

	echo "
	<tr>
	<td style='text-align:center; vertical-align:middle;'><a href=".$CFG_GLPI['url_base']."/front/projecttask.form.php?id=". $row['id'] ." target=_blank >" . $row['id'] . "</a></td>		
	<td style='text-align:center; vertical-align:middle;'> ". $row['name'] ." </td>
	<td style='text-align:center; vertical-align:middle;'> ". getUserName($row['users_id']) ." </td>				
	<td style='text-align:center; vertical-align:middle;'> ". conv_data_hora($row['date']) ."</td>
	<td style='text-align:center; vertical-align:middle;'> ". conv_data_hora($row['real_start_date']) ."</td>
	<td style='text-align:center; vertical-align:middle;'> ". conv_data_hora($row['real_end_date']) ."</td>
	<td style='text-align:center; vertical-align:middle;'> ". time_ext($row['effective_duration']) ."</td>
	<td style='text-align:center; vertical-align:middle;'> 
		<div class='progress' style='margin-top: 5px; margin-bottom: 5px;'>
			<div class='progress-bar ". $cor ." progress-bar-striped active' role='progressbar' aria-valuenow='".$barra."' aria-valuemin='0' aria-valuemax='100' style='width: ".$barra."%;'>
			 			".$barra." % 	
			 </div>		
		</div>			
	</td>
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
        "aaSorting": [[0,'asc']], 
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
                 "sMessage": "<div id='print' class='info_box row-fluid span12' style='margin-bottom:35px; margin-left: -1px;'><table id='print_tb' class='row-fluid'  style='width: 80%; margin-left: 10%; font-size: 18px; font-weight:bold;' cellpadding = '1px'><td colspan='2' style='font-size: 16px; font-weight:bold; vertical-align:middle;'><span style='color:#000;'>  </td> <td colspan='2' style='font-size: 16px; font-weight:bold; vertical-align:middle;'><span style='color:#000;'> <?php echo  _n('Task','Tasks',2); ?> : </span><?php echo $conta_cons ; ?></td><td colspan='2' style='font-size: 16px; font-weight:bold; vertical-align:middle; width:200px;'><span style='color:#000;'><?php echo __('Time'); ?></span> : <?php echo time_ext($tempo_total); ?></td><td colspan='2' style='font-size: 16px; font-weight:bold; vertical-align:middle; width:200px;'><span style='color:#000;'> <?php echo  __('Period','dashboard'); ?> : </span> <?php echo conv_data($data_ini2); ?> a <?php echo conv_data($data_fin2); ?> </td> </table></div>"
             },
             {
                 "sExtends":    "collection",
                 "sButtonText": "<?php echo _x('button', 'Export'); ?>",
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
	</table></div>";

}
//}
//}
?>

</div>
</div>
</div>
</body>
</html>

