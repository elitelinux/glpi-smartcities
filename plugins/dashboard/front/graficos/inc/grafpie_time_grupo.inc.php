
<?php

if($data_ini == $data_fin) {
$datas = "LIKE '".$data_ini."%'";	
}	

else {
$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";	
}

$query_grp = "
SELECT ggt.groups_id AS gid, count( ggt.tickets_id ) AS quant
FROM glpi_groups_tickets ggt, glpi_tickets gt
WHERE ggt.type = 1
AND gt.is_deleted = 0
AND gt.closedate IS NOT NULL
AND ggt.tickets_id = gt.id
AND gt.solvedate ".$datas."
AND gt.entities_id = ".$id_ent."
GROUP BY ggt.groups_id
ORDER BY quant DESC
LIMIT 0, 10 ";

$result_grp = $DB->query($query_grp);


$arr_grft2 = array();

while ($row = $DB->fetch_assoc($result_grp)) {
	
	//tickets by type
	$query2 = "
	SELECT gg.completename AS gname, sum( gt.solve_delay_stat) AS time
	FROM glpi_groups_tickets ggt, glpi_tickets gt, glpi_groups gg
	WHERE ggt.groups_id = ".$row['gid']."
	AND ggt.type = 1
	AND ggt.groups_id = gg.id
	AND gt.is_deleted = 0
	AND closedate IS NOT NULL
	AND gt.id = ggt.tickets_id ";
	
	$result2 = $DB->query($query2) or die('erro');
	
	$row_result = $DB->fetch_assoc($result2);		
		 			
			$v_row_result = $row_result['gname'];
			$arr_grft2[$v_row_result] =  round($row_result['time'], 3);		
		
	$grft2 = array_keys($arr_grft2);	
	$quantt2 = array_values($arr_grft2);
			 		
}

	$conta = count($arr_grft2);
	
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
                text: '".__('Time spent by requester group','dashboard')."'
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
								format: '{point.y} h - ( {point.percentage:.1f}% )',
                   		style: {
                        	color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        		}
                        //connectorColor: 'black'
                    			},
                showInLegend: true
                }
            },
            series: [{
                type: 'pie',
                name: '".__('Tickets','dashboard')."',
                data: [  ";     
                                    
	for($i = 0; $i < $conta; $i++) { 
		$date1 = date('H.i',mktime(0,0,$quantt2[$i])) ;
		if(date('H:i',mktime(0,0,$quantt2[$i])) != 0) {
	     echo '[ "' . $grft2[$i] . '", '. date('H',mktime(0,0,$quantt2[$i])) .'],';   
//	     echo '[ "' . $grft2[$i] . '", '. (int)$date1 .'],';
//		  echo '[ "' . $grft2[$i] . '", '. $quantt2[$i] .'],';
				}
	     }                    
                                                         
echo "                ],
            }]
        });
    });

		</script>"; 	

//echo $quantt2[0];
/*		
$segundos = 687955;
//$converter = date('H:i:s',mktime(0,0,$segundos,15,03,2013));//Converter os segundos em no formato mm:ss
$converter = date('H:i:s',mktime(0,0,$segundos));//Converter os segundos em no formato mm:ss
echo $converter;//no exemplo ira retornar 02:15			
*/					
		?>
