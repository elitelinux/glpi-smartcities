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
   @since     2011

   ------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

Session::checkLoginUser();

$docDir = GLPI_PLUGIN_DOC_DIR.'/monitoring';

if (isset($_GET['file'])) {
   $filename = $_GET['file'];

   $file = $docDir.'/'.$filename;
   if (preg_match("/PluginMonitoringService-([0-9]+)-2h([0-9]+).png/", $filename)) {
      include (GLPI_ROOT."/inc/includes.php");

      $match = array();
      preg_match("/PluginMonitoringService-([0-9]+)-2h([0-9]+).png/", $filename, $match);

      $pmServicegraph = new PluginMonitoringServicegraph();
      $pmService = new PluginMonitoringService();
      $pmComponent = new PluginMonitoringComponent();
      $pmService->getFromDB($match[1]);
      $pmComponent->getFromDB($pmService->fields['plugin_monitoring_components_id']);

      $pmServicegraph->displayGraph($pmComponent->fields['graph_template'],
                                    "PluginMonitoringService",
                                    $match[1],
                                    $match[2],
                                    '2h');
   }
   Toolbox::sendFile($file, $filename);
}

?>