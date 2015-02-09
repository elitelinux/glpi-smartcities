<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//

/**
 * Fonction qui renvoie le tableau général des tables
 * @return array le tableau des tables
 */
function plugin_groupead_getListeTable()
    {
    $listeTable = Array();

    //==>glpi_plugin_groupeAD_profiles
    $arrayTable = Array("id"=>"int(11) NOT NULL PRIMARY KEY default '0' COMMENT 'RELATION to glpi_profiles (id)'",
            "right"=> "char(1) collate utf8_unicode_ci default NULL");
    $listeTable["glpi_plugin_groupead_profiles"] = $arrayTable;
    //==>glpi_plugin_groupeAD_ad
    $arrayTable = Array("id"=>"int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT",
            "serveur"=> "char(32) NOT NULL default ''",
            "dc"=> "char(64) NOT NULL default ''",
            "suffix"=> "char(64) NOT NULL default ''",
            "login"=> "char(32) NOT NULL default ''",
            "passwd"=> "char(32) NOT NULL default ''",
            "groupe"=> "char(72) NOT NULL default ''",
            "vie"=> "char(1) NOT NULL default '1'");
    $listeTable["glpi_plugin_groupead_ad"] = $arrayTable;
    //==>glpi_plugin_groupeAD_log
    $arrayTable = Array("id"=>"int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT",
            "id_item"=> "int(11) NOT NULL",
            "id_item"=> "int(11) NOT NULL",
            "type_item"=> "char(64) NOT NULL default ''",
            "technicien"=> "char(64) NOT NULL default ''",
            "date"=> "char(32) NOT NULL default ''",
            "action"=> "char(32) NOT NULL default ''",
            "groupe"=> "char(64) NOT NULL default ''");
    $listeTable["glpi_plugin_groupead_log"] = $arrayTable;
   
    return $listeTable;  
    }

/**
 * Renvoie la liste des premiers insert à faire
 * @return array le tableau des query
 */
function plugin_groupead_getFirstInsert()
    {
    $id = $_SESSION['glpiactiveprofile']['id'];
    $listeFI[] = "INSERT INTO glpi_plugin_groupead_profiles VALUES ('$id','w')";
    return $listeFI;
    }
    
/**
 * Fonction d'installation du plugin
 * @return boolean
 */
function plugin_groupead_install() 
    {
    global $DB;

    $miseAjour = false;
    //parcour du tableau des tables à créer
    foreach(plugin_groupead_getListeTable() as $id => $table)
        {
        if (!TableExists($id)) 
            {
            $requeteInsert = "";
            foreach($table as $key => $value)
                {
                if($requeteInsert != ""){$requeteInsert .= ",`".$key."` ". $value;} //si pas premier enregistrement on ajoute une virgule
                else{$requeteInsert .= "`".$key."` ". $value;}//si premier enregistrement pas de virgule
                }
            $query = "CREATE TABLE `".$id."` (".$requeteInsert.") ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
            $DB->query($query) or die($DB->error());
            }
        else{$miseAjour = true;}
        }
    
    //enregistrement spéciaux
    //creation du premier accès nécessaire lors de l'installation du plugin
    if($miseAjour){plugin_groupead_miseAjour();}
    else {foreach(plugin_groupead_getFirstInsert() as $query){$DB->query($query) or die($DB->error());}} 
        
    return true;
    }

/**
 * Fonction de désinstallation du plugin
 * @return boolean
 */
function plugin_groupead_uninstall() 
    {
    global $DB;
    foreach(plugin_groupead_getListeTable() as $table => $champ)
        {$DB->query("DROP TABLE IF EXISTS `$table`;");}
    return true;
    }
    
/**
 * Fonction de mise à jour des tables
 * @global type $DB
 * @return boolean
 */ 
function plugin_groupead_miseAjour()
    {
    global $DB;
    $return = true;
    
    foreach(plugin_groupead_getListeTable() as $table => $champ)
        {
        $query = "SHOW COLUMNS FROM ".$table;
        foreach($champ as $key => $value)
            {
            if ($result = $DB->query($query)) //vérification champs existantes
                {
                $booleanChamp = false;
                while ($row = $DB->fetch_row($result)) 
                    {if($row[0] == $key){$booleanChamp = true;break;}}
                if(!$booleanChamp)
                    {$DB->query("ALTER TABLE ".$table." ADD ".$key." ".$value);}
                }              
            }
        }
    return $return;
    }    
?>
