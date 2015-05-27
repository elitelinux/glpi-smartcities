<?php
/*
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE
Inventaire
 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: MickaelH - IPEOS I-Solutions - www.ipeos.com
// Purpose of file: This file is used to show the creation ticket form
// in the page.
// ----------------------------------------------------------------------

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

$welcome = __('Solution');

$back = "item.php?itemtype=Ticket&menu=maintain&ssmenu=ticket&id=".$_REQUEST['id']."";

$common = new PluginMobileCommon;
$common->displayHeader($welcome, $back);


       echo "<form method='post' name='solutionform' action=".$CFG_GLPI["root_doc"]."/plugins/mobile/front/solution.php?id=".$_REQUEST['id']."&new=1 >";                                
                                          
       echo "<table>";
      
		 echo "<tr class='tab_bg_1'>"; 
		 echo "<td>&nbsp;</td>";     
		 echo "</tr>";      

       echo "<tr class='tab_bg_1'>";
       echo "<td>". __('Solution') .":</td>";    
		 echo "</td></tr>";

       echo "<tr class='tab_bg_1'>";       
       echo "<td class='right' colspan='2'><textarea name='content' cols='78' rows='14' >$content</textarea>";
		 echo "</td></tr>";
		 
       echo "<input type='hidden' name='_from_followup' value='$from_followup'>"; 
       echo "<input type='hidden' name='requesttypes_id' value='1'";
       echo "<input type='hidden' name='is_private' value='0'";
       echo "<input type='hidden' name='new' value='1'";
		      
       echo "<tr class='tab_bg_2'>";
       echo "<td colspan='1' class='center'>";
       echo "<input type='submit' value=\"". __("Save") ."\" class='submit' onClick1=\"'history.go(-1)'\">";
       echo "</td></tr>";
       echo "</table>"; 
       Html::closeForm();
       
       if (isset($_REQUEST['new']))
		{
			//echo "<div style='text-align:center; margin-top:20px;'> <b> ".$LANG['plugin_mobile']['common'][13]." </b></div>";		
		}
		
		
		if (Session::haveRight("followup", TicketFollowup::SEEPUBLIC)) {	
			
			global $key, $new;
			
			//$new = $_REQUEST['new'];			
			if(isset($_REQUEST['new']) && $_POST['content'] != "") {
			
			$id = $_REQUEST['id'];
			$content = $_POST['content'];
			
			
			$query =
			"UPDATE glpi_tickets
			SET solvedate = NOW(), date_mod = NOW(), solution = '".$content."', status = 5, users_id_lastupdater = '".$_SESSION['glpiID']."'
			WHERE id = ".$id." 
			";			
			
			$result=$DB->query($query);
			
			Html::redirect($CFG_GLPI["root_doc"].'/plugins/mobile/front/tab.php?glpi_tab=Ticket$2&id='.$id.'&itemtype=Ticket&menu=maintain&ssmenu=ticketcomputer');
			
			}
			
			if(isset($new) && $_POST['content'] == "") {
				echo "<script>alert('Error');</script>";
			}
			
		}

//PluginMobileFollowup::show(Session::getLoginUserID(),1);


$common->displayFooter();
?>
