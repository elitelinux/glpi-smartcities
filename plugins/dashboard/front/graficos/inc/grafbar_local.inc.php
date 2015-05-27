<?php

if($data_ini == $data_fin) {
	$datas = "LIKE '".$data_ini."%'";	
}	

else {
	$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";	
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
	$entidade = "AND glpi_tickets.entities_id = ".$sel_ent." ";
}


$sql_tec = "
SELECT count( glpi_tickets.id ) AS conta, glpi_locations.id AS loc_id, glpi_locations.completename AS name
FROM glpi_locations, glpi_tickets
WHERE glpi_tickets.locations_id = glpi_locations.id
AND glpi_tickets.is_deleted = 0
AND glpi_tickets.date ".$datas."
".$entidade."
GROUP BY loc_id
ORDER BY conta DESC ";


$query_tec = $DB->query($sql_tec);


if($DB->fetch_assoc($query_tec) != '') {

echo "
<script type='text/javascript'>

$(function () {	
	
        $('#graf1').highcharts({
            chart: {
                type: 'bar',
                height: 1000
            },
            title: {
                text: ''
            },
            subtitle: {
                text: ''
            },
            xAxis: { 
            categories: [ ";
            
$DB->data_seek($query_tec, 0) ;  
while ($tecnico = $DB->fetch_assoc($query_tec)) {
	
$user_name = $tecnico['name'];
echo "'". $user_name ."',";	
	
}   

//zerar rows para segundo while
$DB->data_seek($query_tec, 0) ;               

echo "    ],
                title: {
                    text: null
                },
                labels: {
                	style: {
                        fontSize: '12px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: '',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                valueSuffix: ''
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: true                                                
                    },
                     borderWidth: 1,
                		borderColor: 'white',
                		shadow:true,           
                		showInLegend: false
                },
                 series: {
			       	  animation: {
			           duration: 2000,
			           easing: 'easeOutBounce'
			       	  }
			  			 }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -40,
                y: 100,
                floating: true,
                borderWidth: 1,
                //backgroundColor: '#FFFFFF',
                shadow: true,
                enabled: false
            },
            credits: {
                enabled: false
            },
            series: [{            	
            	 dataLabels: {
            	 	//color: '#000099'
            	 	},
                name: '". __('Tickets','dashboard')."',
                data: [  
";
             
while ($tecnico = $DB->fetch_assoc($query_tec)) 
{
 echo $tecnico['conta'].",";
}    

echo "]
            }]
        });
    });

</script>
";
		}
		?>
