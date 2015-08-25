<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginTalkUserpref extends CommonDBTM {
   static $rightname = "plugin_talk_is_active";

   static function getTypeName($nb=0) {
      return __('Talks', 'talk');
   }
    
   static function getIndexName() {
      return "users_id";
   }

   static function canUpdate() {

      if (static::$rightname) {
         return Session::haveRight(static::$rightname, PluginTalkTicket::ACTIVE);
      }
   }

   static function canView() {
      if (static::$rightname) {
         return Session::haveRight(static::$rightname, PluginTalkTicket::ACTIVE);
      }
      return false;
   }
    
   static function install(Migration $migration) {
      global $DB;

      if (!$DB->query("CREATE TABLE IF NOT EXISTS `glpi_plugin_talk_userprefs` (
            `id`                INT(11) NOT NULL auto_increment,
            `users_id`          INT(11) NOT NULL default '0',
            `talk_tab`   TINYINT(1) NOT NULL default '1',
            `old_tabs`   TINYINT(1) NOT NULL default '1',
            `split_view` TINYINT(1) NOT NULL default '0',
            PRIMARY KEY  (`id`),
            UNIQUE KEY (`users_id`),
            KEY `talk_tab` (`talk_tab`),
            KEY `split_view` (`split_view`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci")) {
         return false;
      }   

      if (!FieldExists('glpi_plugin_talk_userprefs', 'old_tabs')) {
         $migration->addField('glpi_plugin_talk_userprefs', 'old_tabs', 'bool');
         $migration->migrationOneTable('glpi_plugin_talk_userprefs');
      }
   }

   static function uninstall() {
      global $DB;

      return $DB->query("DROP TABLE IF EXISTS `glpi_plugin_talk_userprefs`");
   }

   static function loadInSession() {
      unset($_SESSION['talk_userprefs']);
      $self = new self;
      if($self->getFromDB(Session::getLoginUserID())) {
         $_SESSION['talk_userprefs'] = $self->fields;
      } else {
         $self->add(array('users_id' => Session::getLoginUserID()));
         $self->getFromDB(Session::getLoginUserID());
         $_SESSION['talk_userprefs'] = $self->fields;
      }
   }

   static function isFunctionEnabled($function) {
      if (isset($_SESSION['talk_userprefs'][$function])
         && $_SESSION['talk_userprefs'][$function] == 1) {
         return true;
      }

      return false;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if (in_array($item->getType(), array('User', 'Preference'))) {
         return self::getTypeName(2);
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='User') {
         $ID = $item->getField('id');
      } else if ($item->getType()=='Preference') {
         $ID = Session::getLoginUserID();
      }

      $self = new self;
      $self->showForm($ID);
      
      return true;
   }

   function showForm ($ID, $options=array()) {
      if (!$this->getFromDB($ID)) {
         $this->add(array('users_id' => $ID));
      }

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_2'>";
      echo "<td width='10%'>" .__("Enable Talk Tab", 'talk')."</td>";
      echo "<td style='text-align:left;'>";
      Dropdown::showYesNo("talk_tab", $this->fields["talk_tab"]);
      echo "</td>";

      echo "<td width='10%'>" .__("Show replaced Tabs", 'talk')."</td>";
      echo "<td style='text-align:left;'>";
      Dropdown::showYesNo("old_tabs", $this->fields["old_tabs"]);
      echo "</td>";

      // echo "<td width='10%'>" .__("Enable horizontal split view", 'talk')."</td>";
      // echo "<td style='text-align:left;'>";
      // Dropdown::showYesNo("split_view", $this->fields["split_view"]);
      // echo "</td>";
      
      echo "</tr>";

      echo "<input type='hidden' name='id' value=".$this->fields["id"].">";
      echo "<input type='hidden' name='users_id' value=".$this->fields["users_id"].">";

      $options['candel'] = false;
      $this->showFormButtons($options);
   }
}
