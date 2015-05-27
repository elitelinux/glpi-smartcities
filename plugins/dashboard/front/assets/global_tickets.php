<?php


$query_os = "
SELECT glpi_items_tickets.itemtype AS tipo, glpi_items_tickets.`items_id` AS id , COUNT(*) AS conta
FROM glpi_items_tickets
WHERE glpi_items_tickets.`itemtype` <> ''
AND glpi_items_tickets.`items_id` > 0
AND glpi_items_tickets.`itemtype` <> 'PluginProjetProjet'
".$ent_global."
GROUP BY glpi_items_tickets.`itemtype` , glpi_items_tickets.`items_id`
ORDER BY conta DESC
LIMIT 100 ";

$result_os = $DB->query($query_os) or die('erro');


$arr_grf_os = array();

while ($row_result = $DB->fetch_assoc($result_os))	
	{ 
	$v_row_result = $row_result['tipo'];
	$arr_grf_os[$v_row_result] = $row_result['conta'];			
	} 
	
$grf_os2 = array_keys($arr_grf_os);
$quant_os2 = array_values($arr_grf_os);

$conta_os = count($arr_grf_os);


echo ' 
<table cellpadding="0" cellspacing="0" border="0" class="display" id="a_tickets">
	<thead>
		<tr>
		<th>'. __('Assets').'</th>
		<th>'. __('Type').'</th>
		<th>'. __('Tickets').'</th>
		</tr>
	</thead>
	<tbody>'; 		

$DB->data_seek($result_os,0);
while ($row_result = $DB->fetch_assoc($result_os))	
{		

$tipo = strtolower($row_result['tipo']);
$name = strtolower($row_result['tipo'])."s";
$id = $row_result['id'];

$query = "
SELECT name AS name
FROM glpi_".$name."
WHERE id = ".$id." ";

		
$result = $DB->query($query) or die('erro');

while ($row = $DB->fetch_assoc($result))
{
	echo '<tr>
			<td><a href=../../../../front/'.$tipo.'.form.php?id='.$id.' target="_blank"  style="color:#555555;" >'. $row['name'].'</a></td>
			<td>'. __($row_result['tipo']).'</td>
			<td>'. $row_result['conta'].'</td>
			</tr>';		
}
}

echo '		
	</tbody>
</table>';


?>

<script type="text/javascript" >
$(document).ready(function() {
    oTable = $('#a_tickets').dataTable({
        "bJQueryUI": true,
        "sPaginationType": "full_numbers",
        "bFilter":false,
        "aaSorting": [[2,'desc'], [0,'asc']],
        "aoColumnDefs": [{ "sWidth": "45%", "aTargets": [2] }],
         "sDom": 'T<"clear">lfrtip',
         "oTableTools": {
         "aButtons": [
             {
                 "sExtends": "copy",
                 "sButtonText": "<?php echo __('Copy'); ?>"
             },
             {
                 "sExtends": "print",
                 "sButtonText": "<?php echo __('Print','dashboard'); ?>"
                 
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
