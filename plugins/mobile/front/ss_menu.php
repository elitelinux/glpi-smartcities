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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

// Entry menu case
define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

$welcome = "&nbsp;";
//$welcome = "GLPI - Mobile";

      //version check	                                                    								              								
		$ver = explode(" ",implode(" ",plugin_version_mobile()));																																																						
		$urlv = "http://a.fsdn.com/con/app/proj/glpimobile/screenshots/".$ver[1].".png";
		$headers = get_headers($urlv, 1);										

		if($headers[0] != '') {
	
			if ($headers[0] == 'HTTP/1.0 404 Not Found') {
				$welcome = 'GLPI - Mobile &nbsp; <a href="https://sourceforge.net/projects/glpimobile/files/" target="_blank" style="color:#fff;">                
	           <span class="blink_me">'. __('New version avaliable','dashboard').'</span></a>';		
					}				
			else {
					$welcome = "GLPI - Mobile";
					}		
			   }


$common = new PluginMobileCommon;
$common->displayHeader($welcome, 'central.php', true, '', 'ss_menu');

if (!isset($_REQUEST['menu'])) $_REQUEST['menu'] = 'inventory';

$menu = new PluginMobileMenu;
$menu->showSpecificMenu($_REQUEST['menu']);
 
?>
