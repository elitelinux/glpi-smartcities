<?php

function plugin_webnotifications_install(){
	
	global $DB, $LANG;
	
    if (! TableExists("glpi_plugin_webnotifications_count")) {
        $query = "CREATE TABLE `glpi_plugin_webnotifications_count` (`users_id` INTEGER NOT NULL,
        `quant` INTEGER, `type` int(2) NOT NULL,
        PRIMARY KEY (`users_id`,`type`))
		  ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				";
        $DB->query($query) or die("error creating glpi_plugin_webnotifications_count " . $DB->error());
        
        $insert = "INSERT INTO glpi_plugin_webnotifications_count (users_id, quant) VALUES ('1','1','0')";
        $DB->query($insert);
    } 	
    

    if (! TableExists("glpi_plugin_webnotifications_count_grp")) {
        $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_webnotifications_count_grp` (
		  `groups_id` int(11) NOT NULL,
		  `quant` int(11) DEFAULT NULL,
		  `users_id` int(10) NOT NULL,
		  PRIMARY KEY (`groups_id`,`users_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;   ";
		
        $DB->query($query) or die("error creating glpi_plugin_webnotifications_count_grp " . $DB->error());
        
        $insert = "INSERT INTO glpi_plugin_webnotifications_count_grp (groups_id, quant, users_id) VALUES ('1','1','0')";
        $DB->query($insert);
    } 
    
     if (! TableExists("glpi_plugin_webnotifications_config")) {
	     $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_webnotifications_config` (
		  `id` int(4) NOT NULL AUTO_INCREMENT,
  		  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  		  `value` int(3) NOT NULL,
  			PRIMARY KEY (`id`,`name`)
		  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ; ";
		
	     $DB->query($query) or die("error creating glpi_plugin_webnotifications_config " . $DB->error());
	     
	     $insert = "INSERT INTO glpi_plugin_webnotifications_config (id,name, value) VALUES ('1','sound','1')";
	     $DB->query($insert);
    }
        	
	return true;
}


function plugin_webnotifications_uninstall(){

	global $DB;
	
$drop = "DROP TABLE glpi_plugin_webnotifications_count";
$DB->query($drop); 	

$drop_g = "DROP TABLE glpi_plugin_webnotifications_count_grp";
$DB->query($drop_g); 	

$drop_c = "DROP TABLE glpi_plugin_webnotifications_config";
$DB->query($drop_c);
	
	return true;
}

?>
