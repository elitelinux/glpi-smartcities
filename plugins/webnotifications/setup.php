<?php

function plugin_init_webnotifications() {
  
   global $PLUGIN_HOOKS, $LANG ;
             
   $PLUGIN_HOOKS['csrf_compliant']['webnotifications'] = true;   
      
   $PLUGIN_HOOKS['config_page']['webnotifications'] = 'front/config.php';
                
}


function plugin_version_webnotifications(){
	global $DB, $LANG;

	return array('name'			=> __('Web Notifications'),
					'version' 			=> '1.0.2',
					'author'			   => '<a href="mailto:stevenesdonato@gmail.com"> Stevenes Donato </b> </a>',
					'license'		 	=> 'GPLv2+',
					'homepage'			=> 'https://sourceforge.net/projects/glpiwebnotifications/',
					'minGlpiVersion'	=> '0.84');
}

function plugin_webnotifications_check_prerequisites(){
        if (GLPI_VERSION >= 0.84){
                return true;
        } else {
                echo "GLPI version not compatible need 0.84";
        }
}


function plugin_webnotifications_check_config($verbose=false){
	if ($verbose) {
		echo 'Installed / not configured';
	}
	return true;
}


?>
