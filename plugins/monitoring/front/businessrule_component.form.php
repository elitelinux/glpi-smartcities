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
   @since     2014

   ------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

Session::checkRight("plugin_monitoring_servicescatalog", READ);

Html::header(__('Monitoring', 'monitoring'),$_SERVER["PHP_SELF"], "plugins",
             "monitoring", "businessrules");

$pmBusinessrule_component = new PluginMonitoringBusinessrule_component();
$pmComponentscatalog_Component = new PluginMonitoringComponentscatalog_Component();

if (isset($_POST['add'])) {
   if (!isset($_POST['plugin_monitoring_components_id'])
           || $_POST['plugin_monitoring_components_id'] < 1) {
      Html::back();
   }

   $a_data = current($pmComponentscatalog_Component->find(
           "`plugin_monitoring_componentscalalog_id`='".$_POST['plugin_monitoring_componentscatalogs_id']."'
            AND `plugin_monitoring_components_id`='".$_POST['plugin_monitoring_components_id']."'",
           '',
           1));
   $_POST['plugin_monitoring_componentscatalogs_components_id'] = $a_data['id'];
   $pmBusinessrule_component->add($_POST);
   $pmBusinessrule_component->replayDynamicServices($_POST['plugin_monitoring_businessrulegroups_id']);
}
Html::back();

Html::footer();
?>