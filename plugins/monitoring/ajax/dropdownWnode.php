<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2014 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author
   @comment
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2012

   ------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$pmWeathermapnode = new PluginMonitoringWeathermapnode();
$pmWeathermapnode->getFromDB($_POST['items_id']);

echo "<br/>";
echo __('Host', 'monitoring')."&nbsp:&nbsp";
$itemtype = $pmWeathermapnode->fields['itemtype'];
$item = new $itemtype();
$item->getFromDB($pmWeathermapnode->fields['items_id']);
echo $item->getLink(1);
echo "<br/>";
echo __('Name')."&nbsp;:&nbsp;";
echo "<input type='text' name='nameupdate' value='".$pmWeathermapnode->fields['name']."' /><br/>";

$positions = array(
    'middle' => __('Center', 'monitoring'),
    'start' => __('Right', 'monitoring'),
    'end' => __('Left', 'monitoring')
);
echo __('Position of label', 'monitoring')." : ";
Dropdown::showFromArray('position', $positions, array('value' => $pmWeathermapnode->fields['position']));

echo "<script language='JavaScript'>
document.pointform.x.value = ".$pmWeathermapnode->fields['x'].";
document.pointform.y.value = ".$pmWeathermapnode->fields['y'].";
</script>";

?>