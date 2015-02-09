<?php
/**
 * @version $Id: $
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Behaviors plugin for GLPI.

 Behaviors is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Behaviors is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

 @package   behaviors
 @author    Nelly Mahu-Lasson
 @copyright Copyright (c) 2010-2014 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2014

 --------------------------------------------------------------------------
*/

class PluginBehaviorsProblem {


   static function beforeUpdate(Problem $problem) {

      if (!is_array($problem->input) || !count($problem->input)) {
         // Already cancel by another plugin
         return false;
      }

  //    Toolbox::logDebug("PluginBehaviorsProblem::beforeUpdate(), Problem=", $problem);
      $config = PluginBehaviorsConfig::getInstance();

      // Check is the connected user is a tech
      if (!is_numeric(Session::getLoginUserID(false))
          || !Session::haveRight('problem', UPDATE)) {
         return false; // No check
      }


      $soltyp  = (isset($problem->input['solutiontypes_id'])
                        ? $problem->input['solutiontypes_id']
                        : $problem->fields['solutiontypes_id']);

      // Wand to solve/close the problem
      if ((isset($problem->input['solutiontypes_id']) && $problem->input['solutiontypes_id'])
          || (isset($problem->input['solution']) && $problem->input['solution'])
          || (isset($problem->input['status'])
              && in_array($problem->input['status'],
                          array_merge(Problem::getSolvedStatusArray(),
                                      Problem::getClosedStatusArray())))) {

         if ($config->getField('is_problemsolutiontype_mandatory')) {
            if (!$soltyp) {
               unset($problem->input['status']);
               unset($problem->input['solution']);
               unset($problem->input['solutiontypes_id']);
               Session::addMessageAfterRedirect(__('You cannot close a problem without solution type',
                                                   'behaviors'), true, ERROR);
            }
         }
      }
   }

}
