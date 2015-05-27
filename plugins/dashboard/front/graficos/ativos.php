<?php

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");
include (GLPI_ROOT . "/config/config.php");

Session::checkLoginUser();
Session::checkRight("profile", READ);

$mydate = isset($_POST["date1"]) ? $_POST["date1"] : "";

?>

<html> 
<head>
<title>GLPI - <?php echo __('Tickets') .'  '. __('by Assets','dashboard').'s' ?></title>
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
<link href="../inc/calendar/calendar.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="../js/jquery.min.js"></script> 
<script src="../js/highcharts.js"></script>
<script src="../js/modules/exporting.js"></script>

<script src="../js/bootstrap-datepicker.js"></script>
<link href="../css/datepicker.css" rel="stylesheet" type="text/css">
<link href="../less/datepicker.less" rel="stylesheet" type="text/css">

<link href="../inc/select2/select2.css" rel="stylesheet" type="text/css">
<script src="../inc/select2/select2.js" type="text/javascript" language="javascript"></script>

<?php echo '<link rel="stylesheet" type="text/css" href="../css/style-'.$_SESSION['style'].'">';  ?> 
<?php echo '<script src="../js/themes/'.$_SESSION['charts_colors'].'"></script>'; ?>

<style type="text/css">
#select2-chosen-1 { color: #555; }
.select2-chosen { color: #555; }
</style>

</head>

<body style="background-color: #e5e5e5; margin-left:0%;">

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

$month = date("Y-m");
$datahoje = date("Y-m-d");  	  
?>

<div id='content' >
<div id='container-fluid' style="margin: 0px 8% 0px 8%;"> 
<div id="pad-wrapper" >

<div id="charts" class="row-fluid chart"> 
<div id="head" class="row-fluid">

	<a href="../index.php"><i class="fa fa-home" style="font-size:14pt; margin-left:25px;"></i><span></span></a>

	<div id="titulo" style="margin-bottom:45px;"> <?php echo __('Tickets') .'  '. __('by Assets','dashboard');  ?>  
	
		<div id="datas" class="span12 row-fluid" style="margin-left:-25px;"> 
		
			<form id="form1" name="form1" class="form1" method="post" action="?con=1&date1=<?php echo $data_ini ?>&date2=<?php echo $data_fin ?>" style="width:360px;"> 
				<table id="table_form1" border="0" cellspacing="0" cellpadding="2" width="350px">
				<tr>
					<td style="width: 300px;">			
					<?php			    
					echo'
					<table style="margin-top:16px;" border=0>
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
				<tr height="12px" ><td></td></tr>
				<tr>
					<td style="margin-top:0px; width:100px;">
					
					<?php echo __('Type').":  
					
					<select id='sel_item' name='sel_item' style='color:#000; width: 300px; height: 27px;' autofocus onChange='javascript: document.form1.submit.focus()' >
						<option value='0'> -- ".__('Select a asset','dashboard')." -- </option>
						<option value='1'>".__('Computer')."</option>
						<option value='2'>".__('Monitor')."</option>
						<option value='3'>".__('Software')."</option>
						<option value='4'>".__('Network')."</option>
						<option value='5'>".__('Device')."</option>
						<option value='6'>".__('Printer')."</option>
						<option value='7'>".__('Phone')."</option>
					</select> ";	
					
					?>
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr height="12px" ><td></td></tr>
				<tr align="center">
					<td>
						<button class="btn btn-primary btn-sm" type="submit" name="submit" value="Atualizar" ><i class="fa fa-search"></i>&nbsp; <?php echo __('Consult','dashboard'); ?> </button>
						<button class="btn btn-primary btn-sm" type="button" name="Limpar" value="Limpar" onclick="location.href='ativos.php'" ><i class="fa fa-trash-o"></i>&nbsp; <?php echo __('Clean','dashboard'); ?> </button>
					</td>
				</tr>
				</table>
				<p></p>
			<?php Html::closeForm(); ?>
		
		</div>
	</div>
</div>

<div id="graf1" class="row-fluid">
<?php 

if(isset($_REQUEST['con']) && $_REQUEST['con'] == 1 ) {
	
	if(isset($_REQUEST['sel_item']) && $_REQUEST['sel_item'] == '0' ) {
		//$type = $_REQUEST['itemtype'];
		echo '<script language="javascript"> alert(" ' . __('Select a asset','dashboard') . ' "); </script>';		
		 
		}
	
	else {	

		$itemtype = $_REQUEST['sel_item'];

		switch ($itemtype) {
	    case "1": $type = 'computer'; break;
	    case "2": $type = 'monitor'; break;
	    case "3": $type = 'software'; break;
	    case "4": $type = 'networkequipment'; break;
	    case "5": $type = 'peripheral'; break;
	    case "6": $type = 'printer'; break;
	    case "7": $type = 'phone'; break;
	} 
}

include ("./inc/grafbar_ativo_mes.inc.php");

}
?>
</div>

</div>

<script type="text/javascript" >
	$(document).ready(function() { $("#sel_item").select2(); });
</script>


</div>
</div>
</div>
</body> </html>
