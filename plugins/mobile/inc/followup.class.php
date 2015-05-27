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
// Purpose of file: This class displays the form to create a new ticket
// ----------------------------------------------------------------------

/*
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}
*/

class PluginMobileFollowup  {


   public static function show($ID,$from_followup) {
   	
      global $LANG,$CFG_GLPI,$DB, $content;

      if (!Session::haveRight("followup", TicketFollowup::SEEPUBLIC)) {
          return false;
      }

       $is_private = 0;
       $requesttypes_id="1";
       $content="";
       
              
       if (isset($_SESSION["helpdeskSaved"]["content"])) {
          $content = cleanPostForTextArea($_SESSION["helpdeskSaved"]["content"]);
       }
       if (isset($_SESSION["helpdeskSaved"]["requesttypes_id"])) {
          $requesttypes_id = stripslashes($_SESSION["helpdeskSaved"]["requesttypes_id"]);
       }
       if (isset($_SESSION["helpdeskSaved"]["is_private"])) {
          $is_private = stripslashes($_SESSION["helpdeskSaved"]["is_private"]);
       }
       
       unset($_SESSION["helpdeskSaved"]);
 
//}

		if (isset($_REQUEST['new']))
		{
			echo "<div style='text-align:center; margin-top:20px;'> <b> ".$LANG['plugin_mobile']['common'][13]." </b></div>";		
		}		
		
                                           
       echo "<form method='post' name='followupform' action=".$CFG_GLPI["root_doc"]."/plugins/mobile/front/followup.php?id=".$_REQUEST['id']."&new=1 >";                                
                                          
       echo "<table>";
      
		 echo "<tr class='tab_bg_1'>"; 
		 echo "<td>&nbsp;</td>";     
		 echo "</tr>";      

       echo "<tr class='tab_bg_1'>";
       echo "<td>".$LANG['joblist'][6]."&nbsp;:</td>";    
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
       echo "<input type='submit' value=\"". $LANG['plugin_mobile']['common'][7] ."\" class='submit' onClick=\"'history.go(-1)'\">";
       echo "</td></tr>";
       echo "</table>"; 
       Html::closeForm();


if (Session::haveRight("followup", TicketFollowup::SEEPUBLIC)) {	

global $key, $new;

$new = $_REQUEST['new'];

if(isset($new) && $_POST['content'] != "") {

$id = $_REQUEST['id'];
$content = $_POST['content'];


$query =
"INSERT INTO glpi_ticketfollowups (tickets_id, date, users_id, content, is_private, requesttypes_id) 
VALUES (".$id.", NOW(), ".$ID.", '".$content."', '".$is_private."', '".$requesttypes_id."') 
";

$result=$DB->query($query);

}

elseif(isset($new) && $_POST['content'] == "") {
echo $LANG['plugin_mobile']['common'][8];
}

}

}
}

