<?php

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");
include (GLPI_ROOT . "/config/config.php");

global $DB;

$ano = date("Y");
$month = date("Y-m");
$hoje = date("Y-m-d");
$thismonth = date("Y-m-01");
$thisyear = date("Y-01-01");

$yesterday = date('Y-m-d', strtotime('-1 days'));
$lastweek = date('Y-m-d', strtotime('-7 days'));
$lastmonth = date('Y-m-d', strtotime('-30 days'));
$last2month = date('Y-m-d', strtotime('-60 days'));
$datai_m2 = date('Y-m-d', strtotime('-60 days'));
$dataf = date('Y-m-d', strtotime('-365 days'));

// time period for metrics
/*
0 = all
1 = year
2 = this month
3 = 30 days
4 = 60 days
5 = 7 days
*/
//$sel_period = 3;

$sql_met = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'metric' AND users_id = ".$_SESSION['glpiID']."";
$result_met = $DB->query($sql_met);
$sel_period = $DB->result($result_met,0,'value');


switch ($sel_period) {
    case 0:
        $period = '';
        $periodp = ''; 
        break;
    case 1:
        $period = "AND glpi_tickets.date BETWEEN '".$thisyear." 00:00:00' AND '".$hoje." 23:59:59'";
        $periodp = "AND glpi_problems.date BETWEEN '".$thisyear." 00:00:00' AND '".$hoje." 23:59:59'";
        break;
    case 2:
        $period = "AND glpi_tickets.date BETWEEN '".$thismonth." 00:00:00' AND '".$hoje." 23:59:59'";
        $periodp = "AND glpi_problems.date BETWEEN '".$thismonth." 00:00:00' AND '".$hoje." 23:59:59'";
        break;
    case 3:
        $period = "AND glpi_tickets.date BETWEEN '".$lastmonth." 00:00:00' AND '".$hoje." 23:59:59'";
        $periodp = "AND glpi_problems.date BETWEEN '".$lastmonth." 00:00:00' AND '".$hoje." 23:59:59'";
        break;    
    case 4:
        $period = "AND glpi_tickets.date BETWEEN '".$last2month." 00:00:00' AND '".$hoje." 23:59:59'";
        $periodp = "AND glpi_problems.date BETWEEN '".$last2month." 00:00:00' AND '".$hoje." 23:59:59'";
        break;               
}


// entity
$sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$_SESSION['glpiID']."";
$result_e = $DB->query($sql_e);
$sel_ent = $DB->result($result_e,0,'value');

if($sel_ent == '' || $sel_ent == -1) {
	//get user entities
	$entities = Profile_User::getUserEntities($_SESSION['glpiID'], true);
	$ent = implode(",",$entities);

	$entidade = "AND glpi_tickets.entities_id IN (".$ent.")";	
	$ent_problem = "AND glpi_problems.entities_id IN (".$ent.")";
}

else {
	$entidade = "AND glpi_tickets.entities_id IN (".$sel_ent.")";
	$ent_problem =  "AND glpi_problems.entities_id IN (".$sel_ent.")";
}


//chamados ano
$sql_ano =	"SELECT COUNT(glpi_tickets.id) as total        
      FROM glpi_tickets
      LEFT JOIN glpi_entities ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.is_deleted = '0'  
      AND DATE_FORMAT( glpi_tickets.date, '%Y' ) IN (".$ano.")
      $entidade ";

$result_ano = $DB->query($sql_ano);
$total_ano = $DB->fetch_assoc($result_ano);
  

//chamados mes
$sql_mes =	"SELECT COUNT(glpi_tickets.id) as total        
      FROM glpi_tickets
      LEFT JOIN glpi_entities ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.date LIKE '$month%'      
      AND glpi_tickets.is_deleted = '0'  
      $period
		$entidade ";

$result_mes = $DB->query($sql_mes);
$total_mes = $DB->fetch_assoc($result_mes);

  
//ticktes by month
$querym = "
SELECT DISTINCT DATE_FORMAT( date, '%b-%y' ) AS month_l, COUNT( id ) AS nb, DATE_FORMAT( date, '%y-%m' ) AS MONTH
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = '0'
$period
$entidade
GROUP BY MONTH
ORDER BY MONTH ASC ";

$resultm = $DB->query($querym) or die('erro');

$arr_grfm = array();
while ($row_result = $DB->fetch_assoc($resultm))		
	{ 
		$v_row_result = $row_result['month_l'];
		$arr_grfm[$v_row_result] = $row_result['nb'];			
	} 
	
$grfm = array_keys($arr_grfm) ;
$quantm = array_values($arr_grfm) ;

