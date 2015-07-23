<?php



class PluginChatConfig extends CommonDBTM {

   static protected $notable = true;
   
   /**
    * @see CommonGLPI::getMenuName()
   **/
   static function getMenuName() {
      return __('Chat');
   }
   
   /**
    *  @see CommonGLPI::getMenuContent()
    *
    *  @since version 0.5.6
   **/
   static function getMenuContent() {
   	global $CFG_GLPI;
   
   	$menu = array();

      $menu['title']   = __('Chat');
      $menu['page']    = '/plugins/chat/front/index.php/site_admin/user/login'; ///index.php/site_admin/user/login
   	return $menu;
   }
    

}


function plugin_init_chat() {
  
   global $PLUGIN_HOOKS, $LANG ;
   
   $menuentry = '/front/chat.php'; 
       
   $PLUGIN_HOOKS['csrf_compliant']['chat'] = true;
  
   $PLUGIN_HOOKS["menu_toadd"]['chat'] = array('plugins'  => 'PluginChatConfig');  
 
//   $PLUGIN_HOOKS['menu_entry']['chat']     = 'front/index.php/site_admin/';   
      
   $PLUGIN_HOOKS['config_page']['chat'] = 'front/config.php';
                
}


function plugin_version_chat(){
	global $DB, $LANG;

	return array('name'			=> __('Chat'),
					'version' 			=> '1.0.2',
					'author'			   => '<a href="mailto:stevenesdonato@gmail.com"> Stevenes Donato </b> </a>',
					'license'		 	=> 'GPLv2+',
					'homepage'			=> 'https://sourceforge.net/projects/glpichat/',
					'minGlpiVersion'	=> '0.85');
}

function plugin_chat_check_prerequisites(){
        if (GLPI_VERSION>=0.85){
                return true;
        } else {
                echo "GLPI version not compatible need 0.85";
        }
}


function plugin_chat_check_config($verbose=false){
	if ($verbose) {
		echo 'Installed / not configured';
	}
	return true;
}


?>
