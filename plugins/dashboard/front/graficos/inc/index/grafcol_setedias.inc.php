<?php


$data_inis = date("Y-m-d");  //hoje

$data_fins = date('Y-m-d', strtotime('-6 days'));

$sql_tecd = "
SELECT DATE_FORMAT(date, '%Y-%m-%d') as data, COUNT(id) as conta 
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = 0
AND glpi_tickets.date BETWEEN '" . $data_fins ." 00:00:00' AND '".$data_inis." 23:59:59'
". $entidade ."
GROUP BY data
ORDER BY data ASC ";

$query_tecd = $DB->query($sql_tecd);



//REQUEST
$sql_tec = "
SELECT DATE_FORMAT(date, '%Y-%m-%d') as data, COUNT(id) as conta 
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = 0
AND glpi_tickets.type = 2
AND glpi_tickets.date BETWEEN '" . $data_fins ." 00:00:00' AND '".$data_inis." 23:59:59'
". $entidade ."
GROUP BY data
ORDER BY data ASC ";

$query_tec = $DB->query($sql_tec);


//INCIDENT
$sql_teci = "
SELECT DATE_FORMAT(date, '%Y-%m-%d') as data, COUNT(id) as conta 
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = 0
AND glpi_tickets.type = 1
AND glpi_tickets.date BETWEEN '" . $data_fins ." 00:00:00' AND '".$data_inis." 23:59:59'
". $entidade ."
GROUP BY data
ORDER BY data ASC ";

$query_teci = $DB->query($sql_teci);


echo "<script type='text/javascript'>

$(function () {
        $('#graf7').highcharts({
            chart: {
                type: 'column',
                height: 310,
                plotBorderColor: '#ffffff',
            	 plotBorderWidth: 0 ,
               
            },
            title: {
               // text: '". __('Tickets')." - ". __('Last 7 days','dashboard') ."'
               text: ''
            },
            subtitle: {
                text: ''
            },
            xAxis: { 
                        	 
            type: 'datetime',
            dateTimeLabelFormats: {
            day: '%e - %b'
            },
            	 
            formatter: function() 
         		{
               return ''+ Highcharts.numberFormat(this.x, 0);
         		},
           
            categories: [ ";

while ($ticket = $DB->fetch_assoc($query_tecd)) {

	$date=date_create($ticket['data']);

	 switch ($_SESSION['glpidate_format']) {
    case "0": $dataf = $date->format('M-d'); break;
    case "1": $dataf = $date->format('d-M'); break;
    case "2": $dataf = $date->format('M-d'); break;    
    } 

	echo "'" . $dataf. "',";

}   

//zerar rows para segundo while
//$DB->data_seek($query_tec, 0) ;               

echo "    ],
             title: {
                 text: ''
             },
             labels: {
             	style: {
                     fontSize: '11px',
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
	            headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
	            pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}: </td>' +
	                '<td style=\"padding:0\"><b>{point.y:.0f} </b></td></tr>',
	            footerFormat: '</table>',
	            shared: true,
	            useHTML: true
	        },
            plotOptions: {
                column: {
                    dataLabels: {
                        enabled: true,                                                
                    },
                  borderWidth: 2,
                	borderColor: 'white',
                	shadow:true,           
                	showInLegend: true
                }
            },
				legend: {
	            layout: 'vertical',
	            align: 'right',
	            verticalAlign: 'middle',
	            borderWidth: 0
	        },
            credits: {
                enabled: false
            },
            series: [
                {
                name: '". __('Request') ."',
                data: [ ";
   
					$DB->data_seek($query_tec, 0);     
					             
					while ($ticket = $DB->fetch_assoc($query_tec)) 
					{
						echo $ticket['conta'].",";
					}    
					
					echo "]},
					
					{
                name: '". __('Incident') ."',
                data: [ ";
   
					//$DB->data_seek($query_tec, 0) ;     
					             
					while ($ticketi = $DB->fetch_assoc($query_teci)) 
					{
						echo $ticketi['conta'].",";
					}    
					
					echo "]


            }]
        });
    });

</script>
";
		
		?>
