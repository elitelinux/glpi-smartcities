<?php
/*
 * @version $Id: configauthldap.php 36 2012-08-31 13:59:28Z dethegeek $
----------------------------------------------------------------------
MoreLDAP plugin for GLPI
----------------------------------------------------------------------

LICENSE

This file is part of MoreLDAP plugin.

MoreLDAP plugin is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

MoreLDAP plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with MoreLDAP plugin; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
------------------------------------------------------------------------
@package   MoreLDAP
@author    the MoreLDAP plugin team
@copyright Copyright (c) 2014-2014 MoreLDAP plugin team
@license   GPLv2+
http://www.gnu.org/licenses/gpl.txt
@link      https://forge.indepnet.net/projects/moreldap
@link      http://www.glpi-project.org/
@since     2014
------------------------------------------------------------------------
*/

include ('../../../inc/includes.php');

// Check ACL

Session::checkRight("config", 'r');

// Header

Html::header(
   __('Configuration'),
   $_SERVER['PHP_SELF'],
   'plugins',
   'moreldap'
);

Search::show("AuthLDAP");

// Footer

if (strstr($_SERVER['PHP_SELF'], "popup")) {
   Html::popFooter();
} else {
   Html::footer();
}