<?php


echo "<script type='text/javascript' src='".$CFG_GLPI['url_base']."/glpi/lib/jquery/js/jquery-1.10.2.min.js'></script>";
//echo "<script type='text/javascript' src='".$CFG_GLPI['url_base']."/plugins/webnotifications/front/js/jquery-1.10.2.min.js'></script>";
echo "<script type='text/javascript' src='".$CFG_GLPI['url_base']."/plugins/webnotifications/front/js/notify.js'></script>";

//echo "<script type='text/javascript' src='".$CFG_GLPI['url_base']."/plugins/webnotifications/front/js/jquery.gritter.js'></script>";
//echo "<link rel=\"stylesheet\" href='".$CFG_GLPI['url_base']."/plugins/webnotifications/front/css/jquery.gritter.css' />";

global $DB;

$sql = "
SELECT COUNT(gt.id) AS total
FROM glpi_tickets_users gtu, glpi_tickets gt
WHERE gtu.users_id = ". $_SESSION['glpiID'] ."
AND gtu.type = 2
AND gt.is_deleted = 0
AND gt.id = gtu.tickets_id" ;

$resulta = $DB->query($sql);
$abertos = $DB->result($resulta,0,'total');

//$abertos = $data; 
$init = $abertos - 1;

$query_u = "
INSERT IGNORE INTO glpi_plugin_webnotifications_count(users_id, quant, type) 
VALUES ('". $_SESSION['glpiID'] ."', '" . $init ."', '0' )  ";

$result_u = $DB->query($query_u);


$query = "SELECT users_id, quant, type 
FROM glpi_plugin_webnotifications_count
WHERE users_id = ". $_SESSION['glpiID'] ."
AND type = 0 " ;

$result = $DB->query($query);

//$user = $DB->result($result,0,'users_id');
$atual = $DB->result($result,0,'quant');
$type = $DB->result($result,0,'type');

$dif = $abertos - $atual;


//update tickets count	
$query_up = "UPDATE glpi_plugin_webnotifications_count
SET quant=". $abertos ."
WHERE users_id = ". $_SESSION['glpiID'] ." 
AND type = 0 ";

$result_up = $DB->query($query_up);
	

if($abertos > $atual) {
				
	if($dif >= 5) { $dif = 5; }
	
	$queryc = 
	"SELECT gt.id AS id, gt.name AS name 
	FROM glpi_tickets_users gtu, glpi_tickets gt
	WHERE gtu.users_id = ". $_SESSION['glpiID'] ."
	AND gtu.type = 2
	AND gt.is_deleted = 0
	AND gt.id = gtu.tickets_id
	ORDER BY id DESC
	LIMIT ".$dif." ";
	
	$res = $DB->query($queryc);		
}	


//followups

$queryn = "SELECT COUNT(gtf.id) AS total
FROM glpi_ticketfollowups gtf, glpi_tickets_users gtu
WHERE gtf.tickets_id =  gtu.tickets_id 
AND gtu.type = 2
AND gtu.users_id = ". $_SESSION['glpiID'] ." ";

$resultf = $DB->query($queryn);

$abertosn = $DB->result($resultf,0,'total');

$initn = $abertosn - 1;

$query_un = "
INSERT IGNORE INTO glpi_plugin_webnotifications_count(users_id, quant, type) 
VALUES ('". $_SESSION['glpiID'] ."', '" . $initn ."', '1' )  ";

$result_un = $DB->query($query_un);


$queryn1 = "SELECT users_id, quant, type 
FROM glpi_plugin_webnotifications_count
WHERE users_id = ". $_SESSION['glpiID'] ."
AND type = 1 " ;

$resultn1 = $DB->query($queryn1);

//$usern = $DB->result($resultn1,0,'users_id');
$atualn = $DB->result($resultn1,0,'quant');
$typen = $DB->result($resultn1,0,'type');

$difn = $abertosn - $atualn;


//update notif count	
$query_upn = "UPDATE glpi_plugin_webnotifications_count
SET quant=". $abertosn ."
WHERE users_id = ". $_SESSION['glpiID'] ." 
AND type = 1 ";

$result_upn = $DB->query($query_upn);


if($abertosn > $atualn) {
				
	if($difn >= 5) { $difn = 5; }
	
	$querycn = 
	"SELECT DISTINCT gt.id AS id, gt.name AS name, gtf.content AS content 
	FROM glpi_tickets_users gtu, glpi_tickets gt, glpi_ticketfollowups gtf
	WHERE gtu.users_id = ".$_SESSION['glpiID']."
	AND gtf.tickets_id =  gtu.tickets_id 
	AND gtu.type = 2
	AND gt.is_deleted = 0
	AND gt.id = gtu.tickets_id
	ORDER BY id DESC
	LIMIT ".$difn." ";
	
	$resn = $DB->query($querycn);
}	


//groups 
$sql1 = "
SELECT ggu.id, ggu.users_id AS user, ggu.groups_id AS grupo, COUNT(ggt.tickets_id) AS total
FROM glpi_groups_users ggu,  glpi_groups_tickets ggt
WHERE `users_id` = ". $_SESSION['glpiID'] ."
AND ggu.groups_id = ggt.groups_id   " ;

$resulta1 = $DB->query($sql1);

