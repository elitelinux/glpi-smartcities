<?php

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");
include (GLPI_ROOT . "/config/config.php");
include "../inc/functions.php";

global $DB, $con;

Session::checkLoginUser();
Session::checkRight("profile", READ);

if(!empty($_POST['submit']))
	{
   	$data_ini =  $_REQUEST['date1'];
   	$data_fin = $_REQUEST['date2'];
	}

else {
    	$data_ini = date("Y-01-01");
    	$data_fin = date("Y-m-d");
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
<title> GLPI - <?php echo __('Tickets','dashboard') .'  '. __('by Technician','dashboard') ?> </title>
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
/*
#tec th {
	background-color: #373b40;
	color: #fff;
}  */  
</style>

<a href="../index.php"><i class="fa fa-home" style="font-size:14pt; margin-left:25px;"></i><span></span></a>

<div id="titulo_graf" > <?php echo __('Tickets','dashboard') .'  '. __('by Technician','dashboard') ?> </div>

<div id="datas-tec" class="row-fluid" > 
<form id="form1" name="form1" class="form1" method="post" action="rel_tecnicos.php?con=1" onsubmit="datai();dataf();"> 

<table border="0" cellspacing="0" cellpadding="2">
	<tr>
			<td style="width: 300px;">		
			<?php
			    
			echo'
						<table style="margin-top:6px;" border=0>
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
	<tr height="12px" ><td></td></tr>
	<tr align="center">
		<td>
			<button class="btn btn-primary btn-sm" type="submit" name="submit" value="Atualizar" ><i class="fa fa-search"></i>&nbsp; <?php echo __('Consult','dashboard'); ?> </button>
			<button class="btn btn-primary btn-sm" type="button" name="Limpar" value="Limpar" onclick="location.href='rel_tecnicos.php'" ><i class="fa fa-trash-o"></i>&nbsp; <?php echo __('Clean','dashboard'); ?> </button>
		</td>
	</tr>
</table>	
<p>
</p>
<?php Html::closeForm(); ?>
<!-- </form> -->
</div>
</div>

<?php

//tecnico2

if(isset($_GET['con'])) {
	
	$con = $_GET['con'];
	
	//$con = 1;
	if($con == "1") {
	
	if(!isset($_POST['date1']))
		{
		    $data_ini2 = $_REQUEST['date1'];
		    $data_fin2 = $_REQUEST['date2'];
		}
	
	else {
	    $data_ini2 = $_REQUEST['date1'];
	    $data_fin2 = $_REQUEST['date2'];
		}
	
	
	if($data_ini2 === $data_fin2) {
		$datas2 = "LIKE '".$data_ini2."%'";
		}
	
	else {
		$datas2 = "BETWEEN '".$data_ini2." 00:00:00' AND '".$data_fin2." 23:59:59'";
		}


$sql_tec = "
SELECT DISTINCT glpi_users.id AS id , glpi_users.firstname AS fname, glpi_users.realname AS rname, COUNT(glpi_tickets.id) AS chamados
FROM glpi_users , glpi_tickets_users, glpi_tickets
WHERE glpi_tickets_users.users_id = glpi_users.id
AND glpi_tickets.id = glpi_tickets_users.tickets_id
AND glpi_tickets.is_deleted = 0
AND glpi_tickets_users.type = 2
AND glpi_tickets.date ".$datas2."
".$entidade_u."
GROUP BY id
ORDER BY fname ASC ";

$result_tec = $DB->query($sql_tec);

$conta_cons = $DB->numrows($result_tec);

//status
$status = "";
$status_open = "('1','2','3','4')";
$status_closed = "('5','6')";
$status_all = "('1','2','3','4','5','6')";

//check if satisfaction is active
$query_sats = " SELECT * FROM `glpi_ticketsatisfactions` WHERE 1
";

/*
SELECT COUNT(`glpi_ticketsatisfactions`.satisfaction) AS sat
FROM `glpi_ticketsatisfactions`
WHERE glpi_ticketsatisfactions.satisfaction IS NOT NULL
*/

$result_sats = $DB->query($query_sats);
$sats = $DB->fetch_assoc($result_sats);
		
echo "<div class='well info_box row-fluid col-md-12 report' style='margin-left: -1px;'>";

echo "
	<table id='tec' class='display' style='font-size: 13px; font-weight:bold;' cellpadding = 2px >
		<thead>
			<tr>
				<th style='text-align:center; cursor:pointer;'> ". __('Technician','dashboard') ." </th>
				<th style='font-size: 12px; font-weight:bold; text-align: center; cursor:pointer;'> ".__('Tickets')." </th>
				<th style='text-align:center; cursor:pointer;'> ". __('Opened','dashboard') ."</th>								
				<th style='text-align:center; '> ". __('Closed','dashboard') ."</th> ";
	if($sats != '') {					
		echo "<th style='text-align:center; '> ". __('Satisfaction','dashboard') ."</th>";
		}
	echo "	</tr>
		</thead>
	<tbody>";
	

while($id_tec = $DB->fetch_assoc($result_tec)) {	

//abertos
$sql_ab = "SELECT count( glpi_tickets.id ) AS total, glpi_tickets_users.users_id AS id
FROM glpi_tickets_users, glpi_tickets
WHERE glpi_tickets.id = glpi_tickets_users.tickets_id
AND glpi_tickets.date ".$datas2."
AND glpi_tickets_users.users_id = ".$id_tec['id']."
AND glpi_tickets.status NOT IN ".$status_closed."
AND glpi_tickets.is_deleted = 0
".$entidade." " ;

$result_ab = $DB->query($sql_ab) or die ("erro_ab");
$data_ab = $DB->fetch_assoc($result_ab);

$abertos = $data_ab['total'];


//satisfação por tecnico   , glpi_users.firstname AS fname , glpi_users.realname AS rname, glpi_users.name
$query_sat = "
SELECT glpi_users.id, avg( glpi_ticketsatisfactions.satisfaction ) AS media 
FROM glpi_tickets, glpi_ticketsatisfactions, glpi_tickets_users, glpi_users
WHERE glpi_tickets.is_deleted = '0'
AND glpi_ticketsatisfactions.tickets_id = glpi_tickets.id
AND glpi_ticketsatisfactions.tickets_id = glpi_tickets_users.tickets_id
AND glpi_users.id = glpi_tickets_users.users_id
AND glpi_tickets_users.type = 2
AND glpi_tickets.date ".$datas2."
AND glpi_tickets_users.users_id = ".$id_tec['id']." 
".$entidade." ";

$result_sat = $DB->query($query_sat) or die('erro');
$media = $DB->fetch_assoc($result_sat);

$satisfacao = round(($media['media']/5)*100,1);
$nota = round($media['media'],0);


//barra de porcentagem
if($conta_cons > 0) {

if($status == $status_closed ) {
    $barra = 100;
    $cor = "progress-bar-success";
	}

else {

	//porcentagem
	$perc = round(($abertos*100)/$id_tec['chamados'],1);
	$barra = 100 - $perc;
	
	// cor barra
	if($barra == 100) { $cor = "progress-bar-success"; }
	if($barra >= 80 and $barra < 100) { $cor = " "; }
	if($barra > 51 and $barra < 80) { $cor = "progress-bar-warning"; }
	if($barra > 0 and $barra <= 50) { $cor = "progress-bar-danger"; }
	if($barra < 0) { $cor = "progress-bar-danger"; $barra = 0; }

	}
}

else { $barra = 0;}
//fim while	

//echo $id_tec['fname'].' '.$id_tec['rname'].' '.$id_tec['chamados'].' '.$abertos.' '.$satisfacao.'% '.$barra.'%<br>' ;

		echo "
		<tr>
			<td style='vertical-align:middle; text-align:left;'><a href=".$CFG_GLPI['root_doc']."/front/user.form.php?id=". $id_tec['id'] ." target=_blank >" . $id_tec['fname'].' '.$id_tec['rname']. ' ('.$id_tec['id'].")</a></td>
			<td style='vertical-align:middle; text-align:center;'> ".$id_tec['chamados']." </td>
			<td style='vertical-align:middle; text-align:center;'> ". $abertos ." </td>
			<td style='vertical-align:middle; text-align:center;'> 
				<div class='progress' style='margin-top: 5px; margin-bottom: 5px;'>
					<div class='progress-bar ". $cor ." progress-bar-striped active' role='progressbar' aria-valuenow='".$barra."' aria-valuemin='0' aria-valuemax='100' style='width: ".$barra."%;'>
			 			".$barra." % 	
			 		</div>		
				</div>			
		   </td>";
if($sats != '') {	
		echo "<td style='vertical-align:middle; text-align:center;'> 	
					<img src=../img/s". $nota .".png>
				</td>";
			}	
				
	echo "</tr>";
		
//fim while1
}	

echo "</tbody>
		</table>
		</div>"; 
//fim $con
}
}

?>

<script type="text/javascript" charset="utf-8">

$('#tec')
	.removeClass( 'display' )
	.addClass('table table-striped table-bordered');

$(document).ready(function() {
    oTable = $('#tec').dataTable({
        "bJQueryUI": true,
        "sPaginationType": "full_numbers",
        "bFilter": false,
        "aaSorting": [[1,'desc']], 
        "iDisplayLength": 15,
    	  "aLengthMenu": [[15, 25, 50, 100, -1], [15, 25, 50, 100, "All"]], 

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
                 "sMessage": "<div id='print' class='info_box row-fluid span12' style='margin-bottom:12px; margin-left: -1px;'></div>"
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

<script type="text/javascript" >
	$(document).ready(function() { $("#sel1").select2(); });
</script>

</div>
</div>

</div>
</div>

</body>
</html>

