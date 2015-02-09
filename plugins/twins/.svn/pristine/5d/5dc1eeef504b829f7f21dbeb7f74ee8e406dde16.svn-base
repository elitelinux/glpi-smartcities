<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//

/**
 * Class pour la partie gestion de la configuration
 */
class PluginTwinsConfigold extends CommonDBTM
{
    /**
     * Récupère les information sur les AD enregistrés
     * Retour un tableau avec ID + valeur
     * @global type $DB
     * @return string
     */
    function getAD()
    {
        global $DB;
        
        $query = "SELECT * FROM glpi_plugin_twins_ad WHERE vie='1'";
        if ($result = $DB->query($query)){
            if ($DB->numrows($result) > 0){
                $i = 0;
                while ($row = $DB->fetch_assoc($result)) {
                    if (!empty($row['id'])){$ad['id'] = $row['id'];}
                    else{$ad['id'] = "";}
                    if (!empty($row['serveur'])){$ad['serveur'] = $row['serveur'];}
                    else{$ad['serveur'] = "";}
                    if (!empty($row['dc'])){$ad['dc'] = $row['dc'];}
                    else{$ad['dc'] = "";}
                    if (!empty($row['suffix'])){$ad['suffix'] = $row['suffix'];}
                    else{$ad['suffix'] = "";}
                    if (!empty($row['login'])){$ad['login'] = $row['login'];}
                    else{$ad['login'] = "";}
                    if (!empty($row['passwd'])){$ad['passwd'] = $row['passwd'];}
                    else{$ad['passwd'] = "";}
                    if (!empty($row['groupe'])){$ad['groupe'] = $row['groupe'];}
                    else{$ad['groupe'] = "";}
                    $retour[$i] = $ad;
                    $i++;
                }
            }  
        }
        return $retour;
    }
    
    /**
     * Créé un enregistrement dans la base pour un nouvel AD
     */
    function setAD()
    {
        global $DB;
        $query = "INSERT INTO glpi_plugin_twins_ad (serveur,dc,suffix,login,passwd,groupe,vie) "
            . "VALUES ('','','','','','','1')";
        $DB->query($query) or die($DB->error());
    }
 
    /**
     * Enregistre la valeur d'une information concernant un AD
     * @global type $DB
     * @param type $identifiant
     * @param type $valeur
     */
    function setValeurInfoAD($identifiant,$valeur)
    {
        global $DB;
        $id = substr($identifiant, -1);
        $champ = substr($identifiant, 0, -1);
        $valid = true;
        
        if($champ == "valider:"){
            //Vérification de la cohérence des données (serveur, dc et suffix)
            foreach ($valeur as $key => $value){
                if($key == "serveur" || $key == "dc" || $key == "suffix"){
                    $query = "SELECT $key FROM glpi_plugin_twins_ad WHERE id!='$id'";
                    if ($result = $DB->query($query)){
                        if ($DB->numrows($result) > 0){
                            while ($row = $DB->fetch_assoc($result)){
                                if(!empty($row[$key]) && $row[$key]==$value){
                                    $valid = false;
                                    echo "L'élément $key => $value n'a pas pu être modifié car il est enregistré pour un autre serveur.";
                                } 
                            }
                        }
                    }
                }
            }
            if($valid){
                $query = "UPDATE glpi_plugin_twins_ad SET serveur='".$valeur["serveur"]."',
                    dc='".$valeur["dc"]."', suffix='".$valeur["suffix"]."',login='".$valeur["login"]."'
                    , passwd='".$valeur["passwd"]."', groupe='".$valeur["groupe"]."'  WHERE id='$id'";
                $DB->query($query);
                echo "Les informations ont été enregistrées avec succès.";
            }
        }
        else{
            //Vérification de la cohérence des données (serveur, dc et suffix)
            if($champ == "serveur" || $champ == "dc" || $champ == "suffix"){
                $query = "SELECT $champ FROM glpi_plugin_twins_ad WHERE id!='$id'";
                if ($result = $DB->query($query)){
                    if ($DB->numrows($result) > 0){
                        while ($row = $DB->fetch_assoc($result)){
                            if(!empty($row[$champ]) && $row[$champ]==$valeur){
                                $valid = false;
                                echo "L'élément n'a pas pu être modifié car il est enregistré pour un autre serveur.";
                            }
                        }
                    }
                }
            }
            if($valid){
                //Modification de la valeur dans la bd
                $query = "UPDATE glpi_plugin_twins_ad SET $champ='$valeur' WHERE id='$id'";
                $DB->query($query);
                echo "L'élément a été modifié avec succès.";
            }
        }
    }
    
    /**
     * Test la connexion à l'ad
     * @param array $valeur
     * $valeur['login']
     * $valeur['passwd']
     * $valeur['dc']
     * $valeur['suffix']
     * $valeur['serveur']
     */
    function testerAD($valeur)
    {
        // instanciation de adldap
        $cheminAdldap = $this->getAbsolutePath()."adldap";
        require_once($cheminAdldap . '/adLDAP.php');

        $serveur[0]=$valeur['serveur'];
        $adldap = new adLDAP(array('base_dn'=>$valeur['dc'], 'account_suffix'=>$valeur['suffix'], 
            'domain_controllers'=>$serveur));

        $authUser = $adldap->authenticate($valeur['login'], $valeur['passwd']);
        $adldap->close();
        if ($authUser == true) {echo "Test de connexion réussit";}
        else {echo "Test de connexion échoué";}
    }    
        
    /**
    * Récupère le chemin absolue de l'instance glpi
    * @return String : le chemin absolue (racine principale)
    */
    function getAbsolutePath()
    {return str_replace("ajax/config.ajax.php", "", $_SERVER['SCRIPT_FILENAME']);}     
}
?>
