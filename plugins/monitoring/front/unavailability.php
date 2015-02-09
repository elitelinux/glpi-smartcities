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
   @since     2013

   ------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

Session::checkCentralAccess();

Html::header(__('Monitoring - unavailabilities', 'monitoring'), $_SERVER["PHP_SELF"], "plugins",
             "monitoring", "unavailability");

// if (isset($_GET['contains'])) {
   // $pmUnavailability = new PluginMonitoringUnavailability();
   // $pmUnavailability->showList($_GET);
// }

if (isset($_GET['component_catalog_id'])) {
   $pmUnavailability = new PluginMonitoringUnavailability();
   $pmUnavailability->displayComponentscatalog($_GET['component_catalog_id']);
}

// forceUpdate request parameter is to force an update ...
if (isset($_GET['forceUpdate'])) {
   // A services_id may be specified as a parameter ...
   // Default services_id is 0 for all services
   // start and limit may also be specified, defaults are 0 / 100
   PluginMonitoringUnavailability::runUnavailability(
      isset($_GET['services_id']) ? $_GET['services_id'] : 0, 
      isset($_GET['start']) ? $_GET['start'] : 0, 
      isset($_GET['limit']) ? $_GET['limit'] : 100);
}

Search::show('PluginMonitoringUnavailability');

Html::footer();
?>
