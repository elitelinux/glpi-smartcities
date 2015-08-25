<?php
/**
 * @version $Id: config.form.php 294 2015-05-24 23:46:03Z yllen $
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   reports
 @authors    Nelly Mahu-Lasson, Remi Collet, Alexandre Delaunay
 @copyright Copyright (c) 2009-2015 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

include_once ("../../../inc/includes.php");
Plugin::load('reports');

Session::checkSeveralRightsOr(array("config"  => UPDATE,
                                    "profile" => UPDATE));
Html::header(__('Setup'), $_SERVER['PHP_SELF'], "config", "plugins");

echo "<div class='center'>";
echo "<table class='tab_cadre'>";
echo "<tr><th>".__('Reports plugin configuration', 'reports')."</th></tr>";

if (Session::haveRight("profile",UPDATE)) {
   echo "<tr class='tab_bg_1 center'><td>";
   echo "<a href='report.form.php'>".__('Reports plugin configuration', 'reports')."</a>";
   echo "</td/></tr>\n";
}

if (Session::haveRight("config",UPDATE)) {
   foreach (searchReport() as $report => $plug) {
      $url = getReportConfigPage($plug, $report);
      $file = GLPI_ROOT.getReportConfigPage($plug, $report);
      if (is_file($file)) {
         echo "<tr class='tab_bg_1 center'><td>";
         echo "<a href='".$CFG_GLPI['root_doc'].$url."'>".
                sprintf(__('%1$s: %2$s'), __('Report configuration'),
                        _($report.'_report_title', 'reports'));
         echo "</a></td/></tr>";
      }
   }
}

echo "</table></div>";

Html::footer();
?>