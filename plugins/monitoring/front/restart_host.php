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
   @author    Frédéric Mohier
   @co-author
   @comment
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2013

   ------------------------------------------------------------------------
 */

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"restart_host.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

if (empty($_POST)) {
   Html::redirect($CFG_GLPI["root_doc"] . "/front/central.php");
}

// Get FusionInventory agent associated with host ...
if (! isset($_POST['host_id'])) {
   $_SESSION["MESSAGE_AFTER_REDIRECT"] = __('Missing computer id in parameters !', 'monitoring');
   Html::redirect($CFG_GLPI["root_doc"] . "/front/central.php");
}
$computerId = $_POST['host_id'];
if (! isset($_POST['host_name'])) {
   $_SESSION["MESSAGE_AFTER_REDIRECT"] = __('Missing computer name in parameters !', 'monitoring');
   Html::redirect($CFG_GLPI["root_doc"] . "/front/central.php");
}
$computerName = $_POST['host_name'];

$agent = new PluginFusioninventoryAgent();
$fusionAgentId = $agent->getAgentWithComputerid($computerId);

// Get FusionInventory task associated with host command ...
if (! isset($_POST['host_command'])) {
   $_SESSION["MESSAGE_AFTER_REDIRECT"] = __('Missing host command in parameters !', 'monitoring');
   Html::redirect($CFG_GLPI["root_doc"] . "/front/central.php");
}
$host_command = $_POST['host_command'];
$pfTaskjob = new PluginFusioninventoryTaskjob();
$a_lists = $pfTaskjob->find("name LIKE '$host_command'", '', 1);

if (count($a_lists) == 0) {
   $_SESSION["MESSAGE_AFTER_REDIRECT"] = __('Host command task not found : ', 'monitoring').$host_command;
   Html::redirect($CFG_GLPI["root_doc"] . "/front/central.php");
}
$a_list = current($a_lists);

$taskjob_id = $a_list['id'];
$definition = importArrayFromDB($a_list['definition']);

/*
 Pour les valeurs :
 $query = "INSERT INTO `glpi_plugin_fusioninventory_taskjobstates`
      (`plugin_fusioninventory_taskjobs_id`, `items_id`, `itemtype`, `state`,
       `plugin_fusioninventory_agents_id`, `uniqid`)
      VALUES ('0', '0', 'PluginFusioninventoryDeployPackage', '0', '0', '".uniqid()."')";
 '0', => l'id du job dans glpi_plugin_fusioninventory_taskjobs (fixe a chaque exécution)
 '0', => l'id du package 'PluginFusioninventoryDeployPackage',
 'PluginFusioninventoryDeployPackage' => c'est l'itemtype, donc on ne touche pas
 '0', => c'est le statut donc toujours 0 (=préparé)
 '0', => c'est l'id de l'agent de l'ordinateur, que tu peux récupérer l'id via la fonction PluginFusioninventoryAgent::getAgentWithComputerid('idducomputer')
 */
$query = "INSERT INTO `glpi_plugin_fusioninventory_taskjobstates`
   (`plugin_fusioninventory_taskjobs_id`, `items_id`, `itemtype`, `state`,
    `plugin_fusioninventory_agents_id`, `uniqid`)
   VALUES
   ('".$taskjob_id."', '".$definition[0]['PluginFusioninventoryDeployPackage']."',
    'PluginFusioninventoryDeployPackage', '0',
    '".$fusionAgentId."', '".uniqid()."')";

$result = $DB->query($query);

$_SESSION["MESSAGE_AFTER_REDIRECT"] = __('Host command \'', 'monitoring').$host_command.__('\' requested for the host \'', 'monitoring').$computerName.'\'';
Html::redirect($CFG_GLPI["root_doc"] . "/front/central.php");
?>