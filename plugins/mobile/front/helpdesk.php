<?php
/*
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE
Inventaire
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
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: MickaelH - IPEOS I-Solutions - www.ipeos.com
// Purpose of file: This file is used to show the creation ticket form
// in the page.
// ----------------------------------------------------------------------

// Entry menu case

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

$welcome = $LANG['job'][13];

//$back = 'ss_menu.php?menu=maintain&ido='.$IDO;
$back = 'ss_menu.php?menu=maintain';

$common = new PluginMobileCommon;
//$common->displayHeader($welcome, 'ss_menu.php?menu=maintain');

$common->displayHeader($welcome, $back);

PluginMobileHelpdesk::show(Session::getLoginUserID(),1);

$common->displayFooter();
?>
