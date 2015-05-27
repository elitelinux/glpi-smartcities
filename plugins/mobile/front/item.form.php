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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

// Entry menu case

define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$classname = ucfirst($_GET['itemtype']);
$item = new $classname;


if (isset($_POST["add"])) {
   $item->check(-1,'w',$_POST);
   if ($newID = $item->add($_POST)) {
      
   }   
     
   $redirect = GLPI_ROOT."/plugins/mobile/front/item.php?menu=".$_GET['menu']
   ."&ssmenu=".$_GET['ssmenu']
   ."&itemtype=".$_GET['itemtype']
   ."&id=".$newID; 
   Html::redirect($redirect);
   
} else if (isset($_POST["delete"])) {
   $item->check($_POST['id'],'d');
   $ok = $item->delete($_POST);
   
   $redirect = GLPI_ROOT."/plugins/mobile/front/search.php?menu=".$_GET['menu']
   ."&ssmenu=".$_GET['ssmenu']
   ."&itemtype=".$_GET['itemtype']; 
   Html::redirect($redirect);
      
} else if (isset($_REQUEST["purge"])) {
   $item->check($_REQUEST['id'],'d');
   if ($item->delete($_REQUEST,1)) {
      
   }
   
   $redirect = GLPI_ROOT."/plugins/mobile/front/search.php?menu=".$_GET['menu']
   ."&ssmenu=".$_GET['ssmenu']
   ."&itemtype=".$_GET['itemtype']; 
   Html::redirect($redirect);
   
} else if (isset($_POST["update"])) {
   $item->check($_POST['id'],'w');
   $item->update($_POST);
   
   $redirect = GLPI_ROOT."/plugins/mobile/front/item.php?menu=".$_GET['menu']
   ."&ssmenu=".$_GET['ssmenu']
   ."&itemtype=".$_GET['itemtype']
   ."&id=".$_GET['id']; 
   Html::redirect($redirect);
   
}



?>
