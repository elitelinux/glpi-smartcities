<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//


/**
 * Class de gestion AD
 * Gère toute la partie AD du plugin
 */
class PluginTwinsGestionAD extends CommonDBTM
{
    /**
     * Constructeur de la class
     * @return string
     */
    function __construct()
    {require_once($this->getAjaxAbsolutePath()."adldap/adLDAP.php");}
        
    /**
     * Fonction native GLPI
     * @param int $with_comment
     * @return string Nom du plugin
     */
    function getName($with_comment = 0)
    {return "twins";}
        
    /**
     * Récupérer le nom de la machine depuis GLPI
     * @param type $id
     * @return type null ou le nom de la machine
     */
    function getItemName($id)
    {
        global $DB;
        // Récupération du nom de la machine
        $name = null;
        $query = "SELECT name FROM glpi_computers WHERE id = '$id'";
        if ($result = $DB->query($query)){
            if ($DB->numrows($result) > 0) {
                $row = $DB->fetch_assoc($result);
                if (!empty($row['name'])) {$name = $row['name'];}
            }
        }
        return $name;
    }        
        
    /**
     * Récupérer le domaine de la machine depuis GLPI
     * @global type $DB
     * @param type $id
     * @return type -> null ou le domaine si enregistré
     */
    function getDomain($id)
    {
        global $DB;
        $domain = null;
        
        // Récupération du domaine de la machine
        $query = "SELECT t2.name FROM glpi_computers as t1, glpi_domains as t2 
                WHERE t1.id=$id AND t1.domains_id=t2.id";
        
        if ($result = $DB->query($query)){
            if ($DB->numrows($result) > 0){
                $row = $DB->fetch_assoc($result);
                if (!empty($row['name'])){$domain = $row['name'];}
            }
        }
        return $domain;
    }
        
    /**
     * Récupérer la configuration de tout les ldap enregistrés dans la base
     * @global type $DB
     * @return tableau de valeur (ou NULL si null)
     */
    function getLDAPConfig($id)
    {
        global $DB;

        if($this->getDomain($id) != NULL){
            // Récupération des informations LDAP
            $query = "SELECT * FROM glpi_plugin_twins_ad WHERE suffix='@".$this->getDomain($id)."'";
            $tableServeur = NULL;
            if ($result = $DB->query($query)){
                if ($DB->numrows($result) > 0){
                    $row = $DB->fetch_assoc($result);

                    if(!empty($row['serveur'])){
                        $tableServeur['serveur'] = $row['serveur'];
                        $tableServeur['dc'] = $row['dc'];
                        $tableServeur['suffix'] = $row['suffix'];
                        $tableServeur['login'] = $row['login'];
                        $tableServeur['passwd'] = $row['passwd'];
                        $tableServeur['groupe'] = $row['groupe'];
                    }
                }
            }
            return $tableServeur;
        }
        else{return NULL;}
    } 
        
    /**
     * Récupère les groupes depuis les AD
     * @global type $DB
     * @param type $id
     * @param type $appelant
     * @return un tableau 
     * [0] = les groupes de l'ordinateur
     * [1] = les autres groupes disponibles
     */
    function getGroupeAD($id)
    {
        $LDAPConfig = $this->getLDAPConfig($id);
        $groupeOrdinateur = NULL;
        $groupeAD = NULL;
        
        if($LDAPConfig != NULL && $this->testerAD($LDAPConfig)){
            $serveur[0]=$LDAPConfig['serveur'];
            $adldap = new adLDAP(array('base_dn'=>$LDAPConfig['dc'], 
                'account_suffix'=>$LDAPConfig['suffix'], 'domain_controllers'=>$serveur));
            $adldap->close();
            $adldap->setAdminUsername($LDAPConfig['login']);
            $adldap->setAdminPassword($LDAPConfig['passwd']);
            $adldap->connect();
            
            // Récupération des groupes de la machine
            $groupeOrdinateur = $adldap->computer()->groups($this->getItemName($id), FALSE);

            // Récupération des groupes de l'ad ()
            $groupeLDAP = $adldap->group()->info($LDAPConfig['groupe'], array("member"));
            $adldap->close();
            
            $listegroupeAD = NULL;
            for($i=0; $i<=count($groupeLDAP[0]["member"]); $i++){
                $splitGroupe = explode(",",$groupeLDAP[0]["member"][$i]);
                $splitSplitGroupe = explode("=",$splitGroupe[0]);
                $listegroupeAD[$i] = $splitSplitGroupe[1];
            }

            // Traitement du tableau des groupes AD
            if($listegroupeAD != NULL){
                if($groupeOrdinateur != NULL){
                    $k = 0;
                    for($i=0; $i<count($listegroupeAD); $i++){
                        $bool = false;
                        //Vérification si groupeAD est déjà dans groupeOrdi
                        for($j=0; $j<count($groupeOrdinateur); $j++){
                            if($listegroupeAD[$i] == $groupeOrdinateur[$j])
                                {$bool = true;}
                        }
                        $groupeAD[$k]=$listegroupeAD[$i];$k++;
                    }
                }
                else{$groupeAD = $listegroupeAD;}
            }
            $adldap->close();
        }
        $retour[0] = $groupeOrdinateur;
        $retour[1] = $groupeAD;
        return $retour;
    }  