while($row = $DB->fetch_assoc($resulta1)) {

	// numero de chamados por grupo
	$sqlg = "
	SELECT count( ggt.tickets_id ) AS total
	FROM glpi_groups_tickets ggt
	WHERE ggt.groups_id = ". $row['grupo'] ." ";
	
	$resultag = $DB->query($sqlg);
	$up_total = $DB->result($resultag,0,'total');
	
	//$DB->data_seek($resg, 0);
	//while($row1 = $DB->fetch_assoc($resultag)) {
	// grupos e numero de chamados
	$query_g = "
	INSERT IGNORE INTO glpi_plugin_webnotifications_count_grp(groups_id, quant, users_id) 
	VALUES ('". $row['grupo'] ."', '" . $up_total ."','" . $_SESSION['glpiID']  ."')  ";
	
	$result_g = $DB->query($query_g);
	//}
	
	$abertosg = $DB->result($resultag,0,'total');
	
	$initg = $abertosg - 1;
	
	
	$queryg = "SELECT groups_id, quant, users_id
	FROM glpi_plugin_webnotifications_count_grp
	WHERE groups_id = ". $row['grupo'] ." 
	AND users_id = ".$_SESSION['glpiID']." ";
	
	$resultg = $DB->query($queryg);
	
	$atualg = $DB->result($resultg,0,'quant');
	
	
	$difg = $abertosg - $atualg;
	
//}
	
	if($abertosg > $atualg) {
					
		if($difg >= 5) { $difg = 5; }
		
		
		$queryc = 
		"SELECT gt.id AS id, gt.name AS name
		FROM glpi_groups_tickets ggt, glpi_tickets gt
		WHERE ggt.groups_id = ". $row['grupo']  ."
		AND gt.is_deleted = 0
		AND gt.id = ggt.tickets_id 
		ORDER BY id DESC
		LIMIT ".$difg." ";
		
		$resg = $DB->query($queryc);		
		
		
		//update tickets count	
		$query_upg = "UPDATE glpi_plugin_webnotifications_count_grp
		SET quant = ". $abertosg ."
		WHERE groups_id = ". $row['grupo'] ." 
		AND users_id = ". $_SESSION['glpiID']  ." ";
	
	   $result_upg = $DB->query($query_upg);
	
	  //	if($abertosg > $atualg) {
		
		$DB->data_seek($resg, 0);
		
		while($row1 = $DB->fetch_assoc($resg)) {
		
			$icon = "../plugins/webnotifications/front/img/icon.png";
			$titulo = __('Group')." - ". __('New ticket');
			$text = __('Ticket').": ".$row1['id']." - ".$row1['name'];
			$id = $row1['id'];
			
			$text2 = __('Ticket').": <a href=".$CFG_GLPI['url_base']."/front/ticket.form.php?id=".$id." style=color:#ffffff;>".$id."</a> - ".$row1['name'];
			
			$id = $row['id'];
			
			$user_agent = $_SERVER['HTTP_USER_AGENT']; 
			
			if (!preg_match('/Chrome/i', $user_agent)) { 
				echo"<script>notify('".$titulo."','".$text."','".$icon."','".$id."');</script>"; 
				echo"<script>notify2('".$titulo."','".$text2."');</script>"; 
			} 
			
			else { 
				echo"<script>notify('".$titulo."','".$text."','".$icon."','".$id."');</script>"; 		
			}	
		}
		}
	//}

}	

	if($abertos > $atual) {

		$DB->data_seek($res, 0);	
			
		while($row = $DB->fetch_assoc($res)) {
		
			$icon = "../plugins/webnotifications/front/images/icon.png";
			$titulo = __('New ticket');
			$text = __('New ticket').": ".$row['id']." - ".$row['name'];
			
			$text2 = __('New ticket').": <a href=".$CFG_GLPI['url_base']."/front/ticket.form.php?id=".$row['id']." style=color:#ffffff;>".$row['id']."</a> - ".$row['name'];
			
			$id = $row['id'];
			
			$user_agent = $_SERVER['HTTP_USER_AGENT']; 
			
			if (!preg_match('/Chrome/i', $user_agent)) { 
				echo"<script>notify('".$titulo."','".$text."','".$icon."','".$id."');</script>"; 
				echo"<script>notify2('".$titulo."','".$text2."');</script>"; 
			} 
			
			else { 
				echo"<script>notify('".$titulo."','".$text."','".$icon."','".$id."');</script>"; 
			
			}
			}			
	}	


//followup
if($abertosn > $atualn) {

$DB->data_seek($resn, 0);
while($row = $DB->fetch_assoc($resn)) {

	$icon = "../plugins/webnotifications/front/img/icon.png";
	$titulo = __('New followup');
	$text = __('Ticket').": ".$row['id']." - ".$row['content'];
	$id = $row['id'];
	
	$text2 = __('Ticket').": <a href=".$CFG_GLPI['url_base']."/front/ticket.form.php?id=".$row['id']." style=color:#ffffff;>".$row['id']."</a> - ".$row['content'];
	
	$id = $row['id'];
	
	$user_agent = $_SERVER['HTTP_USER_AGENT']; 
	
	if (!preg_match('/Chrome/i', $user_agent)) { 
		echo"<script>notify('".$titulo."','".$text."','".$icon."','".$id."');</script>"; 
		echo"<script>notify2('".$titulo."','".$text2."');</script>"; 
	} 
	
	else { 
		echo"<script>notify('".$titulo."','".$text."','".$icon."','".$id."');</script>"; 
	
	}

	}
}

?>	
