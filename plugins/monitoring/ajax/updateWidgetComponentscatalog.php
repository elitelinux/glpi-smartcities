<?php
/*
 * @version $Id: dropdownValue.php 15573 2011-09-01 10:10:06Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

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
 along with GLPI; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: David DURIEUX
// Purpose of file:
// ----------------------------------------------------------------------

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"updateWidgetComponentscatalog.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}
session_write_close();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

if (! isset($_SESSION['plugin_monitoring_reduced_interface'])) {
   $_SESSION['plugin_monitoring_reduced_interface'] = false;
}

//      echo "
//      <script>
//         function toggleEntity(idEntity) {
//            Ext.select('#'+idEntity).each(function(el) {
//               var displayed = false;
//               el.select('tr.services').each(function(elTr) {
//                  elTr.setDisplayed(! elTr.isDisplayed());
//                  displayed = elTr.isDisplayed();
//               });
//               // if (! displayed) {
//                  // el.select('tr.header').each(function(elTr) {
//                     // elTr.applyStyles({'height':'10px'});
//                     // elTr.select('th').each(function(elTd) {
//                        // elTd.applyStyles({'height':'10px'});
//                     // });
//                  // });
//               // }
//               el.select('tr.header').each(function(elTr) {
//                  elTr.applyStyles(displayed ? {'height':'50px'} : {'height':'10px'});
//                  elTr.select('th').each(function(elTd) {
//                     elTd.applyStyles(displayed ? {'height':'50px'} : {'height':'10px'});
//                  });
//               });
//            });
//         };
//      </script>
//      ";


$pmComponentscatalog = new PluginMonitoringComponentscatalog();
$pmComponentscatalog->showWidgetFrame(
        $_GET['id'],
        $_SESSION['plugin_monitoring_reduced_interface'],
        $_GET['is_minemap']);

?>