<?php

function plugin_chat_install(){
	
	global $DB, $LANG;
	
	//$DB->runFile(GLPI_ROOT ."/plugins/chat/sql/lhc.sql");	
	
	$query = "CREATE DATABASE IF NOT EXISTS lhc";
	$DB->query($query) or die("error creating chat database " . $DB->error());
		
	return true;
}

function plugin_chat_uninstall(){

	global $DB;

	$drop = "DROP DATABASE lhc";
	$DB->query($drop); 	

	return true;

}

?>
