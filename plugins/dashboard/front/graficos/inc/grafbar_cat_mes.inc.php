
<?php

if($data_ini == $data_fin) {
	$datas = "LIKE '".$data_ini."%'";	
}	

else {
	$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";	
}

if($sel_ent == 0) { $limit = "LIMIT 40"; } 
else { $limit = "";}


$sql_tec = "
SELECT count(glpi_tickets.id) AS conta, glpi_itilcategories.name AS name
FROM `glpi_itilcategories`, glpi_tickets
WHERE glpi_tickets.`itilcategories_id` = glpi_itilcategories.id
AND glpi_tickets.is_deleted = 0
AND glpi_tickets.date ".$datas."
". $entidade ."
GROUP BY name
ORDER BY conta DESC
". $limit ." ";

$query_tec = $DB->query($sql_tec);

echo "
<script type='text/javascript'>

$(function () {
        $('#graf1').highcharts({
            chart: {
                type: 'bar',
                height: 800
            },
            title: {
                text: ''
            },
            subtitle: {
                text: ''
            },
            xAxis: { 
            categories: [ ";

while ($entity = $DB->fetch_assoc($query_tec)) {

echo "'". $entity['name']."',";

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
                backgroundColor: '#FFFFFF',
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
                name: '". __('Tickets','dashboard') ."',
                data: [  
";
             
while ($entity = $DB->fetch_assoc($query_tec)) 

{
echo $entity['conta'].",";
}    

echo "]
            }]
        });
    });

</script>
";
		
		?>
