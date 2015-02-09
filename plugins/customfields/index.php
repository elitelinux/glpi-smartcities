<?php
/*
----------------------------------------------------------------------
GLPI - Gestionnaire Libre de Parc Informatique
Copyright (C) 2003-2009 by the INDEPNET Development Team.

http://indepnet.net/   http://glpi-project.org/
----------------------------------------------------------------------

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
along with GLPI; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
------------------------------------------------------------------------
*/

// ----------------------------------------------------------------------
// Sponsor: Oregon Dept. of Administrative Services, State Data Center
// Original Author of file: Ryan Foster
// Contact: Matt Hoover <dev@opensourcegov.net>
// Project Website: http://www.opensourcegov.net
// Purpose of file: This is the main page for the plugin. It redirects
// to the configuration page if the user has configuration rights.
// ----------------------------------------------------------------------

include ('../../inc/includes.php');

// Header

Html::header(
   __('Title','customfields'),
   '',
   'plugins',
   'customfields'
);

if (Session::haveRight('config', UPDATE)) {

   // redirect to the configuration page

   Html::redirect('./front/config.form.php');

} else {

   // Displays 'Access denied'

   echo '<div class="center"><br><br>'
      . '<img src="'
      . $CFG_GLPI['root_doc']
      . '/pics/warning.png" alt="warning"><br><br>';
   echo '<b>' . __('Access Denied') . '</b></div>';

}