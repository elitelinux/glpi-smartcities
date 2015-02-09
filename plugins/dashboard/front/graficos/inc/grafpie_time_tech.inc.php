
<?php

if($data_ini == $data_fin) {
$datas = "LIKE '".$data_ini."%'";	
}	

else {
$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";	
}

$query2 = "
SELECT COUNT(glpi_tickets.id) as tick, glpi_tickets_users.users_id AS uid
FROM glpi_tickets_users, glpi_tickets
WHERE glpi_tickets.is_deleted = '0'
AND glpi_tickets.date ".$datas."
AND glpi_tickets_users.users_id = ".$_SESSION['glpiID']."
AND glpi_tickets_users.type = 2
AND glpi_tickets_users.tickets_id = glpi_tickets.id
AND NOW() > glpi_tickets.due_date

GROUP BY uid
ORDER BY tick DESC    
";
		
$result2 = $DB->query($query2) or die('erro');

$arr_grf2 = array();
while ($row_result = $DB->fetch_assoc($result2))		
	{ 
		$v_row_result = $row_result['uid'];
		$arr_grf2[$v_row_result] = $row_result['tick'];			
	} 
	
$grf2 = array_keys($arr_grf2);
$quant2 = array_values($arr_grf2);

$conta = count($arr_grf2);


$query = "
SELECT count(*) AS tick, glpi_tickets_users.users_id AS uid
FROM glpi_tickets_users, glpi_tickets
WHERE glpi_tickets.is_deleted = '0'
AND glpi_tickets.date ".$datas."
AND glpi_tickets_users.users_id = ".$id_tec."
AND glpi_tickets_users.type = 2
AND glpi_tickets_users.tickets_id = glpi_tickets.id 

";
		
$result = $DB->query($query) or die('erro');

$arr_grf = array();
while ($row_result = $DB->fetch_assoc($result))		
	{ 
		$v_row_result = $row_result['uid'];
		$arr_grf[$v_row_result] = $row_result['tick'];			
	} 
	
$grf = array_keys($arr_grf);
$quant = array_values($arr_grf);
	

echo "
<script type='text/javascript'>

$(function () {		
    	   		
		// Build the chart
        $('#graf_time').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '". __('Opened Tickets by Time','dashboard')."'
            },
            tooltip: {
        	    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    size: '85%',
						  dataLabels: {
								format: '{point.y} - ( {point.percentage:.1f}% )',
                   		style: {
                        	color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        		},
                        connectorColor: 'black'
                    },
                showInLegend: true
                }
            },
            series: [{
                type: 'pie',
                name: '".__('Tickets','dashboard')."',
                data: [
                    {
                        name: '".__('Past-due','dashboard')."',
                        y: ".$quant2[0].",
                        sliced: true,
                        selected: true
                    },";
                    
     echo '[ "'.__('On-time','dashboard').'", '. ($quant[0] - $quant2[0]).'],';                          
                                                         
echo "                ]
            }]
        });
    });

		</script>"; 
		?>
