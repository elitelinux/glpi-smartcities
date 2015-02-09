<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2014 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author
   @comment
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011

   ------------------------------------------------------------------------
 */


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringProfile extends Profile {

      static $rightname = "config";

      /*
       * Old profile names:
       *
       * config_services_catalogs
       * config_components_catalogs
       * config_weathermap
       * config_views
       * config_sliders
       * homepage
       * homepage_views
       * homepage_system_status
       * homepage_hosts_status
       * homepage_services_catalogs
       * homepage_components_catalogs
       *[not used] homepage_perfdatas
       * homepage_all_ressources (service)
       * dashboard
       * dashboard_views
       * dashboard_sliders
       * dashboard_system_status
       * dashboard_hosts_status
       * dashboard_services_catalogs
       * dashboard_components_catalogs
       *[not used] dashboard_perfdatas
       * dashboard_all_ressources (service)
       * restartshinken
       * counters
       * acknowledge
       * downtime
       * host_command
       *
       */


   static function getOldRightsMappings() {
      $types = array (
          'config_services_catalogs'      => 'plugin_monitoring_servicescatalog',
          'config_components_catalogs'    => 'plugin_monitoring_componentscatalog',
          'config_weathermap'             => 'plugin_monitoring_weathermap',
          'config_views'                  => 'plugin_monitoring_displayview',
          'config_sliders'                => 'plugin_monitoring_slider',
          'homepage'                      => 'plugin_monitoring_homepage',
          'homepage_views'                => 'plugin_monitoring_displayview',
          'homepage_system_status'        => 'plugin_monitoring_systemstatus',
          'homepage_hosts_status'         => 'plugin_monitoring_hoststatus',
          'homepage_services_catalogs'    => 'plugin_monitoring_servicescatalog',
          'homepage_components_catalogs'  => 'plugin_monitoring_componentscatalog',
          'homepage_perfdatas'            => 'plugin_monitoring_perfdata',
          'homepage_all_ressources'       => 'plugin_monitoring_service',
          'dashboard'                     => 'plugin_monitoring_dashboard',
          'dashboard_views'               => 'plugin_monitoring_displayview',
          'dashboard_sliders'             => 'plugin_monitoring_slider',
          'dashboard_system_status'       => 'plugin_monitoring_systemstatus',
          'dashboard_hosts_status'        => 'plugin_monitoring_hoststatus',
          'dashboard_services_catalogs'   => 'plugin_monitoring_servicescatalog',
          'dashboard_components_catalogs' => 'plugin_monitoring_componentscatalog',
          'dashboard_perfdatas'           => 'plugin_monitoring_perfdata',
          'dashboard_all_ressources'      => 'plugin_monitoring_service',
          'restartshinken'                => 'plugin_monitoring_restartshinken',
          'counters'                      => 'plugin_monitoring_counter',
          'acknowledge'                   => 'plugin_monitoring_acknowledge',
          'downtime'                      => 'plugin_monitoring_downtime',
          'host_command'                  => 'plugin_monitoring_hostcommand'
          );
      return $types;
   }



   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item->fields['interface'] == 'central') {
         return self::createTabEntry('Monitoring');
      }
   }



   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      $pmProfile = new self();
      $pmProfile->showForm($item->getID());
      return TRUE;
   }



   static function uninstallProfile() {
      $pfProfile = new self();
      $a_rights = $pfProfile->getAllRights();
      foreach ($a_rights as $data) {
         ProfileRight::deleteProfileRights(array($data['field']));
      }
   }



   function getAllRights() {
      $a_rights = array();
      $a_rights = array_merge($a_rights, $this->getRightsGeneral());
      return $a_rights;
   }



   function getRightsGeneral() {
      $rights = array(
          array('rights'    => array(READ => __('Read')),
                'label'     => __('Dashboard', 'monitoring'),
                'field'     => 'plugin_monitoring_dashboard'
          ),
          array('rights'    => array(READ => __('Read')),
                'label'     => __('Homepage', 'monitoring'),
                'field'     => 'plugin_monitoring_homepage'
          ),
          array('itemtype'  => 'PluginMonitoringAcknowledge',
                'label'     => __('Acknowledge', 'monitoring'),
                'field'     => 'plugin_monitoring_acknowledge'
          ),
          array('itemtype'  => 'PluginMonitoringDowntime',
                'label'     => __('Downtime', 'monitoring'),
                'field'     => 'plugin_monitoring_downtime'
          ),
          array('itemtype'  => 'PluginMonitoringDisplayview',
                'label'     => __('Views', 'monitoring'),
                'field'     => 'plugin_monitoring_displayview'
          ),
          array('itemtype'  => 'PluginMonitoringSlider',
                'label'     => __('Slider', 'monitoring'),
                'field'     => 'plugin_monitoring_slider'
          ),
          array('itemtype'  => 'PluginMonitoringServicescatalog',
                'label'     => __('Services catalog', 'monitoring'),
                'field'     => 'plugin_monitoring_servicescatalog'
          ),
          array('itemtype'  => 'PluginMonitoringWeathermap',
                'label'     => __('Weathermap', 'monitoring'),
                'field'     => 'plugin_monitoring_weathermap'
          ),
          array('itemtype'  => 'PluginMonitoringComponentscatalog',
                'label'     => __('Components catalog', 'monitoring'),
                'field'     => 'plugin_monitoring_componentscatalog'
          ),
          array('itemtype'  => 'PluginMonitoringComponent',
                'label'     => __('Component', 'monitoring'),
                'field'     => 'plugin_monitoring_component'
          ),
          array('itemtype'  => 'PluginMonitoringCommand',
                'label'     => __('Command', 'monitoring'),
                'field'     => 'plugin_monitoring_command'
          ),
          array('itemtype'  => 'PluginMonitoringPerfdata',
                'label'     => __('Graph template', 'monitoring'),
                'field'     => 'plugin_monitoring_perfdata'
          ),
          array('itemtype'  => 'PluginMonitoringEventhandler',
                'label'     => __('Event handler', 'monitoring'),
                'field'     => 'plugin_monitoring_eventhandler'
          ),
          array('itemtype'  => 'PluginMonitoringRealm',
                'label'     => __('Reamls', 'monitoring'),
                'field'     => 'plugin_monitoring_realm'
          ),
          array('itemtype'  => 'PluginMonitoringTag',
                'label'     => __('Tag', 'monitoring'),
                'field'     => 'plugin_monitoring_tag'
          ),
          array('rights'    => array(CREATE => __('Create')),
                'label'     => __('Restart Shinken', 'monitoring'),
                'field'     => 'plugin_monitoring_restartshinken'
          ),
          array('itemtype'  => 'PluginMonitoringService',
                'label'     => __('Services (ressources)', 'monitoring'),
                'field'     => 'plugin_monitoring_service'
          ),
          array('itemtype'  => 'PluginMonitoringSystem',
                'label'     => __('System status', 'monitoring'),
                'field'     => 'plugin_monitoring_systemstatus'
          ),
          array('itemtype'  => 'PluginMonitoringHost',
                'label'     => __('Host status', 'monitoring'),
                'field'     => 'plugin_monitoring_hoststatus'
          ),
          array('rights'    => array(CREATE => __('Create')),
                'label'     => __('Host commands', 'monitoring'),
                'field'     => 'plugin_monitoring_hostcommand'
          ),
          array('rights'    => array(READ => __('Read')),
                'label'     => __('Unavailability', 'monitoring'),
                'field'     => 'plugin_monitoring_unavailability'
          ),
      );
      return $rights;
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

      $rights = $this->getRightsGeneral();
      $profile->displayRightsChoiceMatrix($rights, array('canedit'       => $canedit,
                                                      'default_class' => 'tab_bg_2',
                                                      'title'         => __('General', 'monitoring')));


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

   /**
    * @param $ID  integer
    */
   static function createFirstAccess($profiles_id) {
      include_once(GLPI_ROOT."/plugins/monitoring/inc/profile.class.php");
      $profile = new self();
      foreach ($profile->getAllRights() as $right) {
         self::addDefaultProfileInfos($profiles_id,
                                      array($right['field'] => ALLSTANDARDRIGHT));
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

   static function migrateProfiles() {
      global $DB;
      //Get all rights from the old table
      $profiles = getAllDatasFromTable(getTableForItemType(__CLASS__));

      //Load mapping of old rights to their new equivalent
      $oldrights = self::getOldRightsMappings();

      //For each old profile : translate old right the new one
      foreach ($profiles as $id => $profile) {
         switch ($profile['right']) {
            case 'r' :
               $value = READ;
               break;
            case 'w':
               $value = ALLSTANDARDRIGHT;
               break;
            case 0:
            default:
               $value = 0;
               break;
         }
         //Write in glpi_profilerights the new monitoring right
         if (isset($oldrights[$profile['type']])) {
            //There's one new right corresponding to the old one
            if (!is_array($oldrights[$profile['type']])) {
               self::addDefaultProfileInfos($profile['profiles_id'],
                                            array($oldrights[$profile['type']] => $value));
            } else {
               //One old right has been splitted into serveral new ones
               foreach ($oldrights[$profile['type']] as $newtype) {
                  self::addDefaultProfileInfos($profile['profiles_id'],
                                               array($newtype => $value));
               }
            }
         }
      }
   }

   /**
   * Init profiles during installation :
   * - add rights in profile table for the current user's profile
   * - current profile has all rights on the plugin
   */
   static function initProfile() {
      $pfProfile = new self();
      $profile   = new Profile();
      $a_rights  = $pfProfile->getAllRights();

      foreach ($a_rights as $data) {
         if (countElementsInTable("glpi_profilerights", "`name` = '".$data['field']."'") == 0) {
            ProfileRight::addProfileRights(array($data['field']));
            $_SESSION['glpiactiveprofile'][$data['field']] = 0;
         }
      }

      // Add all rights to current profile of the user
      if (isset($_SESSION['glpiactiveprofile'])) {
         $dataprofile       = array();
         $dataprofile['id'] = $_SESSION['glpiactiveprofile']['id'];
         $profile->getFromDB($_SESSION['glpiactiveprofile']['id']);
         foreach ($a_rights as $info) {
            if (is_array($info)
                && ((!empty($info['itemtype'])) || (!empty($info['rights'])))
                  && (!empty($info['label'])) && (!empty($info['field']))) {

               if (isset($info['rights'])) {
                  $rights = $info['rights'];
               } else {
                  $rights = $profile->getRightsFor($info['itemtype']);
               }
               foreach ($rights as $right => $label) {
                  $dataprofile['_'.$info['field']][$right] = 1;
                  $_SESSION['glpiactiveprofile'][$data['field']] = $right;
               }
            }
         }
         $profile->update($dataprofile);
      }
   }

}

?>