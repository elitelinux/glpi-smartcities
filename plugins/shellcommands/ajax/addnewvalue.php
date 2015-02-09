<?php
/*
 * @version $Id: setup.php 480 2012-11-09 tynet $
 -------------------------------------------------------------------------
 Installations plugin for GLPI
 Copyright (C) 2006-2012 by the Installations Development Team.

 https://forge.indepnet.net/projects/installations
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Installations.

 Installations is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Installations is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Installations. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

//$AJAX_INCLUDE = 1;

include ('../../../inc/includes.php');
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

switch($_POST['action']){
   case 'add':
      PluginShellcommandsAdvanced_Execution::addNewValue($_POST['count']);
      break;
}

Html::ajaxFooter();

?>