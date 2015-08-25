<?php

function plugin_talk_install() {
   $version = plugin_version_talk();
   $migration = new Migration($version['version']);

   // Parse inc directory
   foreach(glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if(preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginTalk' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if(method_exists($classname, 'install')) {
            $classname::install($migration);
         }
      }
   }
   return true ;
}   

function plugin_talk_uninstall() {
   // Parse inc directory
   foreach(glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if(preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginTalk' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if(method_exists($classname, 'uninstall')) {
            $classname::uninstall();
         }
      }
   }
   return true ;
}


function plugin_talk_getAddSearchOptions($itemtype) {
   $sopt = array();
   if ($itemtype == 'Profile') {
      $sopt[63976]['table']          = 'glpi_profilerights';
      $sopt[63976]['field']          = 'rights';
      $sopt[63976]['name']           = __('Talks', 'talk')." - ".__('Active');
      $sopt[63976]['datatype']       = 'right';
      $sopt[63976]['rightclass']     = 'PluginTalkTicket';
      $sopt[63976]['rightname']      = 'plugin_talk_is_active';
      $sopt[63976]['joinparams']     = array('jointype' => 'child',
                                         'condition' => "AND `NEWTABLE`.`name`= 'plugin_talk_is_active'");
   }
   return $sopt;
}

function plugin_talk_MassiveActions($type) {
   switch ($type) {
      case 'Profile' :
         return array("plugin_talk_edit_profile" => __("Talks", 'talk'));
   }
   return array();
}

function plugin_talk_MassiveActionsDisplay($options=array()) {
   switch ($options['itemtype']) {
      case 'Profile' :
         switch ($options['action']) {
            case "plugin_talk_edit_profile" :
               echo _sx('button', 'Enable')." : ";
               Dropdown::showYesNo("is_active", 1);
               echo "&nbsp;<input type='submit' name='massiveaction' class='submit' value='".
                      __s('Post')."'>";
            break;
         }
         break;
   }
   return "";
}

function plugin_talk_MassiveActionsProcess($data) {

   $ok      = 0;
   $ko      = 0;
   $noright = 0;

   switch ($data['action']) {
      case 'plugin_talk_edit_profile' :
         if ($data['itemtype'] == 'Profile') {
            $talk_profile = new PluginTalkProfile;
            foreach ($data['item'] as $profiles_id => $val) {
               if ($val == 1) {
                  if (!$id = $talk_profile->getFromDBByProfile($profiles_id)) {
                     PluginTalkProfile::createFirstAccess($profiles_id, $data['is_active']);
                     $ok++;
                  } else {
                     if ($talk_profile->update(array('id'        => $id, 
                                                     'is_active' => $data['is_active']))) 
                        $ok++;
                     else 
                        $ko++;
                  }
               }
            }
         }
         break;

   }
   return array('ok'      => $ok,
                'ko'      => $ko,
                'noright' => $noright);

}