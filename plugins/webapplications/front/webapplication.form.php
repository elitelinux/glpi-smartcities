<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Webapplications plugin for GLPI
 Copyright (C) 2003-2011 by the Webapplications Development Team.

 https://forge.indepnet.net/projects/webapplications
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Webapplications.

 Webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$web      = new PluginWebapplicationsWebapplication();
$web_item = new PluginWebapplicationsWebapplication_Item();

if (isset($_POST["add"])) {
   $web->check(-1,CREATE,$_POST);
   $newID = $web->add($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $web->check($_POST['id'],DELETE);
   $web->delete($_POST);
   $web->redirectToList();

} else if (isset($_POST["restore"])) {
   $web->check($_POST['id'],PURGE);
   $web->restore($_POST);
   $web->redirectToList();

} else if (isset($_POST["purge"])) {
   $web->check($_POST['id'],PURGE);
   $web->delete($_POST,1);
   $web->redirectToList();

} else if (isset($_POST["update"])) {
   $web->check($_POST['id'],UPDATE);
   $web->update($_POST);
   Html::back();

} else if (isset($_POST["additem"])) {
   
   if (!empty($_POST['itemtype'])&&$_POST['items_id']>0) {
       $web_item->check(-1,UPDATE,$_POST);
      $web_item->addItem($_POST);
   }
   Html::back();

} else if (isset($_POST["deleteitem"])) {
   foreach ($_POST["item"] as $key => $val) {
      $input = array('id' => $key);
      if ($val == 1) {
         $web_item->check($key, UPDATE);
         $web_item->delete($input);
      }
   }
   Html::back();

//unlink webapplications to items of glpi from the items form
} else if (isset($_POST["deletewebapplications"])) {
   $input = array('id' => $_POST["id"]);
   $web_item->check($_POST["id"], UPDATE);
   $web_item->delete($input);
   Html::back();

} else {
   $web->checkGlobal(READ);

   //check environment meta-plugin installtion for change header
   $plugin = new Plugin();
   if ($plugin->isActivated("environment")) {
      Html::header(PluginWebapplicationsWebapplication::getTypeName(2),
                     '',"assets","pluginenvironmentdisplay","webapplications");
   } else {
      Html::header(PluginWebapplicationsWebapplication::getTypeName(2), '', "assets","pluginwebapplicationsmenu");
   }
   //load webapplications form
   $web->display($_GET);

   Html::footer();
}

?>