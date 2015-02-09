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

class PluginShellcommandsCommandGroup extends CommonDBTM {

   public $dohistory = true;
   static $rightname = 'plugin_shellcommands';

   public static function getTypeName($nb = 0) {
      return _n('Command group', 'Command groups', $nb, 'shellcommands');
   }

   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, DELETE));
   }

   function cleanDBonPurge() {
      global $DB;

      $temp = new PluginShellcommandsCommandGroup_Item();
      $temp->deleteByCriteria(array('plugin_shellcommands_commandgroups_id' => $this->fields['id']));
   }

   function getSearchOptions() {
      $tab = array();

      $tab['common'] = self::getTypeName(2);

      $tab[1]['table']         = $this->getTable();
      $tab[1]['field']         = 'name';
      $tab[1]['name']          = __('Name');
      $tab[1]['datatype']      = 'itemlink';
      
      $tab[30]['table']        = $this->getTable();
      $tab[30]['field']        = 'id';
      $tab[30]['name']         = __('ID');
      $tab[30]['datatype']     = 'integer';

      $tab[80]['table']        = 'glpi_entities';
      $tab[80]['field']        = 'completename';
      $tab[80]['name']         = __('Entity');
      $tab[80]['datatype']      = 'dropdown';

      $tab[81]['table']        = $this->getTable();
      $tab[81]['field']        = 'is_recursive';
      $tab[81]['name']         = __('Child entities');
      $tab[81]['datatype']     = 'bool';

      return $tab;
   }

   function defineTabs($options = array()) {
      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginShellcommandsCommandGroup_Item', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   function showForm($ID, $options = array()) {
      global $CFG_GLPI;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      
      echo "<td>".__('Check command', 'shellcommands')."</td>";
      echo "<td>";
      Dropdown::show('PluginShellcommandsShellcommand', array('name'   => "check_commands_id", 
                                                              'value'  => $this->fields['check_commands_id'], 
                                                              'entity' => $_SESSION['glpiactive_entity']));
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }
   
   /**
    * Main entry of the modal window for massive actions
    *
    * @return nothing: display
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma){
      
      $PluginShellcommandsShellcommand = new PluginShellcommandsShellcommand();

      switch ($ma->getAction()) {
         case 'generate':
            $itemtype = $ma->getItemtype(false);
            if (in_array($itemtype, PluginShellcommandsShellcommand::getTypes(true))) {
               echo PluginShellcommandsCommandGroup::getTypeName(1)." ";
               Dropdown::show('PluginShellcommandsCommandGroup', array('name' => 'commandgroup', 'entity' => $_SESSION['glpiactive_entity'], 'comments' => false));
               echo "<br><br>";
            }
            break;
      }
      return false;
   }

   /**
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      global $CFG_GLPI;
      
      switch ($ma->getAction()) {
         case 'generate':
            if ($ma->POST['commandgroup']) {
               $_SESSION["plugin_shellcommands"]["massiveaction"] = $ma;
               $_SESSION["plugin_shellcommands"]["ids"]           = $ids;
               
               $ma->results['ok'] = 1;
               $ma->display_progress_bars = false;
               
               echo "<script type='text/javascript'>";
               echo "location.href='".$CFG_GLPI['root_doc']."/plugins/shellcommands/front/massiveexec.php';";
               echo "</script>";
               
            }
            break;
      }
   }
   
   /**
    * Custom fonction to process shellcommand massive action
   **/
   function doMassiveAction(MassiveAction $ma, array $ids){
      
      if (!empty($ids)) {
         $input = $ma->getInput();

         $itemtype = $ma->getItemType(false);
         $commands_id = $input['commandgroup'];

         switch($ma->getAction()){
            case 'generate':
               echo "<div class='center'>";
               echo "<table class='tab_cadre_fixe center'>";
               echo "<tr class='tab_bg_1'>";
               echo "<th colspan='4'>".PluginShellcommandsCommandGroup::getTypeName(2)."</th>";
               echo "</tr>";
               foreach ($ids as $key => $items_id) {
                  PluginShellcommandsCommandGroup_Item::lauchCommand(array('itemID'    => $items_id,
                                                                           'itemtype'  => $itemtype,
                                                                           'id'        => $commands_id,
                                                                           'value'     => null));
               }
               echo "</table>";
               echo "</div>";
               break;
         }
      }
   }
   
}

?>