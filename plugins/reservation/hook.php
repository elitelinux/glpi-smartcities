<?php


function plugin_reservation_install() {
  global $DB;
  $migration = new Migration(100);
  if (!TableExists("glpi_plugin_reservation_manageresa")) { //INSTALL
    $query = "CREATE TABLE `glpi_plugin_reservation_manageresa` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `resaid` int(11) NOT NULL,
      `matid` int(11) NOT NULL,
      `date_return` datetime,
      `date_theorique` datetime NOT NULL,
      `itemtype` VARCHAR(100) NOT NULL,
      `dernierMail` datetime,
      PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"; 

	$DB->queryOrDie($query, $DB->error());
  }
  else { // UPDATE
   
  }


    if(TableExists("glpi_plugin_reservation_config"))
    {
      $query = "RENAME TABLE `glpi_plugin_reservation_config` TO `glpi_plugin_reservation_configdayforauto`";
      $DB->query($query) or die($DB->error());
    }

    if (!TableExists("glpi_plugin_reservation_configdayforauto")) 
    {
        // Création de la table config
        $query = "CREATE TABLE `glpi_plugin_reservation_configdayforauto` (
        `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `jour` char(32) NOT NULL default '',
        `actif` int(1) NOT NULL default '1'
        )ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $DB->query($query) or die($DB->error());

         $query = "INSERT INTO `glpi_plugin_reservation_configdayforauto` (`jour` , `actif`)
                VALUES (\"lundi\",1),
                       (\"mardi\",1),
                       (\"mercredi\",1),
                       (\"jeudi\",1),
                       (\"vendredi\",1),
                       (\"samedi\",0),
                       (\"dimanche\",0)";
                       
      $DB->queryOrDie($query) or die($DB->error());
    }
	else { // UPDATE
	}


    if (!TableExists("glpi_plugin_reservation_config")) 
    {
        // Création de la table config
        $query = "CREATE TABLE `glpi_plugin_reservation_config` (
        `name` VARCHAR(10) NOT NULL PRIMARY KEY,
        `value` VARCHAR(10) NOT NULL
        )ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $DB->query($query) or die($DB->error());

         $query = "INSERT INTO `glpi_plugin_reservation_config` (`name` , `value`)
                VALUES (\"methode\",\"manual\")";
                       
      $DB->queryOrDie($query) or die($DB->error());
    }
  else { // UPDATE
  }
     

  $cron = new CronTask;
  if (!$cron->getFromDBbyName('PluginReservationTask','SurveilleResa'))
  {
    CronTask::Register('PluginReservationTask', 'SurveilleResa', 5*MINUTE_TIMESTAMP,array('param' => 24, 'mode' => 2, 'logs_lifetime'=> 10));
  }

  if (!$cron->getFromDBbyName('PluginReservationTask','MailUserDelayedResa'))
  {
    CronTask::Register('PluginReservationTask', 'MailUserDelayedResa', DAY_TIMESTAMP,array('hourmin' => 23, 'hourmax' => 24,  'mode' => 2, 'logs_lifetime'=> 30, 'state'=>0));

  }




  return true;
}

function plugin_reservation_uninstall() {
  global $DB;
  $tables = array("glpi_plugin_reservation_manageresa","glpi_plugin_reservation_config","glpi_plugin_reservation_configdayforauto");
  foreach($tables as $table) 
  {$DB->query("DROP TABLE IF EXISTS `$table`;");}
  return true;
}

function plugin_item_update_reservation($item) {
  global $DB;
  $query = "DELETE FROM `glpi_plugin_reservation_manageresa` WHERE `resaid` = '".$item->fields["id"]."';";
  $DB->query($query) or die("error on 'DELETE' into plugin_item_update_reservation : ". $DB->error());
  return true;
}



?>

