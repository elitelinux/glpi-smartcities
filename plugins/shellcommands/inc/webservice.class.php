<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Shellcommands plugin for GLPI
 Copyright (C) 2003-2011 by the Shellcommands Development Team.

 https://forge.indepnet.net/projects/shellcommands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Shellcommands.

 Shellcommands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Shellcommands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with shellcommands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginShellcommandsWebservice {
    
    /**
     * Method to list shellcommands by webservice
     *
     * @param array $params       Options
     * @param string $protocol    Communication protocol used
     *
     * @return array or error value
     */
    static function methodList($params, $protocol) {
        if (isset ($params['help'])) {
            return array(
                'help' => 'bool,optional'
            );
        }
        if (!Session::getLoginUserID()) {
            return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
        }
        
        $shellcommandsList = array();
        
        $command = new PluginShellcommandsShellcommand();
        $commandItems = new PluginShellcommandsShellcommand_Item();
        
        foreach ($command->find('is_deleted = 0', 'name ASC') as $currentCommand) {
            $currentCommandTargets = array();
            foreach ($commandItems->find('`plugin_shellcommands_shellcommands_id` = ' . $currentCommand['id'] . '') as $currentItem) {
                $currentCommandTargets[] = $currentItem['itemtype'];
            }
            $shellcommandsList[] =  array(
                'id' => $currentCommand['id'],
                'name' => $currentCommand['name'],
                'targets' => $currentCommandTargets,
            );
        }
        
        return $shellcommandsList;
    }
    
    
    /**
     * Method to run shellcommands by webservice
     * 
     * @param array $params       Options
     * @param string $protocol    Communication protocol used
     * 
     * @return array or error value
     */
    static function methodRun($params, $protocol) {
      global $DB;
         
        if (isset ($params['help'])) {
            return array(
                'command_id'   => 'integer,optional,multiple',
                'command_name' => 'string,optional,multiple',
                'target_type'  => 'string,mandatory',
                'target_id'    => 'integer,optional,multiple',
                'target_name'  => 'string,optional',
                'help'         => 'bool,optional'
            );
        }
        if (!Session::getLoginUserID()) {
            return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
        }
        
        $executionOutputs = array();
        $invalidCommands = array();
        $invalidTargets = array();
        
        /** Parameters check **/
        if (
            (!isset($params['command_id']) || empty($params['command_id']))
            && (!isset($params['command_name']) || empty($params['command_name']))
        ) {
            return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'one among "command_id" and "command_name"');
        }
        //Post-relation: Either Shellcommand ID or name is given
        
        if (
            (!isset($params['target_type']) || empty($params['target_type']))
            || (
                (!isset($params['target_id']) || empty($params['target_id']))
                && (!isset($params['target_name']) || empty($params['target_name']))
            )
        ) {
            return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'must specify "target_type" and one among "target_id" and "target_name"');
        }
        //Post-relation: Either Target ID or name is given
        
        $possibleTargetTypes = PluginShellcommandsShellcommand_Item::getClasses(true);
        if (!in_array($params['target_type'], $possibleTargetTypes) || !class_exists($params['target_type'])) {
            return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', '"target_type" is "' . $params['target_type'] . '" must be one of: ' . implode(', ', $possibleTargetTypes) . '');
        }
        //Post-relation: Given target type ($params['target_type']) is valid
        /** /Parameters check **/
        
        
        /** Command determination **/
        $commandIds = array();
        if (isset($params['command_id']) && !empty($params['command_id'])) { // ID(s) given
            $commandIds = (array) $params['command_id'];
        } else { // Name(s) given
            $command_names = (array) $params['command_name'];
            foreach ($command_names as $currentCommandName) {
                $command = new PluginShellcommandsShellcommand();
                if ($command->getFromDBbyName($currentCommandName)) {
                    $commandIds[] = $command->fields['id'];
                } else {
                    $invalidCommands[] = $currentCommandName;
                }
            }
        }
        //Post-relation: $commandIds is an array containing either provided or found (via given name) shellcommand IDs
        /** /Command determination **/
        
        /** Target determination **/
        $targetIds = array();
        if (isset($params['target_id']) && !empty($params['target_id'])) { // ID(s) given
            $targetIds = (array) $params['target_id'];
        } else { // Name(s) given
            $target_names = (array) $params['target_name'];
            foreach ($target_names as $currentTargetName) {
                $target = new $params['target_type']();
                if ($found = $target->find("`name` LIKE '".$DB->escape($currentTargetName)."'")) {
                    $targetIds = array_merge($targetIds, array_keys($found));
                } else {
                    $invalidTargets[] = $currentTargetName;
                }
            }
        }
        //Post-relation: $targetIds is an array containing either provided or found (via given name) targets IDs (of type $params['target_type'])
        /** /Target determination **/
        
        
        $commandIds = array_unique($commandIds);
        $targetIds = array_unique($targetIds);
        
        foreach($targetIds as $currentTargetId) {
            $item = new $params['target_type']();
            $targetFound = $item->getFromDB($currentTargetId);
            if (!$targetFound) {
                $invalidTargets[] = $currentTargetId;
            }
            
            foreach ($commandIds as $currentCommandId) {
                $targetParams = PluginShellcommandsShellcommand_Item::resolveLinkOfCommand($currentCommandId, $item);
                if ($targetParams !== false) {
                    foreach ((array) $targetParams as $currentTargetParam) {
                        list($error, $executionOutput) = PluginShellcommandsShellcommand_Item::execCommand($currentCommandId, $currentTargetParam);
                        $executionOutputs[] = trim($executionOutput);
                    }
                } else {
                    $invalidCommands[] = $currentCommandId;
                }
            }
        }
        
        return array(
            'callResults' => implode(PHP_EOL, $executionOutputs),
            'invalidCommands' => $invalidCommands,
            'invalidTargets' => $invalidTargets,
        );
    }
}

?>