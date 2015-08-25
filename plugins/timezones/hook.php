<?php


/**
 * Summary of plugin_timezones_install
 * @return true or die!
 */
function plugin_timezones_install() {
	global $DB ;

	if (!TableExists("glpi_plugin_timezones_users")) {
		$query = "  CREATE TABLE `glpi_plugin_timezones_users` (
	                    `id` INT(11) NOT NULL AUTO_INCREMENT,
	                    `users_id` INT(11) NOT NULL,
	                    `timezone` VARCHAR(50) NOT NULL,
	                    PRIMARY KEY (`id`),
	                    UNIQUE INDEX `users_id` (`users_id`),
	                    INDEX `timezone` (`timezone`)
                    )
                    COLLATE='utf8_general_ci'
                    ENGINE=InnoDB                    
                    ;
			";
        
		$DB->query($query) or die("error creating glpi_plugin_timezones_users" . $DB->error());    
        
	} else if( !FieldExists("glpi_plugin_timezones_users","users_id") ) {
        $query = "  ALTER TABLE `glpi_plugin_timezones_users`
	                    ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
	                    CHANGE COLUMN `id` `users_id` INT(11) NOT NULL AFTER `id`,
	                    DROP PRIMARY KEY,
	                    ADD PRIMARY KEY (`id`),
	                    ADD UNIQUE INDEX `users_id` (`users_id`);
                ";

		$DB->query($query) or die("error altering glpi_plugin_timezones_users" . $DB->error());    

    }

    if (!TableExists("glpi_plugin_timezones_dbbackups")) {
		$query = "  CREATE TABLE `glpi_plugin_timezones_dbbackups` (
	                `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	                `table_name` VARCHAR(255) NULL ,
	                `alter_table` TEXT NULL
                )
                COLLATE='utf8_general_ci'
                ENGINE=InnoDB;
			";
        
		$DB->query($query) or die("error creating glpi_plugin_timezones_dbbackups" . $DB->error());        
	}

    if (!TableExists("glpi_plugin_timezones_tasks_localtimes")) {
		$query = " CREATE TABLE `glpi_plugin_timezones_tasks_localtimes` (
	                    `id` INT(11) NOT NULL AUTO_INCREMENT,
	                    `items_type` VARCHAR(50) NOT NULL,
	                    `items_id` INT(11) NOT NULL,
	                    `begin` VARCHAR(20) NULL DEFAULT NULL COMMENT 'In order to keep local time',
	                    `end` VARCHAR(20) NULL DEFAULT NULL COMMENT 'In order to keep local time',
	                    PRIMARY KEY (`id`),
	                    UNIQUE INDEX `items_type_items_id` (`items_type`, `items_id`)
                    )
                    COLLATE='utf8_general_ci'
                    ENGINE=InnoDB
                    ;
			";
        
		$DB->query($query) or die("error creating glpi_plugin_timezones_tasks_localtimes" . $DB->error());        
	}

    

        // here we update the time_zones mySQL tables.
        // with data from PHP module: php_timezonedb. See: https://pecl.php.net/package/timezonedb
    //$query = "TRUNCATE `mysql`.`time_zone`;";
    //$DB->query( $query ) or die("error truncating mysql.time_zone" . $DB->error()); 
    //$query = "TRUNCATE `mysql`.`time_zone_name`;";
    //$DB->query( $query ) or die("error truncating mysql.time_zonetime_zone_name" . $DB->error()); 
    //$query = "TRUNCATE `mysql`.`time_zone_transition`;";
    //$DB->query( $query ) or die("error truncating mysql.time_zone_transition" . $DB->error()); 
    //$query = "TRUNCATE `mysql`.`time_zone_transition_type`;";
    //$DB->query( $query ) or die("error truncating mysql.time_zone_transition_type" . $DB->error()); 
    //$query = "TRUNCATE `mysql`.`time_zone_leap_second`;";
    //$DB->query( $query ) or die("error truncating mysql.time_zone_leap_second" . $DB->error()); 

    //    $timezones = DateTimeZone::listIdentifiers( ) ;
    //    foreach( $timezones as $key => $tz ){
    //        $key++ ;
    //        // time_zone
    //        $query = "INSERT INTO `mysql`.`time_zone` (`Time_zone_id`, `Use_leap_seconds`) VALUES ($key, 'N');";
    //        $DB->query( $query ) or die("error inserting data into mysql.time_zone" . $DB->error()); 

    //        $query = "INSERT INTO `mysql`.`time_zone_name` (`Name`, `Time_zone_id`) VALUES ('$tz', $key);";
    //        $DB->query( $query ) or die("error inserting data into mysql.time_zone_name" . $DB->error()); 

    //        $tz_trans = (new DateTimeZone( $tz ))->getTransitions() ;
    //        $trans_array = array( ) ;
    //        $trans_type_id=0;
    //        foreach($tz_trans as $key_trans => $trans) {
    //            $trans_key = $trans['offset'].", ".($trans['isdst']?1:0).", '".$trans['abbr']."'";
    //            if( !in_array( $trans_key, $trans_array ) ){
    //                $trans_array[$trans_type_id++] = $trans_key ;
    //            }

    //            $query = "REPLACE INTO `mysql`.`time_zone_transition` (`Time_zone_id`, `Transition_time`, `Transition_type_id`) VALUES ($key, ".$trans['ts'].", ".$trans_type_id.");" ;
    //            $DB->query( $query ) or die("error inserting data into mysql.time_zone_transition" . $DB->error());
    //        }
    //        foreach( $trans_array as $trans_type_id => $trans_key ){
    //            $trans_type_id++;
    //            $query = "INSERT INTO `mysql`.`time_zone_transition_type` (`Time_zone_id`, `Transition_type_id`, `Offset`, `Is_DST`, `Abbreviation`) VALUES ($key, $trans_type_id, $trans_key);";
    //            $DB->query( $query ) or die("error inserting data into mysql.time_zone_transition_type" . $DB->error());
    //        }
            
    //    }

    


        
	return true;
}

function plugin_timezones_uninstall() {
	global $DB;

    	    
	return true;
}


function plugin_init_session_timezones() {
    if( !isset($_SESSION["glpicronuserrunning"]) || (Session::getLoginUserID() != $_SESSION["glpicronuserrunning"])) {
        $pref = new PluginTimezonesUser;
        $tzid = $pref->getIDFromUserID( Session::getLoginUserID() ) ;

        if( $tzid && $pref->getFromDB( $tzid ) ) {
            setTimeZone( $pref->fields['timezone'] ) ;
        }        
    }
}

/**
 * Summary of setTimeZone
 * @param string $tz timezone to be set like 'Europe/Paris'
 */
function setTimeZone( $tz ) {
    global $DB ;
    $_SESSION['glpitimezone'] = $tz; // could be redondant, but anyway :)
    date_default_timezone_set( $tz ) or Toolbox::logInFile("php-errors", "Can't set tz: $tz for ".Session::getLoginUserID()."\n");          
    $DB->query("SET SESSION time_zone = '$tz'" ) or Toolbox::logInFile("php-errors", "Can't set tz: $tz - ". $DB->error()."\n"); //die ("Can't set tz: ". $DB->error());
    $_SESSION['glpi_currenttime'] = date("Y-m-d H:i:s");
}

function plugin_timezones_postinit( ) {
    if( isset($_SESSION['glpitimezone']) ) {
        setTimeZone( $_SESSION['glpitimezone'] ) ;       
    }        
}


function plugin_item_add_update_timezones_tasks(CommonITILTask $parm){
    global $DB;
    $itemType = $parm->getType() ;
    $begin = (isset($parm->fields['begin'])?$parm->fields['begin']:'');
    $end = (isset($parm->fields['end'])?$parm->fields['end']:'');
    $query = "REPLACE INTO `glpi_plugin_timezones_tasks_localtimes` (`items_type`, `items_id`, `begin`, `end`) VALUES ('$itemType', ".$parm->getID().", '$begin', '$end');";
    $DB->query( $query ) ;

}


function plugin_item_add_update_timezones_dbconnection(Config $parm) {
    $slaveDB = DBConnection::getDBSlaveConf( ) ;
    if( $slaveDB ) {
        $host = $slaveDB->dbhost ;
        $user = $slaveDB->dbuser ;
        $password = $slaveDB->dbpassword ;
        $DBname = $slaveDB->dbdefault ;
        unset( $slaveDB  ) ;
        timezones_createSlaveConnectionFile($host, $user, $password, $DBname) or Toolbox::logInFile('php-errors', "timezones: Can't create config_db_slave.php\n") ;
    }

}

/**
    * Create slave DB configuration file
    *
    * @param host the slave DB host(s)
    * @param user the slave DB user
    * @param password the slave DB password
    * @param DBname the name of the slave DB
    *
    * @return boolean for success
   **/
   function timezones_createSlaveConnectionFile($host, $user, $password, $DBname) {

      $DB_str = "<?php \n class DBSlave extends DBmysql { \n var \$slave = true; \n var \$dbhost = ";
      $host   = trim($host);
      if (strpos($host, ' ')) {
         $hosts = explode(' ', $host);
         $first = true;
         foreach ($hosts as $host) {
            if (!empty($host)) {
               $DB_str .= ($first ? "array('" : ",'").$host."'";
               $first   = false;
            }
         }
         if ($first) {
            // no host configured
            return false;
         }
         $DB_str .= ");\n";

      } else {
         $DB_str .= "'$host';\n";
      }
      $DB_str .= " var \$dbuser = '" . $user . "'; \n var \$dbpassword= '" .rawurlencode($password) . "'; \n var \$dbdefault = '" . $DBname . "'; 
    function __construct(\$choice=NULL) { 
        global \$DB;
        parent::connect(\$choice); 
        if (\$this->connected && isset(\$_SESSION['glpitimezone']) ) { 
            \$dbInit = isset( \$DB ) ; 
            if( !\$dbInit ) {
                \$DB=\$this;
            }
            \$plug = new Plugin;            if( \$plug->isActivated('timezones' ) ) {
                \$tz = \$_SESSION['glpitimezone'] ; 
                \$this->query(\"SET SESSION time_zone = '\$tz'\" ) or Toolbox::logInFile(\"php-errors\", \"Can't set tz: \$tz - \". \$this->error().\"\\n\"); 
            }
            if( !\$dbInit ) {
                unset(\$DB) ;
            }        }
    }                  
} \n ?>";
      $fp      = fopen(GLPI_CONFIG_DIR . "/config_db_slave.php", 'wt');
      if ($fp) {
         $fw = fwrite($fp, $DB_str);
         fclose($fp);
         return true;
      }
      return false;
   }

   function plugin_timezones_getAddSearchOptions( $itemtype ) {
       global $LANG;

       $sopt = array();
       if( $itemtype == 'User' ) {
            $sopt[11001]['table']     = 'glpi_plugin_timezones_users';
            $sopt[11001]['field']     = 'timezone';
            $sopt[11001]['linkfield'] = 'plugin_timezones_users_timezone' ;
            $sopt[11001]['massiveaction'] = true;
            $sopt[11001]['name']      = $LANG['timezones']['item']['tab'] ;
            $sopt[11001]['datatype']       = 'dropdown';
            $sopt[11001]['forcegroupby'] = true ;       
            $sopt[11001]['joinparams'] = array('jointype' => 'child');
            $sopt[11001]['searchtype']    = 'contains';

        }
        return $sopt;       
   }

   //function plugin_timezones_addLeftJoin($type,$ref_table,$new_table,$linkfield,&$already_link_tables) {

   //    switch ($type){
           
   //        case 'User':
   //            switch ($new_table){

   //                case "glpi_plugin_timezones_users" : 
   //                    $out= " LEFT JOIN `glpi_plugin_timezones_users` 
   //                     ON (`$ref_table`.`id` = `glpi_plugin_timezones_users`.`id` ) ";
   //                    return $out;
   //            }
               
   //            return "";
   //    }
       
   //    return "";
   //}

   //function plugin_pre_item_update_timezones_user(CommonDBTM $parm){
   //    global $DB;

   //    if($parm->getType() == 'User' && isset( $parm->input['plugin_timezones_users_timezone']) ) {
   //        $query = "REPLACE INTO `glpi_plugin_timezones_users` (`users_id`, `timezone`) VALUES (".$parm->getID().", '".$parm->input['plugin_timezones_users_timezone']."');";
   //        $DB->query( $query ) ;
   //    }
   //}

    function plugin_timezones_MassiveActionsFieldsDisplay($options=array()) {
       //$type,$table,$field,$linkfield

       $table     = $options['options']['table'];
       $field     = $options['options']['field'];
       $linkfield = $options['options']['linkfield'];

       if ($options['itemtype'] == 'User' ) {
           // Table fields
           switch ($table.".".$field) {
               case 'glpi_plugin_timezones_users.timezone' :
                   $timezones = PluginTimezonesUser::getTimezones( ) ;
                    // default timezone is the one of PHP                   
                   Dropdown::showFromArray('plugin_timezones_users_timezone', $timezones, array('value' => ini_get('date.timezone') )); 
                   // Need to return true if specific display
                   return true;
           }

       } 

       // Need to return false on non display item
       return false;
   }

    //function plugin_timezones_MassiveActionsProcess($data) {
    //    global $LANG, $DB;
    //    switch ($data['action']) {

    //        case "plugin_timezones_users_timezone" :
    //            if ($data['itemtype'] == 'User') {
    //                foreach ($data["item"] as $key => $val) {
    //                    if ($val == 1) {
    //                        $tzUser = new PluginTimezonesUser ;
    //                        $tzUser->getFromDB( $key ) ;
                            


                            
    //                    }
    //                }
    //            }
    //            break;
    //    }
    //    return ;

    //}

    //function plugin_timezones_MassiveActions($type) {
    //    global $LANG;

    //    switch ($type) {
    //        case 'User' :
    //            return array('plugin_timezones_users_timezone' => 'Update Time Zone');
    //    }

    //    return array();
    //}

    //function plugin_timezones_MassiveActionsDisplay($options) {
    //    global $LANG;

    //    switch ($options['itemtype']) {
    //        case 'User' :
    //            switch ($options['action']) {
                    
    //                case "plugin_timezones_users_timezone" :
    //                    $timezones = PluginTimezonesUser::getTimezones( ) ;
    //                    // default timezone is the one of PHP                   
    //                    Dropdown::showFromArray('timezone', $timezones, array('value' => ini_get('date.timezone')
    //                                                                             )); 
    //                    echo "&nbsp;<input type='submit' name='massiveaction' class='submit' ".
    //                    "value='".$LANG['buttons'][2]."'>";
    //                    break;

    //            }
    //            break;

    //    }

    //    return "";
    //}

?>