$grfm2 = implode("','",$grfm);
$grfm3 = "'$grfm2'";
$quantm2 = implode(',',$quantm);

$opened = array_sum($quantm);


//Ticktes by day
$queryd = "
SELECT DISTINCT DATE_FORMAT(date, '%b-%d') as day_l,  COUNT(id) as nb, DATE_FORMAT(date, '%Y-%m-%d') as day
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = '0'
AND glpi_tickets.date BETWEEN '".$lastmonth." 00:00:00' AND '".$hoje." 23:59:59'
GROUP BY day
ORDER BY day";

$resultd = $DB->query($queryd) or die('erro_day');

$arr_day = array();
while ($row_result = $DB->fetch_assoc($resultd))		
	{ 
		$v_row_result = $row_result['day_l'];
		$arr_day[$v_row_result] = $row_result['nb'];			
	} 
	
$grfd = array_keys($arr_day) ;
$quantd = array_values($arr_day) ;

$grfd2 = implode("','",$grdd);
$grfd3 = "'$grfd2'";
$quantd2 = implode(',',$quantd);

$bydays = array_sum($quantd);


//resolution time
$query2 = "
SELECT count( id ) AS chamados , DATEDIFF( solvedate, date ) AS days
FROM glpi_tickets
WHERE solvedate IS NOT NULL
AND is_deleted = 0
$period
$entidade
GROUP BY days ";
		
$result2 = $DB->query($query2) or die('erro');


$arr_grf2 = array();
while ($row_result = $DB->fetch_assoc($result2))		
	{ 
		$v_row_result = $row_result['days'];
		$arr_grf2[$v_row_result] = $row_result['chamados'];			
	} 
	
$grf2 = array_keys($arr_grf2);
$quant2 = array_values($arr_grf2);

$conta = count($arr_grf2);

for($i=0; $i < 7; $i++) {

	if($quant2[$i] != 0) {
		$till[$i] = $quant2[$i];
	}
	else {
		$till[$i] = 0;
	}	
	
	$arr_days[] += $till[$i];
}

$res_days = implode(',',$arr_days);
//$res_days = $arr_days;


// SLA time
$sql_cham = 
"SELECT glpi_tickets.id AS id, glpi_tickets.name AS descr, glpi_tickets.date AS date, glpi_tickets.solvedate as solvedate, 
glpi_tickets.status, glpi_tickets.due_date AS duedate, sla_waiting_duration AS slawait, glpi_tickets.type,
FROM_UNIXTIME( UNIX_TIMESTAMP( `glpi_tickets`.`solvedate` ) , '%Y-%m' ) AS date_unix, AVG( glpi_tickets.solve_delay_stat ) AS time
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = 0
$period
$entidade
GROUP BY id DESC
ORDER BY id DESC ";

$result_cham = $DB->query($sql_cham);
$conta_cons = $DB->numrows($result_cham);

// Count overdue tickets
$v = 0;
while($row = $DB->fetch_assoc($result_cham)){
	
	if($row['solvedate'] > $row['duedate'] && $row['slawait'] == 0) {
		$v = $v+1;
	}
} 

// Count within tickets
$w = $conta_cons - $v;

$gauge_val = round(($w*100)/$conta_cons,2);


//STATUS
$query_sta = "
SELECT COUNT(glpi_tickets.id) as tick, glpi_tickets.status as stat
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = 0
AND status NOT IN (5,6)       
$period      
$entidade 
GROUP BY glpi_tickets.status
ORDER BY tick DESC ";
		
$result_sta = $DB->query($query_sta) or die('erro_stat');

$arr_sta = array();
while ($row_result = $DB->fetch_assoc($result_sta))		
	{ 
	   $v_row_result = Ticket::getStatus($row_result['stat']);
	   $arr_sta[$v_row_result] = $row_result['tick'];			
	} 
	
$grf_sta = array_keys($arr_sta);
$quant_sta = array_values($arr_sta);

$conta = count($arr_sta);

$sta_labels = implode(',',$grf_sta);
$sta_values = implode(',',$quant_sta);


//Tickets type
//incident - request
$query_type = "SELECT count(id) AS quant, type
	FROM glpi_tickets
	WHERE is_deleted = 0
	$period
	$entidade
	GROUP BY type
	ORDER BY type ASC"; 

$res_type = $DB->query($query_type);

$inc = $DB->result($res_type,0,'quant');
$req = $DB->result($res_type,1,'quant');

$inc_label = $DB->result($res_type,0,'type');
$req_label = $DB->result($res_type,1,'type');

