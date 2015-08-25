<?php

// Init the hooks of the plugins -Needed
function plugin_init_talk() {
   global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;
    
   $PLUGIN_HOOKS['csrf_compliant']['talk'] = true;
   
   $plugin = new Plugin();
   if ($plugin->isInstalled('talk') && $plugin->isActivated('talk')) {
      //load preferences on profile changing
      $PLUGIN_HOOKS['change_profile']['talk'] = array('PluginTalkProfile','changeProfile');
       
      //if glpi is loaded
      if (Session::getLoginUserID()) {

         Plugin::registerClass('PluginTalkProfile',
                               array('addtabon' => 'Profile'));

         Plugin::registerClass('PluginTalkUserpref',
                               array('addtabon' => array('User', 'Preference')));

         $PLUGIN_HOOKS['use_massive_action']['talk'] = 1;

         if (Session::haveRight('plugin_talk_is_active', PluginTalkTicket::ACTIVE)) {
            if (strpos($_SERVER['REQUEST_URI'], "/ticket.form.php") !== false
               && isset($_GET['id'])) {

               if (PluginTalkUserpref::isFunctionEnabled("talk_tab"))  {
                  // Plugin::registerClass('PluginTalkTicket',
                  //                  array('addtabon' => array('Ticket')));

                  $PLUGIN_HOOKS['add_css']['talk'][] = 'css/talk.css';
                  if (!PluginTalkUserpref::isFunctionEnabled("old_tabs"))  {
                     $PLUGIN_HOOKS['add_css']['talk'][] = 'css/hide_ticket_tabs.css';
                  }

                  $_SESSION['plugin_talk_lasttickets_id'] = $_REQUEST['id'];
                  $PLUGIN_HOOKS['add_javascript']['talk'][] = 'scripts/insert_talktab.js.php';
                  $PLUGIN_HOOKS['add_javascript']['talk'][] = 'scripts/filter_timeline.js';
                  $PLUGIN_HOOKS['add_javascript']['talk'][] = 'scripts/read_more.js';
                  $PLUGIN_HOOKS['add_javascript']['talk'][] = 'scripts/split_button.js';
               }

               /* disabled for 0.85 */
               // if (PluginTalkUserpref::isFunctionEnabled("split_view"))  {
               //    $PLUGIN_HOOKS['add_css']['talk'][] = 'css/split_ticket_view.css';
               // }
            

            }
         }
      }
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_talk() {
   global $LANG;

   $author = "<a href='www.teclib.com'>TECLIB'</a>";
   return array ('name' => __("Talks", "talk"),
                 'version' => '0.85-1.1',
                 'author' => $author,
                 'homepage' => 'www.teclib.com',
                 'minGlpiVersion' => '0.85');
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_talk_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.85','lt') || version_compare(GLPI_VERSION,'0.86','ge')) {
      echo "This plugin requires GLPI 0.85+";
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_talk_check_config() {
   return true;
}
