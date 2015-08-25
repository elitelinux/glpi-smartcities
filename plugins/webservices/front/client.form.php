<?php
/**
 * @version $Id: client.form.php 397 2014-11-29 23:54:21Z ddurieux $
 -------------------------------------------------------------------------
LICENSE

 This file is part of Webservices plugin for GLPI.

 Webservices is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Webservices is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Webservices. If not, see <http://www.gnu.org/licenses/>.

 @package   Webservices
 @author    Nelly Mahu-Lasson
 @copyright Copyright (c) 2009-2014 Webservices plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/webservices
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

Plugin::load('webservices', true);

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
$webservices = new PluginWebservicesClient();

if (isset($_POST["add"])) {
   $webservices->check(-1, CREATE,$_POST);
   $webservices->add($_POST);
   Html::back();

} else if (isset($_POST["update"])) {
   $webservices->check($_POST["id"], UPDATE);
   $webservices->update($_POST);
   Html::back();

} else if (isset($_POST["purge"])) {
   $webservices->check($_POST["id"], PURGE);
   $webservices->delete($_POST);
   Html::redirect($CFG_GLPI["root_doc"]."/plugins/webservices/front/client.php");

} else {
   Html::header(__('Web Services', 'webservices'), $_SERVER['PHP_SELF'], "config",
                "pluginWebservicesClient");
   $webservices->display(array('id' => $_GET["id"]));
   Html::footer();
}
?>