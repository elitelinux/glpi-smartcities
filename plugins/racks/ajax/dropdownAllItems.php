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

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Plugin::load('racks', true);
if (isset($_POST["modeltable"]) && !empty($_POST["modeltable"])) {
   $rand       = mt_rand();
   $itemtype   = substr($_POST['modeltable'], 0, -5);
   $modelfield = getForeignKeyFieldForTable(getTableForItemType($_POST['modeltable']));
   $table      = getTableForItemType($itemtype);
   $params     = array('searchText' => '__VALUE__',
                       'modeltable' => $_POST["modeltable"],
                       'modelfield' => $modelfield,
                       'itemtype'   => $itemtype,
                       'rand'       => $rand,
                       'width'      => '500',
                       'myname'     => $_POST["myname"]);

   if (isset($_POST['value'])) {
      $params['value'] = $_POST['value'];
      $params['valuename'] = "-----";
   }
   if (isset($_POST['entity_restrict'])) {
      $params['entity_restrict'] = $_POST['entity_restrict'];
   }
   $field_id = Html::cleanId($_POST['myname'].$rand);
   echo Html::jsAjaxDropdown($_POST["myname"], 
                             $field_id, 
                             $CFG_GLPI['root_doc']."/plugins/racks/ajax/dropdownValue.php", 
                             $params);
}
?>