    /**
     * ajoute ou supprime un ordinateur d'un groupe
     * @global type $DB
     * @param String $action (add/supp)
     * @param String $groupe (le groupe cible)
     * @param int $id (l'id de la machine
     */
    function changeGroupe($action, $groupe, $groupeModif, $id)
    {
        $newGroupe = null;
        $explodeGroupe = explode("|", $groupe);
        $explodeGroupe1 = explode(",", $explodeGroupe[0]);
        $explodeGroupe1 = array_diff($explodeGroupe1, array(""));
        foreach($explodeGroupe1 as $grp){$newGroupe[] = $grp;}
        $retourGroupe[0] = $newGroupe; $newGroupe = null;
        $explodeGroupe2 = explode(",", $explodeGroupe[1]);
        $explodeGroupe2 = array_diff($explodeGroupe2, array(""));
        foreach($explodeGroupe2 as $grp){$newGroupe[] = $grp;}
        $retourGroupe[1] = $newGroupe;
        
        $idGrp1 = 1; $idGrp2 = 0;
        if($action == "add"){$idGrp1 = 0; $idGrp2 = 1;}
        $retourGroupe[$idGrp1][] = $groupeModif;
        
        $result = array_diff($retourGroupe[$idGrp2], array($groupeModif));
        $retourGroupe[$idGrp2] = null; $retourGroupe[$idGrp2] = $result;

        return $retourGroupe;
    }
        
        
    function clonerOrdiAD($idOrdinateur,$idCloner, $groupe,$log)
    {
        require_once($this->getAjaxAbsolutePath()."adldap/adLDAP.php");
        $name = $this->getItemName($idOrdinateur);
        $LDAPConfig = $this->getLDAPConfig($idCloner);
        if($LDAPConfig != NULL && $this->testerAD($LDAPConfig)){
            $serveur[0]=$LDAPConfig['serveur'];
            $adldap = new adLDAP(array('base_dn'=>$LDAPConfig['dc'], 
                'account_suffix'=>$LDAPConfig['suffix'], 'domain_controllers'=>$serveur));
            $adldap->close();
            $adldap->setAdminUsername($LDAPConfig['login']);
            $adldap->setAdminPassword($LDAPConfig['passwd']);
            $adldap->connect();
            $computerInfo = $adldap->computer()->info($name, array("dn"));
            if($computerInfo["count"] == 0){// si l'ordinateur n'existe pas dans l'ad on le créé
                $attributes["cn"] = $name;
                $container = array("Computers");
                $attributes["container"] = $container;
                if($adldap->computer()->create($attributes)){
                    $log = "Ordinateur créé dans l'AD: ".$LDAPConfig['suffix'];
                    $this->setLog($idOrdinateur,'Computer',$log);
                }
                else{
                    $log = "Erreur lors de la création de l'ordinateur dans l'AD: ".$LDAPConfig['suffix'];
                    $this->setLog($idOrdinateur,'Computer',$log);
                }
            }
            else{
                $log = "L'ordinateur existe déjà dans l'AD: ".$LDAPConfig['suffix'];
                $this->setLog($idOrdinateur,'Computer',$log);
            }
            if($groupe != null){
                $explodeGroupe = explode("|", $groupe);
                if($explodeGroupe[0] != null){
                    $explodeGroupe1 = explode(",", $explodeGroupe[0]);
                    foreach ($explodeGroupe1 as $value){
                        $adldap->group()->addUser($value, "CN=".$name.",CN=Computers,".$LDAPConfig['dc']);
                        $log = "L'ordinateur a été ajouté dans le groupe: ".$value." de l'AD: ".$LDAPConfig['suffix'];
                        $this->setLog($idOrdinateur,'Computer',$log);
                    }
                }
            }
        }
        return true;
    }
    
    /**
     * Enregistre une entrée de log
     * @global type $DB
     * @param int $idOrdinateur
     * @param string $typeItem
     * @param string $info
     */
    function setLog($idOrdinateur,$typeItem,$info)
    {
        global $DB;
        $DB->query("INSERT INTO glpi_plugin_twins_log 
        (id_item,type_item,technicien,date,info) VALUES ('".
        $idOrdinateur."','$typeItem','".$_SESSION['glpiname']."','"
            .date('j-m-Y|H:m:s')."',\"".$info."\")") or die($DB->error());
    }
        
    /**
     * Test la connexion à l'ad
     * @param array $valeur
     * $valeur['login']
     * $valeur['passwd']
     * $valeur['dc']
     * $valeur['suffix']
     * $valeur['serveur']
     * @return Boolean
     */
    function testerAD($valeur)
    {
        $serveur[0]=$valeur['serveur'];
        $adldap = new adLDAP(array('base_dn'=>$valeur['dc'], 'account_suffix'=>$valeur['suffix'],
            'domain_controllers'=>$serveur));
        return $adldap->authenticate($valeur['login'], $valeur['passwd']);
    }     

    /**
     * renvoie l'id d'un utilisateur
     * @global type $DB
     * @param String $name le login de l'utilisateur recherché
     * @return int l'id de l'utilisateur, false si pas trouvé
     */
    function getUserId($name)
    {
        global $DB;
        $query = "SELECT id FROM glpi_users WHERE name='$name'";
        
        if ($result = $DB->query($query)){
            if ($DB->numrows($result) > 0){
                $row = $DB->fetch_assoc($result);
                if (!empty($row['id'])){return $row['id'];}
            }
        }
        return false;
    }   
//=========================== Méthodes générales =============================//        
    /**
     * Récupère le chemin absolue de l'instance glpi depuis l'appel ajax de groupead
     * @return String : le chemin absolue (racine principale)
     */
    function getAjaxAbsolutePath()
    {return str_replace("ajax/twins.ajax.php", "", $_SERVER['SCRIPT_FILENAME']);}
         
    }
?>
