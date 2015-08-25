<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginTalkProfile extends Profile {
   static $rightname = "profile";

   static function install(Migration $migration) {
      global $DB;

      if (!$DB->query("CREATE TABLE IF NOT EXISTS `glpi_plugin_talk_profiles` (
            `id` int(11) NOT NULL auto_increment,
            `profiles_id` int(11) NOT NULL default '0',
            `is_active` char(1) collate utf8_unicode_ci default NULL,
            PRIMARY KEY  (`id`),
            KEY `profiles_id` (`profiles_id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci")) {
         return false;
      }

      //Migrate profiles to the new system
      require_once "ticket.class.php";
      self::initProfile();
      self::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   }

   static function uninstall() {
      global $DB;

      //delete profiles
      return 
         $DB->query("DELETE FROM glpi_profilerights WHERE name LIKE 'plugin_talk%'")
         && $DB->query("DROP TABLE IF EXISTS `glpi_plugin_talk_profiles`");
   }

   function getAllRights($all = false) {
      $rights = array();

      if ($all) {
         $rights[] = array('itemtype' => 'PluginTalkTicket',
                           'label'    =>  _sx('button', 'Enable'),
                           'field'    => 'plugin_talk_is_active');
      }
      
      return $rights;
   }

   static function getTypeName($nb=0) {
      return __('Talks', 'talk');
   }
   
   /**
   * Initialize profiles, and migrate it necessary
   */
   static function initProfile() {
      global $DB;
      $profile = new self();

      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
         if (countElementsInTable("glpi_profilerights", "`name` = '".$data['field']."'") == 0) {
            ProfileRight::addProfileRights(array($data['field']));
            $_SESSION['glpiactiveprofile'][$data['field']] = 0;
         }
      }
      
      //Migration old rights in new ones
      if (countElementsInTable("glpi_profilerights", "`name` = 'plugin_talk_is_active'") == 0) {
         foreach ($DB->request("SELECT `id` FROM `glpi_profiles`") as $prof) {
            self::migrateOneProfile($prof['id']);
         }
      }
   }
    
   /**
   * @since 0.85
   * Migration rights from old system to the new one for one profile
   * @param $profiles_id the profile ID
   */
   static function migrateOneProfile($profiles_id) {
      global $DB;
      
      foreach ($DB->request('glpi_plugin_talk_profiles', "`profiles_id`='$profiles_id'") as $profile_data) {
         $matching = array('is_active' => 'plugin_talk_is_active');
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

   public static function createFirstAccess($ID) {
      $talk_profile = new self();
      $profile = new Profile;
      $dataprofile = array('id' => $ID);
      $profile->getFromDB($ID);

      foreach ($talk_profile->getAllRights(true) as $talk_r) {   
         $g_rights = $profile->getRightsFor($talk_r['itemtype']);

         foreach ($g_rights as $g_right => $label) {
            $dataprofile['_'.$talk_r['field']][$g_right."_0"] = 1;
         }
      }
      $profile->update($dataprofile);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item->getType()=='Profile') {
         return self::getTypeName(2);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='Profile') {
         $ID = $item->getField('id');
         $prof = new self();
          
         $prof->showForm($item->getField('id'), array('target' =>
                  $CFG_GLPI["root_doc"]."/plugins/talk/front/profile.form.php"));
      }
      return true;
   }
    
    
   static function purgeProfiles(Profile $prof) {
      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }
    
   function getFromDBByProfile($profiles_id) {
      global $DB;

      $query = "SELECT * FROM `glpi_profilerights`
               WHERE `profiles_id` = '" . $profiles_id . "' 
               AND name = 'plugin_talk_is_active' 
               AND rights = ".PluginTalkTicket::ACTIVE;
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return $this->fields['id'];
         } else {
            return false;
         }
      }
      return false;
   }

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

   
   static function changeProfile() {
      $prof = new self();
      if ($prof->getFromDBByProfile($_SESSION['glpiactiveprofile']['id'])) {
         //get User preferences
         PluginTalkUserpref::loadInSession();

         // re-load profile in session (removed in self-service by Session::cleanProfile)
         $_SESSION['glpiactiveprofile']['plugin_talk_is_active'] = $prof->fields['rights'];
      } 
   }
    
   function showForm($profiles_id=0, $openform=TRUE, $closeform=TRUE) {
      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr("followup", array(CREATE, UPDATE, PURGE)))
          && $openform) {
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $rights = $this->getAllRights(true);
      $profile->displayRightsChoiceMatrix($rights, array('canedit'       => $canedit,
                                                      'default_class' => 'tab_bg_2',
                                                      'title'         => ""));

      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";

      $this->showLegend();
   }
}

?>