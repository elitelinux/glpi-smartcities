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

//select entity
if($sel_ent == '' || $sel_ent == -1) {	

	$query_ent1 = "
	SELECT entities_id
	FROM glpi_users
	WHERE id = ".$_SESSION['glpiID']." ";
	
	$res_ent1 = $DB->query($query_ent1);
	$user_ent = $DB->result($res_ent1,0,'entities_id');

	//get all user entities
	$entities = Profile_User::getUserEntities($_SESSION['glpiID'], true);
	$entities[] = $user_ent;
	$ent = implode(",",$entities);

	$entidade = "AND glpi_projects.entities_id IN (".$ent.") ";
	$entidade1 = "";
	
}
else {
	$entidade = "AND glpi_projects.entities_id IN (".$sel_ent.") ";
}

?>

<html>
<head>
<title> GLPI - <?php echo _n('Project','Projects',2); ?> </title>
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

    <div id="titulo"> <?php echo _n('Project','Projects',2); ?>  </div>
    <div id="datas-tec3" class="span12 row-fluid" >
    <form id="form1" name="form1" class="form_rel" method="post" action="./rel_projects.php?con=1" style="margin-left: 37%;">
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

if($data_ini2 === $data_fin2) {
    $datas2 = "LIKE '".$data_ini2."%'";
}

else {
    $datas2 = "BETWEEN '".$data_ini2." 00:00:00' AND '".$data_fin2." 23:59:59'";
}

// Projetos
$sql_cham =
"SELECT * 
FROM glpi_projects
WHERE glpi_projects.date ". $datas2 ."
".$entidade."
ORDER BY id ASC ";

$result_cham = $DB->query($sql_cham);

$conta_cons = $DB->numrows($result_cham);
$consulta = $conta_cons;
	
	echo "
	<div class='well info_box row-fluid col-md-12 report' style='margin-left: -1px;'>
	
	<table class='row-fluid' style='font-size: 18px; font-weight:bold; margin-bottom: 30px;' cellpadding = 1px>
		<tr>
			<td style='vertical-align:middle; width:350px;'> <span style='color: #000;'>"._n('Project', 'Projects',2).": </span>". $conta_cons ."</td>			
			<td colspan='4' style='font-weight:bold; vertical-align:middle; width:200px;'><span style='color:#000;'>".__('Period', 'dashboard') .": </span> " . conv_data($data_ini2) ." a ". conv_data($data_fin2)."</td>
		</tr>
	</table>";

	echo "
	<table id='tarefa' class='display' style='font-size: 13px; font-weight:bold;' cellpadding = 2px>
		<thead>
			<tr>
				<th style='text-align:center; cursor:pointer;'> ". __('ID') ."  </th>
				<th style='text-align:center; cursor:pointer;'> ". __('Name') ."  </th>
				<th style='text-align:center; cursor:pointer;'> ". __('Status') ." </th>
				<th style='text-align:center; cursor:pointer;'> ". __('% finalizado') ."</th>
				<th style='text-align:center; cursor:pointer;'> ". _n('Task', 'Tasks',2) ." </th>
				<th style='text-align:center; cursor:pointer;'> ". __('Creation date') ." </th>
				<th style='text-align:center; cursor:pointer;'> ". __('Manager') ."  </th>
			</tr>
		</thead>
	<tbody>
	";

//listar projetos

$DB->data_seek($result_cham, 0);
while($row = $DB->fetch_assoc($result_cham)){

	//status
	$sql_stat = "
	SELECT id, name, color
	FROM glpi_projectstates
	WHERE id = ".$row['projectstates_id']." ";
	
	$result_stat = $DB->query($sql_stat) ;
	$row_stat = $DB->fetch_assoc($result_stat);
	
	//tasks
	$sql_task = "
	SELECT COUNT(*) as tasks
	FROM glpi_projecttasks
	WHERE projects_id = ".$row['id']." ";
	
	$result_task = $DB->query($sql_task) ;
	$row_task = $DB->fetch_assoc($result_task);
		
	// bar color
	if($row['percent_done'] == 100) { $cor = "progress-bar-success"; }
	else { $cor = ""; }
	
	
	echo "
	<tr>
	<td style='text-align:center;'><a href=".$CFG_GLPI['root_doc']."/front/project.form.php?id=". $row['id'] ." target=_blank >" . $row['id'] . "</a></td>
	<td style='text-align:center;'> ". $row['name'] ." </td>
	<td style='text-align:center; color:".$row_stat['color'].";'> ". $row_stat['name'] ." </td>
	<td style='text-align:center;'>
		<div class='progress' style='margin-top: 5px; margin-bottom: 5px;'>
			<div class='progress-bar " . $cor . " progress-bar-striped active' role='progressbar' aria-valuenow='".$row['percent_done']."' aria-valuemin='0' aria-valuemax='100' style='width: ".$row['percent_done']."%;'>
			 			".$row['percent_done']." % 	
			</div>		
		</div> </td>
	<td style='text-align:center;'><a href='./rel_projecttasks.php?sel_pro=". $row['id'] ."' target=_self >" . $row_task['tasks'] . "</a></td>
	<td style='text-align:center;'> ". conv_data_hora($row['date']) ."</td>
	<td> ". getUserName($row['users_id']) ." </td>	
	</tr>";
}

//echo 	<td> ". $row_nome['firstname'] ." ".$row_nome['realname']." </td>;

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
        //"aaSorting": [[0,'desc']], 
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
}
//}
?>

</div>
</div>
</div>
</body>
</html>

