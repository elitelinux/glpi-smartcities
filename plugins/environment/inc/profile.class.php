<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Environment plugin for GLPI
 Copyright (C) 2003-2011 by the Environment Development Team.

 https://forge.indepnet.net/projects/environment
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Environment.

 Environment is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Environment is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Environment. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginEnvironmentProfile extends CommonDBTM {
   
   static $rightname = "profile";
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Profile' 
            && $item->getField('interface')!='helpdesk') {
            return PluginEnvironmentDisplay::getTypeName(1);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;


      if ($item->getType()=='Profile') {
         $ID = $item->getID();
         $prof = new self();

         self::addDefaultProfileInfos($ID, 
                                    array('plugin_environment'                   => 0,
                                          'plugin_environment_appliances'        => 0,
                                          'plugin_environment_webapplications'   => 0,
                                          'plugin_environment_certificates'      => 0,
                                          'plugin_environment_accounts'          => 0,
                                          'plugin_environment_domains'           => 0,
                                          'plugin_environment_databases'         => 0,
                                          'plugin_environment_badges'           => 0));
         $prof->showForm($ID);
      }
      return true;
   }

   static function createFirstAccess($ID) {
      //85
      self::addDefaultProfileInfos($ID,
                                    array('plugin_environment'                   => 1,
                                          'plugin_environment_appliances'        => 1,
                                          'plugin_environment_webapplications'   => 1,
                                          'plugin_environment_certificates'      => 1,
                                          'plugin_environment_accounts'          => 1,
                                          'plugin_environment_domains'           => 1,
                                          'plugin_environment_databases'         => 1,
                                          'plugin_environment_badges'           => 1), true);
   }
   
    /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {
      global $DB;
      
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (countElementsInTable('glpi_profilerights',
                                   "`profiles_id`='$profiles_id' AND `name`='$right'") && $drop_existing) {
            $profileRight->deleteByCriteria(array('profiles_id' => $profiles_id, 'name' => $right));
         }
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
      if ($profile->getField('interface') == 'central') {
         $rights = $this->getAllRights();
         $profile->displayRightsChoiceMatrix($rights, array('canedit'       => $canedit,
                                                         'default_class' => 'tab_bg_2',
                                                         'title'         => __('General')));
      }
      
      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }

   static function getAllRights($all = false) {
      
      $rights = array(
          array('rights'  => array(READ    => __('Read')),
                'label'     => __('Environment', 'environment'),
                'field'     => 'plugin_environment'
          ),
          array('rights'  => array(READ    => __('Read')),
                'label'     => __('Appliances', 'environment'),
                'field'     => 'plugin_environment_appliances'
          ),
          array('rights'  => array(READ    => __('Read')),
                'label'     => __('Web applications', 'environment'),
                'field'     => 'plugin_environment_webapplications'
          ),
          array('rights'  => array(READ    => __('Read')),
                'label'     => __('Certificates', 'environment'),
                'field'     => 'plugin_environment_certificates'
          ),
          array('rights'  => array(READ    => __('Read')),
                'label'     => __('Accounts', 'environment'),
                'field'     => 'plugin_environment_accounts'
          ),
          array('rights'  => array(READ    => __('Read')),
                'label'     => __('Domains', 'environment'),
                'field'     => 'plugin_environment_domains'
          ),
          array('rights'  => array(READ    => __('Read')),
                'label'     => __('Databases', 'environment'),
                'field'     => 'plugin_environment_databases'
          ),
          array('rights'  => array(READ    => __('Read')),
                'label'     => __('Badges', 'environment'),
                'field'     => 'plugin_environment_badges'
          ),
      );

      return $rights;
   }

   /**
    * Init profiles
    *
    **/
    
   static function translateARight($old_right) {
      switch ($old_right) {
         case '': 
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return ALLSTANDARDRIGHT;
         case '0':
         case '1':
            return $old_right;
            
         default :
            return 0;
      }
   }
   
   /**
   * @since 0.85
   * Migration rights from old system to the new one for one profile
   * @param $profiles_id the profile ID
   */
   static function migrateOneProfile($profiles_id) {
      global $DB;
      //Cannot launch migration if there's nothing to migrate...
      if (!TableExists('glpi_plugin_environment_profiles')) {
      return true;
      }
      
      foreach ($DB->request('glpi_plugin_environment_profiles', 
                            "`profiles_id`='$profiles_id'") as $profile_data) {

         $matching = array('environment'    => 'plugin_environment', 
                           'appliances' => 'plugin_environment_appliances',
                           'webapplications' => 'plugin_environment_webapplications',
                           'certificates' => 'plugin_environment_certificates',
                           'accounts' => 'plugin_environment_accounts',
                           'domains' => 'plugin_environment_domains',
                           'databases' => 'plugin_environment_databases',
                           'badges' => 'plugin_environment_badges');
         $current_rights = ProfileRight::getProfileRights($profiles_id, array_values($matching));
         foreach ($matching as $old => $new) {
            if (!isset($current_rights[$old])) {
               $query = "UPDATE `glpi_profilerights` 
                         SET `rights`='".self::translateARight($profile_data[$old])."' 
                         WHERE `name`='$new' AND `profiles_id`='$profiles_id'";
               $DB->query($query);
            }
         }
      }
   }
   
   /**
   * Initialize profiles, and migrate it necessary
   */
   static function initProfile() {
      global $DB;
      $profile = new self();

      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
         if (countElementsInTable("glpi_profilerights", 
                                  "`name` = '".$data['field']."'") == 0) {
            ProfileRight::addProfileRights(array($data['field']));
         }
      }
      
      //Migration old rights in new ones
      foreach ($DB->request("SELECT `id` FROM `glpi_profiles`") as $prof) {
         self::migrateOneProfile($prof['id']);
      }
      foreach ($DB->request("SELECT *
                           FROM `glpi_profilerights` 
                           WHERE `profiles_id`='".$_SESSION['glpiactiveprofile']['id']."' 
                              AND `name` LIKE '%plugin_environment%'") as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights']; 
      }
   }


   static function removeRightsFromSession() {
      foreach (self::getAllRights(true) as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }
}
?>