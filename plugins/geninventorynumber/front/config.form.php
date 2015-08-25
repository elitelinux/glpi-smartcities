<?php
/*
 * @version $Id: soap.php 306 2011-11-08 12:36:05Z remi $
 -------------------------------------------------------------------------
 geninventorynumber - plugin for GLPI
 Copyright (C) 2003-2011 by the geninventorynumber Development Team.
 This file is part of the geninventorynumber plugin.

 geninventorynumber plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 geninventorynumber plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 This file is part of geninventorynumber plugin.
 You should have received a copy of the GNU General Public License
 along with GLPI; along with geninventorynumber. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   geninventorynumber
 @author    the geninventorynumber plugin team
 @copyright Copyright (c) 2008-2013 geninventorynumber plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/geninventorynumber
 @link      http://www.glpi-project.org/
 @since     2008
 ---------------------------------------------------------------------- */

include ('../../../inc/includes.php');

$config = new PluginGeninventorynumberConfig();

if (isset($_POST['update'])) {
   $config->update($_POST);
   Html::back();
}


