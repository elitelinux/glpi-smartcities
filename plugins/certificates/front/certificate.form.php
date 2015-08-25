<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Certificates plugin for GLPI
 Copyright (C) 2003-2011 by the certificates Development Team.

 https://forge.indepnet.net/projects/certificates
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of certificates.

 Certificates is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Certificates is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Certificates. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$certif=new PluginCertificatesCertificate();
$certif_item=new PluginCertificatesCertificate_Item();

if (isset($_POST["add"])) {
   $certif->check(-1,CREATE,$_POST);
   $newID= $certif->add($_POST);
   if ($_SESSION['glpibackcreated']) {
      Html::redirect($certif->getFormURL()."?id=".$newID);
   }
   Html::back();
   
} else if (isset($_POST["delete"])) {

   $certif->check($_POST['id'],DELETE);
   $certif->delete($_POST);
   $certif->redirectToList();
   
} else if (isset($_POST["restore"])) {

   $certif->check($_POST['id'],PURGE);
   $certif->restore($_POST);
   $certif->redirectToList();
   
} else if (isset($_POST["purge"])) {

   $certif->check($_POST['id'],PURGE);
   $certif->delete($_POST,1);
   $certif->redirectToList();
   
} else if (isset($_POST["update"])) {

   $certif->check($_POST['id'],UPDATE);
   $certif->update($_POST);
   Html::back();
   
} else if (isset($_POST["additem"])) {

   if (!empty($_POST['itemtype'])&&$_POST['items_id']>0) {
      $certif_item->check(-1,UPDATE,$_POST);
      $certif_item->addItem($_POST);
   }
   Html::back();
   
} else if (isset($_POST["deleteitem"])) {

   foreach ($_POST["item"] as $key => $val) {
      $input = array('id' => $key);
      if ($val==1) {
         $certif_item->check($key, UPDATE);
         $certif_item->delete($input);
      }
   }
   Html::back();
   
} else if (isset($_POST["deletecertificates"])) {

   $input = array('id' => $_POST["id"]);
   $certif_item->check($_POST["id"], UPDATE);
   $certif_item->delete($input);
   Html::back();
   
} else {

   $certif->checkGlobal(READ);

   $plugin = new Plugin();
   if ($plugin->isActivated("environment")) {
      Html::header(PluginCertificatesCertificate::getTypeName(2),'',"assets","pluginenvironmentdisplay","certificates");
   } else {
      Html::header(PluginCertificatesCertificate::getTypeName(2), '', "assets","plugincertificatesmenu");
   }
   $certif->display($_GET);

   Html::footer();
}

?>