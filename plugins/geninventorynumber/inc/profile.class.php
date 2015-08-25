<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
 LICENSE

 This file is part of the geninventorynumber plugin.

 geninventorynumber plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 geninventorynumber plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginGeninventorynumberProfile extends CommonDBTM {

   static $rightname = "config";

   /**
    * @param $ID  integer
    */
   static function createFirstAccess($profiles_id) {
      include_once(GLPI_ROOT."/plugins/geninventorynumber/inc/profile.class.php");
      $profile = new self();
      foreach ($profile->getAllRights() as $right) {
         self::addDefaultProfileInfos($profiles_id,
                                      array($right['field'] => ALLSTANDARDRIGHT));
      }
   }

   static function addDefaultProfileInfos($profiles_id, $rights) {
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (!countElementsInTable('glpi_profilerights',
                                   "`profiles_id`='$profiles_id' AND `name`='$right'")) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   static function removeRightsFromSession() {
      $profile = new self();
      foreach ($profile->getAllRights() as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
      ProfileRight::deleteProfileRights(array($right['field']));

   }

  /**
    * Show profile form
    *
    * @param $items_id integer id of the profile
    * @param $target value url of target
    *
    * @return nothing
    **/
   function showForm($profiles_id=0, $openform=TRUE, $closeform=TRUE) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE)))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      $rights = $this->getAllRights();
      $profile->displayRightsChoiceMatrix($rights, array('canedit'       => $canedit,
                                                         'default_class' => 'tab_bg_2',
                                                         'title'         => __('General')));

      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'),
                           array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }

   static function getAllRights() {
      return array(array('itemtype'  => 'PluginGeninventorynumber',
                         'label'     => __('GenerateInventoryNumber', 'geninventorynumber'),
                         'field'     => 'plugin_geninventorynumber',
			 'rights' => array(CREATE    => __('Create'),UPDATE    => __('Update'))));
   }

   static function install(Migration $migration) {
      global $DB;
      $table = getTableForItemType(__CLASS__);
       
      if ( isset( $_SESSION['glpiactiveprofile'] ) ) {
	       PluginGeninventorynumberProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
      }

      if (TableExists("glpi_plugin_geninventorynumber_profiles")) {
          foreach (getAllDatasFromTable($table) as $data) {
             $profile = new self();
             foreach ($profile->getAllRights() as $right => $rights) {

                if (!countElementsInTable('glpi_profilerights',
                                          "`profiles_id`='".$data['profiles_id']."' 
                                            AND `name`='".$rights['field']."'")) {

                   $profileRight = new ProfileRight();

                   $myright = array();
                   $myright['name']        = $rights['field'];
                   $myright['profiles_id'] = $data['profiles_id'];

                   if (!strcmp($data['plugin_geninventorynumber_generate'],'w'))
                       $myright['rights'] = CREATE;

                   if (!strcmp($data['plugin_geninventorynumber_overwrite'],'w'))
                      $myright['rights'] += UPDATE;

                  $profileRight->add($myright);
               }
            }
         }
         $migration->dropTable($table);
      }
   }

   static function uninstallProfile() {

      $pfProfile = new self();
      $a_rights = $pfProfile->getAllRights();

      foreach ($a_rights as $data) {
         ProfileRight::deleteProfileRights(array($data['field']));
      }
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->fields['interface'] == 'central') {
         return self::createTabEntry(__('geninventorynumber', 'geninventorynumber'));
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      $profile = new self();
      $profile->showForm($item->getID());
      return true;
   }

}
