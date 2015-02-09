<?php

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");
include (GLPI_ROOT . "/config/config.php");

Session::checkLoginUser();
Session::checkRight("profile", READ);

global $DB;
   
    switch (date("m")) {
    case "01": $mes = __('January','dashboard'); break;
    case "02": $mes = __('February','dashboard'); break;
    case "03": $mes = __('March','dashboard'); break;
    case "04": $mes = __('April','dashboard'); break;
    case "05": $mes = __('May','dashboard'); break;
    case "06": $mes = __('June','dashboard'); break;
    case "07": $mes = __('July','dashboard'); break;
    case "08": $mes = __('August','dashboard'); break;
    case "09": $mes = __('September','dashboard'); break;
    case "10": $mes = __('October','dashboard'); break;
    case "11": $mes = __('November','dashboard'); break;
    case "12": $mes = __('December','dashboard'); break;
    }

?>

<html> 
<head>
<title>GLPI - <?php echo __('Tickets','dashboard'). " " .__('by Date','dashboard'); ?></title>
<!-- <base href= "<?php $_SERVER['SERVER_NAME'] ?>" > -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="content-language" content="en-us" /> 

<link rel="icon" href="../img/dash.ico" type="image/x-icon" />
<link rel="shortcut icon" href="../img/dash.ico" type="image/x-icon" />
<link href="../css/styles.css" rel="stylesheet" type="text/css" />
<link href="../css/bootstrap.css" rel="stylesheet" type="text/css" />
<link href="../css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
<link href="../css/font-awesome.css" type="text/css" rel="stylesheet" />
<link href="../css/datepicker.css" rel="stylesheet" type="text/css">
<link href="../less/datepicker.less" rel="stylesheet" type="text/css">

<script type="text/javascript" src="../js/jquery.min.js"></script> 
<script src="../js/highcharts.js"></script>
<script src="../js/modules/exporting.js"></script>
<script src="../js/modules/no-data-to-display.js"></script>
<script src="../js/bootstrap-datepicker.js"></script>

<?php echo '<link rel="stylesheet" type="text/css" href="../css/style-'.$_SESSION['style'].'">';  ?>
<?php echo '<script src="../js/themes/'.$_SESSION['charts_colors'].'"></script>'; ?>

</head>

<body style="background-color:#e5e5e5; margin-left:0%;">

<?php

if(!empty($_POST['submit']))
{	
	$data_ini =  $_POST['date1'];
	
	$data_fin = $_POST['date2'];
}

else {
	$data_ini = date("Y-m-01");
	$data_fin = date("Y-m-d");
} 

$ano = date("Y");
$month = date("Y-m");
$datahoje = date("Y-m-d");

# entity
$sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$_SESSION['glpiID']."";
$result_e = $DB->query($sql_e);
$sel_ent = $DB->result($result_e,0,'value');

if($sel_ent == '' || $sel_ent == -1) {
	$sel_ent = 0;
	$entidade = "";
	$problem = "";
}
else {
	$entidade = "AND glpi_tickets.entities_id = ".$sel_ent." ";
	$problem =  "AND glpi_problems.entities_id = ".$sel_ent." ";
}

?>

<div id='content' >
<div id='container-fluid' style="margin: 0px 8% 0px 8%;"> 

<div id="pad-wrapper" >
<div id="charts" class="row-fluid chart"> 
<div id="head" class="row-fluid">

<a href="../index.php"><i class="fa fa-home" style="font-size:14pt; margin-left:25px;"></i><span></span></a>

<div id="titulo" style="margin-bottom: 2px;">

<?php echo __('Tickets','dashboard') ." ". __('by Date','dashboard');  ?> 

<div id="datas" class="col-md-12 row-fluid" > 
<form id="form1" name="form1" class="form1" method="post" action="?date1=<?php echo $data_ini ?>&date2=<?php echo $data_fin ?>" onsubmit="datai();dataf();"> 
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
		</tr>
		<tr height="12px" ><td></td></tr>
		<tr align="center">			
			<td>
				<button class="btn btn-primary btn-sm" type="submit" name="submit" value="Atualizar" ><i class="fa fa-search"></i>&nbsp; <?php echo __('Consult','dashboard'); ?> </button>
				<button class="btn btn-primary btn-sm" type="button" name="Limpar" value="Limpar" onclick="location.href='<?php echo $url2 ?>'" ><i class="fa fa-trash-o"></i>&nbsp; <?php echo __('Clean','dashboard'); ?> </button>
			</td>
		</tr>
	</table>
<p>
</p>
<?php Html::closeForm(); ?>
<!-- </form> -->
</div>

</div>
<!-- DIV's -->

 </div>

<div id="graf_linhas" class="span12" style="height: 450px; margin-left: -5px; margin-top: -120px;">
	<?php  include ("./inc/graflinhas_sat_geral_mes.inc.php"); ?>
</div>

<div id="graf2" class="span6" >
	<?php include ("./inc/grafpie_stat_geral_mes.inc.php"); ?>
</div>

<div id="graf4" class="span6" >
	<?php  include ("./inc/grafpie_origem_mes.inc.php");  ?>
</div>

<div id="graf_tipo" class="span12" style="margin-top: 35px;">
	<?php include ("./inc/grafcol_tipo_geral_mes.inc.php");  ?>
</div>

<div>
	<?php include ("./inc/grafent_geral_mes.inc.php"); ?>
</div>
<!--
<div id="grafhour" class="row-fluid span12" style="margin-top: 35px; margin-left:-0.8%;">
	<?php include ("./inc/grafbar_ticket_hour.inc.php"); ?>	
</div>

<div id="grafday" class="row-fluid span12" style="margin-top: 35px; margin-left:-0.8%;">
	<?php include ("./inc/grafbar_ticket_day.inc.php"); ?>	
</div>
-->
<div id="grafcat"  class="span12 row-fluid" style="margin-top:35px; margin-left: -10px;">
	<?php include ("./inc/grafcat_geral_mes.inc.php"); ?>
</div>

<div id="grafgrp" class="span12 row-fluid" style="height: 450px; margin-top:35px; margin-left: -10px;">
	<?php  include ("./inc/grafbar_grupo_geral_mes.inc.php"); ?>
</div>


</div>
</div>
</div>
</body> </html>
