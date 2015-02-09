<?php
/**
 * @version $Id: common.class.php 172 2014-11-15 17:41:55Z yllen $
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
 @author    Remi Collet
 @copyright Copyright (c) 2010-2014 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2010

 --------------------------------------------------------------------------
*/

class PluginBehaviorsCommon {

   static $clone_types = array('NotificationTemplate'  => 'PluginBehaviorsNotificationTemplate',
                               'Profile'               => 'PluginBehaviorsProfile',
                               'RuleImportComputer'    => 'PluginBehaviorsRule',
                               'RuleImportEntity'      => 'PluginBehaviorsRule',
                               'RuleMailCollector'     => 'PluginBehaviorsRule',
                               'RuleRight'             => 'PluginBehaviorsRule',
                               'RuleSoftwareCategory'  => 'PluginBehaviorsRule',
                               'RuleTicket'            => 'PluginBehaviorsRule',
                               'Transfer'              => 'PluginBehaviorsCommon');


   static function getCloneTypes() {
      return self::$clone_types;
   }


   /**
    * Declare that a type is clonable
    *
    * @param $clonetype    String   classe name of new clonable type
    * @param $managertype  String   class name which manage the clone actions (default '')
    *
    * @return Boolean
   **/
   static function addCloneType($clonetype, $managertype='') {

      if (!isset(self::$clone_types[$clonetype])) {
         self::$clone_types[$clonetype] = ($managertype ? $managertype : $clonetype);
         return true;
      }
      // already registered
      return false;
   }


   static function postInit() {

      Plugin::registerClass('PluginBehaviorsCommon',
                            array('addtabon' => array_keys(PluginBehaviorsCommon::getCloneTypes())));

      PluginBehaviorsTicket::onNewTicket();
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (array_key_exists($item->getType(), self::$clone_types)
          && $item->canUpdate()) {
         return __('Clone', 'behaviors');
      }
      return '';
   }


   static function showCloneForm(CommonGLPI $item) {

      echo "<form name='form' method='post' action='".Toolbox::getItemTypeFormURL(__CLASS__)."' >";
      echo "<div class='spaced' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th>".__('Clone', 'behaviors')."</th></tr>";

      if ($item->isEntityAssign()) {
         echo "<tr class='tab_bg_1'><td class='center'>";
         printf(__('%1$s: %2$s'), __('Destination entity'),
                   "<span class='b'>". Dropdown::getDropdownName('glpi_entities',
                                                                $_SESSION['glpiactive_entity']).
                   "</span>");
         echo "</td></tr>";
      }

      $name = sprintf(__('%1$s %2$s'), __('Clone of', 'behaviors'), $item->getName());
      echo "<tr class='tab_bg_1'><td class='center'>".sprintf(__('%1$s: %2$s'), __('Name'), $name);
      Html::autocompletionTextField($item, 'name', array('value' => $name,
                                                         'size'  => 60));
      echo "<input type='hidden' name='itemtype' value='".$item->getType()."'>";
      echo "<input type='hidden' name='id'       value='".$item->getID()."'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td class='center'>";
      echo "<input type='submit' name='_clone' value='".__('Clone', 'behaviors')."' class='submit'>";
      echo "</th></tr>";

      echo "</table></div>";
      Html::closeForm();

   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if (array_key_exists($item->getType(), self::$clone_types)
          && $item->canUpdate()) {
         self::showCloneForm($item);
      }
      return true;
   }


   static function cloneItem(Array $param) {

      // Sanity check
      if (!isset($param['itemtype']) || !isset($param['id']) || !isset($param['name'])
          || !array_key_exists($param['itemtype'], self::$clone_types)
          || empty($param['name'])
          || !($item = getItemForItemtype($param['itemtype']))) {
         return false;
      }

      // Read original and prepare clone
      $item->check($param['id'], READ);

      $input = ToolBox::addslashes_deep($item->fields);
      $input['name']    = $param['name'];
      $input['_add']    = 1;
      $input['_old_id'] = $input['id'];
      unset($input['id']);
      if ($item->isEntityAssign()) {
         $input['entities_id'] = $_SESSION['glpiactive_entity'];
      }

      // Manage NULL fields in original
      foreach($input as $k => $v) {
         if (is_null($input[$k])) {
            $input[$k] = "NULL";
         }
      }

      // Specific to itemtype - before clone
      if (method_exists(self::$clone_types[$param['itemtype']], 'preClone')) {
         $input = call_user_func(array(self::$clone_types[$param['itemtype']], 'preClone'),
                                 $item, $input);
      }

      // Clone
      $clone = clone $item;
      $clone->check(-1, CREATE, $input);
      $new = $clone->add($input);

      // Specific to itemtype - after clone
      if (method_exists(self::$clone_types[$param['itemtype']], 'postClone')) {
         call_user_func(array(self::$clone_types[$param['itemtype']], 'postClone'),
                        $clone, $param['id']);
      }
      Plugin::doHook('item_clone', $clone);

      // History
      if ($clone->dohistory) {
         $changes[0] = '0';
         $changes[1] = '';
         $changes[2] = addslashes(sprintf(__('%1$s %2$s'), __('Clone of', 'behaviors'),
                                             $item->getNameID(0, true)));
         Log::history($clone->getID(), $clone->getType(), $changes, 0,
                      Log::HISTORY_LOG_SIMPLE_MESSAGE);
      }
   }
}
