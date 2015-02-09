<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//

/**
 * Class principale du projet
 * Gère toute la partie groupeAD d'une machine
 */
class PluginGroupeadGroupeadold extends CommonDBTM
    {
    
    /**
     * Fonction native GLPI
     * @param int $with_comment
     * @return string Nom du plugin
     */
    function getName($with_comment = 0)
        {return "grouepad";}  
    
    /**
     * Fonction native GLPI
     * @param CommonGLPI $item
     * @param int $withtemplate
     * @return string Nom du Tab
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) 
        {
        $profile = new PluginGroupeadProfileold();
        if ($profile->estAutorise())
            {if ($item->getType() == 'Computer'){return "GroupeAD";}}
        return '';
        }
    
    /**
     * Fonction native GLPI
     * @param CommonGLPI $item
     * @param int $tabnum
     * @param int $withtemplate
     * @return boolean
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) 
        {
        if ($item->getType() == 'Computer') 
            {
            $profile = new PluginGroupeadProfileold();
            if ($profile->estAutorise())
                {
                $groupeAD = new self();
                $ID = $item->getField('id');
                $Name = $item->getField('name');
                // j'affiche le formulaire
                $groupeAD->showForm($ID, $Name);
                }
            }
        return true;
        }  
    
    /**
     * Fonction qui affiche le formulaire du plugin
     * @param type $id
     * @param type $options
     * @return boolean
     */
    function showForm($id)     
        {
        global $DB;
        $cheminSmarty = $this->getAbsolutePath()."plugins/groupead/Smarty";

        // définition de l'emplacement de la bibliothèque
        define('SMARTY_DIR', $cheminSmarty."/libs/");
        
        // instanciation de la class Smarty
        require_once(SMARTY_DIR . 'Smarty.class.php');
        $smarty = new Smarty();
        
        // définition des dossiers Smarty
        $smarty->template_dir = $cheminSmarty."/templates/";
        $smarty->compile_dir = $cheminSmarty."/templates_c/";
        $smarty->config_dir = $cheminSmarty."/configs/";
        $smarty->cache_dir = $cheminSmarty."/cache/";

        if($this->getDomain($id)!=null){ //vérifiaction si machine dans un domaine
            $groupe = $this->getGroupeAD($id);
            asort($groupe[0]); asort($groupe[1]);
            $smarty->assign('erreur','null');
            $smarty->assign('groupeOrdinateur',$groupe[0]);
            $smarty->assign('groupeAD',$groupe[1]);
        }
        else{
            $smarty->assign('erreur','nodomain');
            $config = new PluginGroupeadConfig();
            $listeAD = null;
            foreach($config->getAD() as $AD){
                $listeAD[] = $AD["suffix"];
            }
            $smarty->assign('listeAD',$listeAD);
        }
        $smarty->assign('id',$id);
        $smarty->assign('httpPath', $this->getHttpPath());
        $smarty->assign('endform', HTML::closeForm(false));
        $smarty->display('groupeadold.tpl');
        } 
        
    /**
     * Récupère le chemin absolue de l'instance glpi
     * @return String : le chemin absolue (racine principale)
     */
    function getAbsolutePath()
        {return str_replace("ajax/common.tabs.php", "", $_SERVER['SCRIPT_FILENAME']);}

    /**
     * Récupère le chemin absolue de l'instance glpi depuis l'appel ajax de groupead
     * @return String : le chemin absolue (racine principale)
     */
    function getAjaxAbsolutePath()
        {return str_replace("ajax/groupead.ajax.php", "", $_SERVER['SCRIPT_FILENAME']);}
        
    /**
     * Récupère le chemin http absolu de l'application glpi
     * @return string : le chemin http absolue de l'application
     */
    function getHttpPath()
        {
        $temp = explode("/",$_SERVER['HTTP_REFERER']);
        $Ref = "";
        foreach ($temp as $value)
            {
            if($value != "front"){$Ref.= $value."/";}
            else{break;}
            }
        return $Ref;
        }
        
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
        if ($result = $DB->query($query))
            {
            if ($DB->numrows($result) > 0) 
                {
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
        
        if ($result = $DB->query($query))
            {
            if ($DB->numrows($result) > 0) 
                {
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

        if($this->getDomain($id) != NULL)
            {
            // Récupération des informations LDAP
            $query = "SELECT * FROM glpi_plugin_groupead_ad WHERE suffix='@".$this->getDomain($id)."'";
            $tableServeur = NULL;
            if ($result = $DB->query($query))
                {
                if ($DB->numrows($result) > 0)
                    {
                    $row = $DB->fetch_assoc($result);

                    if(!empty($row['serveur']))
                        {
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
     * Récupérer la configuration d'un ldap enregistrés dans la base via son suffix
     * @global type $DB
     * @return tableau de valeur
     */
    function getLDAP($suffix)
        {
        global $DB;

        // Récupération des informations LDAP
        $query = "SELECT * FROM glpi_plugin_groupead_ad WHERE suffix='$suffix'";
        $tableServeur = NULL;
        if ($result = $DB->query($query))
            {
            if ($DB->numrows($result) > 0)
                {
                $row = $DB->fetch_assoc($result);

                if(!empty($row['serveur']))
                    {
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
        
    /**
     * Récupère les groupes depuis les AD
     * @global type $DB
     * @param type $id
     * @param type $appelant
     * @return un tableau 
     * [0] = les groupes de l'ordinateur
     * [1] = les autres groupes disponibles
     */
    function getGroupeAD($id,$appelant="null")
        {
        // instanciation de adldap
        if($appelant === "ajax")
            {$require_adldap = $this->getAjaxAbsolutePath()."adldap/adLDAP.php";}
        else{$require_adldap = $this->getAbsolutePath()."plugins/groupead/adldap/adLDAP.php";}
        require_once($require_adldap);
        $LDAPConfig = $this->getLDAPConfig($id);
        $groupeOrdinateur = NULL;
        $groupeAD = NULL;
        
        if($LDAPConfig != NULL && $this->testerAD($LDAPConfig, $require_adldap))
            {
            $serveur[0]=$LDAPConfig['serveur'];
            $adldap = new adLDAP(array('base_dn'=>$LDAPConfig['dc'], 'account_suffix'=>$LDAPConfig['suffix'], 'domain_controllers'=>$serveur));
            
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
            //for($i=0; $i<=count($groupeLDAP); $i++)
            for($i=0; $i<=$groupeLDAP[0]["member"]["count"]; $i++)
                {
                $splitGroupe = explode(",",$groupeLDAP[0]["member"][$i]);
                $splitSplitGroupe = explode("=",$splitGroupe[0]);
                if($splitSplitGroupe[1] != ""){
                $listegroupeAD[$i] = $splitSplitGroupe[1];}
                }

            // Traitement du tableau des groupes AD
            if($listegroupeAD != NULL)
                {
                if($groupeOrdinateur != NULL)
                    {
                    $k = 0;
                    for($i=0; $i<count($listegroupeAD); $i++)
                        {
                        $test = false;
                        for($j=0; $j<count($groupeOrdinateur); $j++)
                            {
                            if($listegroupeAD[$i] == $groupeOrdinateur[$j])
                                {$test = true;}
                            }
                        if(!$test)
                            {
                            $groupeAD[$k]=$listegroupeAD[$i];
                            $k++;
                            }
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
     * @param type $action
     * @param type $groupe
     * @param type $id
     */
    function changeGroupe($action, $groupe, $id)
        {
        global $DB;        
        // instanciation de adldap
        $cheminAdldap = $this->getAjaxAbsolutePath()."adldap/adLDAP.php";
        require_once($cheminAdldap);
  
        $LDAPConfig = $this->getLDAPConfig($id);
 
        if($LDAPConfig != NULL && $this->testerAD($LDAPConfig, $cheminAdldap))
            {
            // Connection à l'AD
            $serveur[0]=$LDAPConfig['serveur'];
            $adldap = new adLDAP(array('base_dn'=>$LDAPConfig['dc'], 'account_suffix'=>$LDAPConfig['suffix'], 'domain_controllers'=>$serveur));
            
            $adldap->close();
            $adldap->setAdminUsername($LDAPConfig['login']);
            $adldap->setAdminPassword($LDAPConfig['passwd']);
            $adldap->connect();
            
            $computerInfo = $adldap->computer()->info($this->getItemName($id), array("dn"));

            // Ajout ou suppression de l'ordinateur du groupe
            if($action == "add")
                {$groupeOrdinateur = $adldap->group()->addUser($groupe, $computerInfo[0]["dn"]);}
            else{$groupeOrdinateur = $adldap->group()->removeUser($groupe, $computerInfo[0]["dn"]);}
            
            // Enregistrement de l'action dans la base de données
            $technicien = $_SESSION["glpiname"];
            $date = date('j-m-Y');
            $heure = date('H:m:s');
            $query = "INSERT INTO glpi_plugin_groupead_log VALUES ('','$id',
                     'Computer','$technicien','$date|$heure','$action','$groupe')";
            $DB->query($query);
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
     * @return Boolean
     */
    function testerAD($valeur, $cheminAdldap = null)
        {
        // instanciation de adldap
        if($cheminAdldap == null)
            {$cheminAdldap = $this->getAbsolutePath()."plugins/groupead/adldap/adLDAP.php";}
        require_once($cheminAdldap);
        $serveur[0]=$valeur['serveur'];
        $adldap = new adLDAP(array('base_dn'=>$valeur['dc'], 'account_suffix'=>$valeur['suffix'], 'domain_controllers'=>$serveur));
        //$adldap->close();
        return $adldap->authenticate($valeur['login'], $valeur['passwd']);
        } 
  
    /**
     * Créer un ordinateur dans lAD
     * @global db $DB
     * @param int $id id le d'ordinateur
     * @param string $ad suffix de l'ad
     * @return boolean
     */
    function createComputer($id,$ad){
        global $DB;
        // instanciation de adldap
        $cheminAdldap = $this->getAjaxAbsolutePath()."adldap/adLDAP.php";
        require_once($cheminAdldap);
        $LDAPConfig = $this->getLDAP($ad);
        $return = false;
        if($LDAPConfig != NULL && $this->testerAD($LDAPConfig, $cheminAdldap)){
            // Connection à l'AD
            $serveur[0]=$LDAPConfig['serveur'];
            $adldap = new adLDAP(array('base_dn'=>$LDAPConfig['dc'], 'account_suffix'=>$LDAPConfig['suffix'], 'domain_controllers'=>$serveur));
            
            $adldap->close();
            $adldap->setAdminUsername($LDAPConfig['login']);
            $adldap->setAdminPassword($LDAPConfig['passwd']);
            $adldap->connect();

            
            $attributes["cn"] = $this->getItemName($id);
            $container = array("Computers");
            $attributes["container"] = $container;
            $idDomaine = null;
            if($adldap->computer()->create($attributes)){
                //modification de la fiche glpi
                $query = "SELECT id FROM glpi_domains WHERE name='".trim(substr($ad,1))."'";
                if ($result = $DB->query($query)){
                    if ($DB->numrows($result) > 0) {
                        $row = $DB->fetch_assoc($result);
                        if (!empty($row['id'])){$idDomaine = $row['id'];}
                        }
                }
                $query = "UPDATE glpi_computers SET ";
                $query .= "domains_id='".$idDomaine."'";
                $query .= " WHERE id='".$id."'";
                if($DB->query($query)){
                    $return = true;
                } 
            }
            
            // Enregistrement de l'action dans la base de données
            $technicien = $_SESSION["glpiname"];
            $date = date('j-m-Y');
            $heure = date('H:m:s');
            $query = "INSERT INTO glpi_plugin_groupead_log VALUES ('','$id',
                     'Computer','$technicien','$date|$heure','createComputer','$ad')";
            $DB->query($query);
        }
    return $return;
    }        
        
    /**
     * Réupère l'historique du peuplement d'une machine
     * @global type $DB
     * @param type $id
     * @return type
     */
    function getHistorique($id)
        {
        global $DB; 
        
        $query = "SELECT * FROM glpi_plugin_groupead_log WHERE id_item = '$id' AND type_item = 'Computer'";
        $retour = null; $i=0;
        if ($result = $DB->query($query))
            {
            if ($DB->numrows($result) > 0)
                {
                while ($row = $DB->fetch_assoc($result)) 
                    {
                    if (!empty($row['technicien'])) 
                        {$retour[$i]['technicien'] = $row['technicien'];}
                    if (!empty($row['date'])) 
                        {$retour[$i]['date'] = $row['date'];}
                    if (!empty($row['action'])) 
                        {$retour[$i]['action'] = $row['action'];}  
                    if (!empty($row['groupe'])) 
                        {$retour[$i]['groupe'] = $row['groupe'];}
                    $i++;
                    }
                }
            }
        return $retour;
        }    
    }
?>
