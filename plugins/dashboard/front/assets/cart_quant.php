<?php


$query_os = "
SELECT glpi_cartridgeitems.id, glpi_cartridgeitems.name AS name, glpi_cartridgeitems.ref, COUNT( glpi_cartridges.cartridgeitems_id ) AS conta
FROM `glpi_cartridges` , glpi_cartridgeitems
WHERE glpi_cartridgeitems.is_deleted =0
AND glpi_cartridgeitems.id = glpi_cartridges.cartridgeitems_id
GROUP BY glpi_cartridges.cartridgeitems_id
ORDER BY `conta` DESC
";

$result_os = $DB->query($query_os) or die('erro');


$arr_grf_os = array();

while ($row_result = $DB->fetch_assoc($result_os))	
	{ 
	$v_row_result = $row_result['name'];
	$arr_grf_os[$v_row_result] = $row_result['conta'];			
	} 
	
$grf_os2 = array_keys($arr_grf_os);
$quant_os2 = array_values($arr_grf_os);

$conta_os = count($arr_grf_os);

if($conta_os != 0) {

echo ' 
<table cellpadding="0" cellspacing="0" border="0" class="display" id="tb_cart">
	<thead>
		<tr>
		<th>'. __('Name').'</th>
		<th>'. __('Reference').'</th>
		<th>'. __('Total').'</th>
		<th>'. __('Used','dashboard').'</th>
		</tr>
	</thead>
	<tbody>'; 		

$DB->data_seek($result_os,0);
while ($row_result = $DB->fetch_assoc($result_os))	
{		

$id = $row_result['id'];

$query = "
SELECT glpi_cartridgeitems.id, glpi_cartridgeitems.name AS name, COUNT( glpi_cartridges.cartridgeitems_id ) AS conta
FROM `glpi_cartridges` , glpi_cartridgeitems
WHERE glpi_cartridgeitems.is_deleted =0
AND glpi_cartridgeitems.id = glpi_cartridges.cartridgeitems_id
AND glpi_cartridges.printers_id <>0
AND glpi_cartridges.cartridgeitems_id = ".$id."
GROUP BY glpi_cartridges.cartridgeitems_id
ORDER BY `conta` DESC ";

		
$result = $DB->query($query) or die('erro');

while ($row = $DB->fetch_assoc($result))
{
	echo '<tr>
			<td><a href=../../../../front/cartridgeitem.form.php?id='.$id.' target="_blank"  style="color:#555555;" >'. $row['name'].'</a></td>
			<td>'. $row_result['ref'].'</td>
			<td>'. $row_result['conta'].'</td>
			<td>'. $row['conta'].'</td>
			</tr>';		
}
}

echo '		
	</tbody>
</table>';
}

?>

<script type="text/javascript" >
$(document).ready(function() {
    oTable = $('#tb_cart').dataTable({
        "bJQueryUI": true,
        "sPaginationType": "full_numbers",
        "bFilter":false,
        "aaSorting": [[2,'desc'], [0,'asc']],
        
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