if($inc_label == "1") {
	$inc_label = __('Incident');
	$req_label = __('Request');
}
else {
	$inc_label = __('Request');	
	$req_label = __('Incident');
}

//problems	
$query_prob = "SELECT count(id) AS quant
	FROM glpi_problems
	WHERE is_deleted = 0
	$periodp
	$ent_problem ";

$res_prob = $DB->query($query_prob);

$prob = $DB->result($res_prob,0,'quant');
$prob_label = __('Problem');


//$array_type = array();
$types = array();

$array_type[0] = $req;
$array_type[1] = $inc;
$array_type[2] = $prob;

$types = implode(",",$array_type);

$array_label[0] = $req_label;
$array_label[1] = $inc_label;
$array_label[2] = $prob_label;

$rag_labels = implode(",",$array_label);


//Satisfaction
$query_sat =
"SELECT avg( `glpi_ticketsatisfactions`.satisfaction ) AS media
	FROM glpi_tickets, `glpi_ticketsatisfactions`
	WHERE glpi_tickets.is_deleted = '0'
	AND `glpi_ticketsatisfactions`.tickets_id = glpi_tickets.id
	AND glpi_ticketsatisfactions.satisfaction <> 'NULL'
	$period
	$entidade ";

$result_sat = $DB->query($query_sat) or die('erro_sat');

$sat = $DB->result($result_sat,0,'media');

$satisf = round(($sat*100/5),1);



	//count by status
	$query_stat = "
	SELECT 
	SUM(case when glpi_tickets.status = 1 then 1 else 0 end) AS new,
	SUM(case when glpi_tickets.status = 2 then 1 else 0 end) AS assig,
	SUM(case when glpi_tickets.status = 3 then 1 else 0 end) AS plan,
	SUM(case when glpi_tickets.status = 4 then 1 else 0 end) AS pend,
	SUM(case when glpi_tickets.status = 5 then 1 else 0 end) AS solve,
	SUM(case when glpi_tickets.status = 6 then 1 else 0 end) AS close
	FROM glpi_tickets
	WHERE glpi_tickets.is_deleted = '0' 
	$period
	$entidade ";
	
	$result_stat = $DB->query($query_stat);
	
	$new = $DB->result($result_stat,0,'new');
	$assig = $DB->result($result_stat,0,'assig');
	$plan = $DB->result($result_stat,0,'plan');
	$pend = $DB->result($result_stat,0,'pend');
	$solved = $DB->result($result_stat,0,'solve');
	$closed = $DB->result($result_stat,0,'close'); 
	
	$assigned = $assig + $plan;
	$total = $new + $assig + $plan + $pend + $solved;
	
	
	//by status yesterday
	$query_staty = "
	SELECT 
	SUM(case when glpi_tickets.status = 1 then 1 else 0 end) AS new,
	SUM(case when glpi_tickets.status = 2 then 1 else 0 end) AS assig,
	SUM(case when glpi_tickets.status = 3 then 1 else 0 end) AS plan,
	SUM(case when glpi_tickets.status = 4 then 1 else 0 end) AS pend,
	SUM(case when glpi_tickets.status = 5 then 1 else 0 end) AS solve,
	SUM(case when glpi_tickets.status = 6 then 1 else 0 end) AS close
	FROM glpi_tickets
	WHERE glpi_tickets.is_deleted = '0'	
	AND glpi_tickets.date BETWEEN '" . $last_week ." 00:00:00' AND '". $yesterday." 23:59:59' ";
	
	$result_staty = $DB->query($query_staty);
	
	$newy = $DB->result($result_staty,0,'new');
	$assigy = $DB->result($result_staty,0,'assig');
	$plany = $DB->result($result_staty,0,'plan');
	$pendy = $DB->result($result_staty,0,'pend');
	$solvedy = $DB->result($result_staty,0,'solve');
	$closedy = $DB->result($result_staty,0,'close'); 
	
	$assignedy = $assigy + $plany;
	$totaly = $newy + $assigy + $plany + $pendy + $solvedy;


	
	function percent($data,$datay) {
	
		$diff = $data/$datay;		
		
		if($diff > 1 && $diff != 0) {
			$perc = ($diff-1)*100;	
			echo "<span style='color:#66ce39 !important; '>". round($perc,2) ." %</span>" ;					
		}
		
		if($diff < 1 && $diff != 0) {
			$perc = (1-$diff)*100;
			echo "<span style='color:#f23c25 !important;'>". round($perc,2) ." %</span>" ;
		}		

		if($diff == 1 ) {
			$perc = 0;
			echo "<span>". round($perc,2) ." %</span>" ;
		}	
		return round($perc,2);
	}


?>