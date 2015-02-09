<?php

function plugin_init_webnotifications() {
  
   global $PLUGIN_HOOKS, $LANG ;
   
   //$menuentry = 'front/config.php'; 
       
   $PLUGIN_HOOKS['csrf_compliant']['webnotifications'] = true;
   //$PLUGIN_HOOKS['menu_entry']['notification']     = $menuentry;
      
   $PLUGIN_HOOKS['config_page']['webnotifications'] = 'front/config.php';
                
}


function plugin_version_webnotifications(){
	global $DB, $LANG;

	return array('name'			=> __('Web Notifications'),
					'version' 			=> '1.0.0',
					'author'			   => '<a href="mailto:stevenesdonato@gmail.com"> Stevenes Donato </b> </a>',
					'license'		 	=> 'GPLv2+',
					'homepage'			=> 'https://sourceforge.net/projects/glpiwebnotifications/',
					'minGlpiVersion'	=> '0.85');
}

function plugin_webnotifications_check_prerequisites(){
        if (GLPI_VERSION >= 0.85){
                return true;
        } else {
                echo "GLPI version not compatible need 0.85";
        }
}


function plugin_webnotifications_check_config($verbose=false){
	if ($verbose) {
		echo 'Installed / not configured';
	}
	return true;
}


?>
