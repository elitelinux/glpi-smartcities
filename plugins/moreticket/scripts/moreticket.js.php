<?php
/*
  -------------------------------------------------------------------------
  Moreticket plugin for GLPI
  Copyright (C) 2013 by the Moreticket Development Team.
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Moreticket.

  Moreticket is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Moreticket is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Moreticket. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

//change mimetype
header("Content-type: application/javascript");

//not executed in self-service interface & right verification
if ($_SESSION['glpiactiveprofile']['interface'] == "central") {

   $config          = new PluginMoreticketConfig();
   $use_waiting     = $config->useWaiting();
   $use_solution    = $config->useSolution();
   $solution_status = $config->solutionStatus();

   $params = array('root_doc'        => $CFG_GLPI['root_doc'],
                   'waiting'         => CommonITILObject::WAITING,
                   'closed'          => CommonITILObject::CLOSED,
                   'use_waiting'     => $use_waiting,
                   'use_solution'    => $use_solution,
                   'solution_status' => $solution_status);

   echo "moreticket(".json_encode($params).");";
}
?>