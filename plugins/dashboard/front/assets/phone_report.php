<?php

$query2 = "
SELECT id, name, contact, users_id, groups_id, phonetypes_id, phonemodels_id, brand, manufacturers_id, states_id 
FROM glpi_phones
WHERE glpi_phones.is_deleted = 0
AND is_template = 0 
".$ent_phone."
ORDER BY id ASC ";
	
$result2 = $DB->query($query2) or die('erro');
	
echo ' 
<table cellpadding="0" cellspacing="0" border="0" class="display" id="phone_report">
	<thead>
		<tr>
		<th style="color:#555;">'. __('ID').'</th>
		<th style="color:#555;">'. __('Name').'</th>
		<th style="color:#555;">'. __('Status').'</th>
		<th style="color:#555;">'. __('Manufacturer').'</th>
		<th style="color:#555;">'. __('Type').'</th>
		<th style="color:#555;">'. __('Model').'</th>
		<th style="color:#555;">'. __('User').'</th>
		<th style="color:#555;">'. __('Group').'</th>		
		</tr>
	</thead>
	<tbody>'; 		

while ($row = $DB->fetch_assoc($result2))		
{		

$sql_state = "SELECT name FROM glpi_states WHERE id = ".$row['states_id']." ";
$res_state = $DB->query($sql_state);
$state = $DB->result($res_state,0,'name');

$sql_fab = "SELECT name FROM glpi_manufacturers WHERE id = ".$row['manufacturers_id']." ";
$res_fab = $DB->query($sql_fab);
$fab = $DB->result($res_fab,0,'name');

$sql_type = "SELECT name FROM glpi_phonetypes WHERE id = ".$row['phonetypes_id']." ";
$res_type = $DB->query($sql_type);
$type = $DB->result($res_type,0,'name');

$sql_model = "SELECT name FROM glpi_phonemodels WHERE id = ".$row['phonemodels_id']." ";
$res_model = $DB->query($sql_model);
$model = $DB->result($res_model,0,'name');

$sql_group = "SELECT name FROM glpi_groups WHERE id = ".$row['groups_id']." ";
$res_group = $DB->query($sql_group);
$group = $DB->result($res_group,0,'name');

	echo '<tr>
			<td>'. $row['id'].'</td>
			<td><a href=../../../../front/phone.form.php?id='.$row['id'].' target="_blank"  style="color:#555555;" >'. $row['name'].'</td>
			<td>'. $state.'</td>
			<td>'. $fab.'</td>
			<td>'. $type.'</td>
			<td>'. $model.'</td>
			<td>'. getUserName($row['users_id']).'</td>
			<td>'. $group.'</td>
			</tr>';		
}

echo '		
	</tbody>
</table>';

?>

<script type="text/javascript" >
$(document).ready(function() {
    oTable = $('#phone_report').dataTable({
        "bJQueryUI": true,
        "sPaginationType": "full_numbers",
        "bFilter":false,
        //"aaSorting": [[0,'asc'], [1,'asc']],
        //"aoColumnDefs": [{ "sWidth": "60%", "aTargets": [1] }],
         "sDom": 'T<"clear">lfrtip',
         "oTableTools": {
			"sRowSelect": "os",
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
                 "sButtonText": "<?php echo _x('button', 'Export'); ?>",
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
