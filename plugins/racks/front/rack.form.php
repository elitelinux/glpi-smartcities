<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Racks plugin for GLPI
 Copyright (C) 2003-2011 by the Racks Development Team.

 https://forge.indepnet.net/projects/racks
 -------------------------------------------------------------------------

 LICENSE
                
 This file is part of Racks.

 Racks is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Racks is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Racks. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset ($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$PluginRacksRack      = new PluginRacksRack();
$PluginRacksOther     = new PluginRacksOther();
$PluginRacksRack_Item = new PluginRacksRack_Item();

if (isset ($_POST["add"])) {
   $PluginRacksRack->check(-1, CREATE, $_POST);
   $PluginRacksRack->add($_POST);
   Html::back();
} elseif (isset ($_POST["delete"])) {
   $PluginRacksRack->check($_POST['id'], DELETE);
   $PluginRacksRack->delete($_POST);
   $PluginRacksRack->redirectToList();
} elseif (isset ($_POST["restore"])) {
   $PluginRacksRack->check($_POST['id'], PURGE);
   $PluginRacksRack->restore($_POST);
   $PluginRacksRack->redirectToList();
} else if (isset ($_POST["purge"])) {
   $PluginRacksRack->check($_POST['id'], PURGE);
   $PluginRacksRack->delete($_POST, true);
   $PluginRacksRack->redirectToList();
} else if (isset ($_POST["update"])) {
   $PluginRacksRack->check($_POST['id'], UPDATE);
   $PluginRacksRack->update($_POST);
   Html::back();
} else if (isset ($_POST["addDevice"])) {
   if (!isset ($_POST['rack_size'])) {
      $PluginRacksRack->getFromDB($_POST['racks_id']);
      $_POST['rack_size'] = $PluginRacksRack->fields['rack_size'];
   }

   $test = explode(";", $_POST['itemtype']);
   
   $_POST['itemtype']                           = $test[0];
   $_POST['items_id']                           = $test[1];
   $_POST['plugin_racks_itemspecifications_id'] = $test[2];
        
   if ($_POST['itemtype']=='PluginRacksOtherModel') {
      $newid=$PluginRacksOther->addOthers($_POST['items_id']);
      $_POST['items_id']=$newid;
   }

   if (!empty($_POST['itemtype']) && $_POST['items_id'] > 0 && !empty ($_POST['pos'])) {
      if ($PluginRacksRack->canCreate()) {
          $space_left = $PluginRacksRack_Item->addItem($_POST['plugin_racks_racks_id'], 
                                                       $_POST['rack_size'], 
                                                       $_POST['faces_id'], 
                                                       $_POST['items_id'], 
                                                       $_POST['itemtype'], 
                                                       $_POST['plugin_racks_itemspecifications_id'], 
                                                       $_POST['pos']);
         if ($space_left < 0)
            Session::addMessageAfterRedirect(__('No more place for insertion', 'racks'),
                                             false, ERROR);
      }
   }
   Html::back();

} elseif (isset ($_POST["deleteDevice"])) {
   if ($PluginRacksRack->canCreate()) {
      foreach ($_POST["item"] as $key => $val) {
            $input = array('id' => $key);
            if ($val == 1) {
               $PluginRacksRack_Item->delete($input);
            }
         }
   }
   Html::back();
} elseif (isset ($_POST["deleteitem"])) {
   $input = array('id' => $_POST["id"]);
   $PluginRacksRack_Item->check($_POST["id"],UPDATE);
   $PluginRacksRack_Item->delete($input);
   Html::back();
} else if (isset ($_POST["update_server"])) {
   if ($PluginRacksRack->canCreate()) {
      foreach ($_POST["updateDevice"] as $key => $val) {
         $vartype     = "type" . $key;
         $varspec     = "plugin_racks_itemspecifications_id" . $key;
         $varname     = "name" . $key;
         $varitems_id = "items_id" . $key;
         if ($_POST[$vartype] == 'PluginRacksOtherModel') {
            $PluginRacksOther->updateOthers($_POST[$varitems_id],$_POST[$varname]);
         }
         $varpos = "position" . $key;

         $space_left = $PluginRacksRack_Item->updateItem($key, 
                                                         $_POST[$vartype], 
                                                         $_POST[$varspec],
                                                         $_POST['plugin_racks_racks_id'], 
                                                         $_POST['rack_size'], 
                                                         $_POST['faces_id'], 
                                                         $_POST[$varitems_id], 
                                                         $_POST[$varpos]);
                }
        }
        if ($space_left < 0) {
         Session::addMessageAfterRedirect(__('No more place for insertion', 'racks'), false, ERROR);
        }
        Html::back();
} else {
   $PluginRacksRack->checkGlobal(READ);
   Html::header(PluginRacksRack::getTypeName(2), '', "assets", "pluginracksmenu", "racks");
   $PluginRacksRack->display($_GET);
   Html::footer();
}
?>