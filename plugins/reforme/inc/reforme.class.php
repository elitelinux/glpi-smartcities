<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2014     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//

/**
 * Class principale du projet
 * Gère toute la partie réforme d'une machine
 */
class PluginReformeReforme extends CommonDBTM
    {
    /**
     * Fonction native GLPI
     * @param int $with_comment
     * @return string Nom du plugin
     */
    function getName($with_comment = 0)
        {return "reforme";} 

    /**
     * Fonction native GLPI
     * @param CommonGLPI $item
     * @param int $withtemplate
     * @return string Nom du Tab
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
       if(Session::haveRight('plugin_reforme_reforme', CREATE)){
            return "Reforme";
       }
    }

    /**
     * Fonction native GLPI
     * @param CommonGLPI $item
     * @param int $tabnum
     * @param int $withtemplate
     * @return boolean
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
        if ($item->getType() == 'Computer' || $item->getType() == 'Monitor') {
            if(Session::haveRightsOr('plugin_reforme_reforme', array(CREATE))){
                $reforme = new self();
                $ID = $item->getField('id');
                $Name = $item->getField('name');
                // j'affiche le formulaire
                $reforme->showForm($ID, $item->getType());
            }
        }
    }  
    
    /**
     * Fonction qui affiche le formulaire du plugin
     * @param int $id
     * @param String $itemType
     * @return boolean
     */
    function showForm($id, $itemType)     
        {
        global $DB;
        $cheminSmarty = $this->getAbsolutePath()."plugins/reforme/Smarty";

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
 
        // vérification si réforme déjà effectuée
        $query = "SELECT * FROM glpi_plugin_reforme_log WHERE id_item = '$id' "
            . "AND type_item = '$itemType' AND vie='1'";

        if ($result = $DB->query($query))
            {
            $technicien = "";
            $bon_reforme = "";
            $date = "";

            if ($DB->numrows($result) > 0) // Si la réforme est déjà effectuée
                {
                $row = $DB->fetch_assoc($result);
                if (!empty($row['technicien'])) 
                    {$technicien = $row['technicien'];}
                if (!empty($row['bon_reforme'])) 
                    {$bon_reforme = $row['bon_reforme'];}
                if (!empty($row['date'])) 
                    {$date = $row['date'];}
                if (!empty($row['domaine'])) 
                    {$domaine = $row['domaine'];}

                // Vérification si fiche glpi restauré (callback)
                switch ($itemType) {
                    case 'Computer':
                        $query = "SELECT is_deleted FROM glpi_computers WHERE id = '$id'"; 
                        break;
                    case 'Monitor':
                        $query = "SELECT is_deleted FROM glpi_monitors WHERE id = '$id'"; 
                        break;
                    default:
                        break;
                }
                
                if ($result = $DB->query($query))
                    {
                    if ($DB->numrows($result) > 0) 
                        {
                        $row = $DB->fetch_assoc($result);
                        if ($row["is_deleted"] == "0")
                            {
                            $target = $this->getFormURL();
                            $smarty->assign('target',$target);
                            $smarty->assign('typeItem',$itemType);
                            $smarty->assign('id',$id);
                            $smarty->assign('callback',"on");
                            }
                        }
                    }
                $smarty->assign('technicien',$technicien);
                $smarty->assign('bon_reforme',$this->getHttpPath()
                    ."plugins/reforme/bon_reforme/".$bon_reforme.'.pdf');
                $smarty->assign('date',$date);
                $smarty->assign('domaine',$domaine);
                $reforme = "false";   
                }
            else // Si la réforme n'est pas effectuée
                {
                // envoie du formulaire de réforme
                $smarty->assign('info',$this->getInfoMachine($id, $itemType));
                $smarty->assign('post_Form_Reforme',$this->getHttpPath()
                    ."plugins/reforme/front/reforme.form.php");
                $smarty->assign('typeItem',$itemType);
                $reforme = "true";
                }
            }
        $smarty->assign('endform', HTML::closeForm(false));
        $smarty->assign('reforme',$reforme);
        $smarty->assign('targetCSS', $this->getHttpPath()."plugins/reforme/css/reforme.css");
        $smarty->display('reforme.tpl');
        }

    /**
     * Fait la réforme de l'item demandé
     * @param type $arrayItem (tableau contenant toutes les informations nécessaires)
     */
    function reformeItem($arrayItem)     
        {
        global $DB;
       
        //Récupération des infos de la machine
        $info = $this->getInfoMachine($arrayItem[0], $arrayItem[1]);
        
        // instanciation de adldap
        $cheminAdldap = $this->getAbsolutePathForm()."/adldap";
        require_once($cheminAdldap . '/adLDAP.php');

        //Récupération des AD et désactivation de la machine si présente
        $LDAPConfig = $this->getLDAPConfig($info["id"]);
        $domaine = "";

        //Si la machine appartient à un domaine on la supprime de ce domaine
        if($LDAPConfig != NULL && $arrayItem[1] == 'Computers' && $this->testerAD($LDAPConfig))
            {
            $serveur[0]=$LDAPConfig['serveur'];
            $adldap = new adLDAP(array('base_dn'=>$LDAPConfig['dc'], 
                'account_suffix'=>$LDAPConfig['suffix'], 'domain_controllers'=>$serveur));

            $adldap->close();
            $adldap->setAdminUsername($LDAPConfig['login']);
            $adldap->setAdminPassword($LDAPConfig['passwd']);
            $adldap->connect();
  
            $result=$adldap->computer()->info($info["name"],array("distinguishedname"));
            if($result[0]["distinguishedname"][0] != "")
                {
                if($adldap->user()->desactiverComputer($result[0]["distinguishedname"][0]))
                    {$domaine = $LDAPConfig['suffix'];}
                else {$domaine = $LDAPConfig['suffix']."-->erreur";}
                }
            $adldap->close();
            }            
        
        //Récupération des informations de configuration
        $configClass = new PluginReformeConfig(); 
        $config = $configClass->getInfoAdministrative();
               
        $date = date('j-m-Y');
        $heure = date('H:m:s');
        $stockage = $this->getAbsolutePathForm()."bon_reforme";
        
        // Création du bon de réforme
        //Chargement de la Class fpdf
        $cheminFpdf = $this->getAbsolutePathForm()."/fpdf";
        require($cheminFpdf .'/fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->Image('../images/logo.png',5,10,'PNG');
        
        $pdf->SetXY(50,20);
        $pdf->SetFont('Times','I',16);
        $pdf->Cell(150,8,utf8_decode($config['structure']),0,2,'C');
  
        $pdf->SetXY(50,40);
        $pdf->SetFont('Times','',13);
        $pdf->Cell(150,8,utf8_decode($config['service']),0,2,'C');
        
        $pdf->SetXY(0,80);
        $pdf->SetFillColor(200);
        $pdf->Cell(220,30,'',0,2,'L',true);
        $pdf->SetFillColor(175);
        $pdf->SetXY(0,80);
        $pdf->SetFont('Times','I',14);
        $pdf->Cell(220,8,utf8_decode('CERTIFICAT DE REFORME DE MATERIEL'),0,2,'C',true);
        $pdf->SetFont('Times','B',11);
        $pdf->SetXY(15,90);
        $pdf->Cell(50,8,utf8_decode('Technicien en charge de la réforme: '),0,2,'L');
        $pdf->SetFont('Times','I',11);
        $pdf->SetXY(100,90);
        $pdf->Cell(75,8,$arrayItem[2],0,2,'L');
        $pdf->SetFont('Times','B',11);
        $pdf->SetXY(15,100);
        $pdf->Cell(50,8,'Date: ',0,2,'L');
        $pdf->SetFont('Times','B',11);
        $pdf->SetXY(100,100);
        $pdf->SetFont('Times','I',11);
        $pdf->Cell(75,8,utf8_decode(date('j/m/Y')." à ".$heure),0,2,'L');
        
        $pdf->SetXY(45,140);
        $pdf->SetFont('Times','B',11);
        $pdf->Cell(60,10,utf8_decode('Type de l\'objet: '),1,2,'L');
        $pdf->SetXY(105,140);
        $pdf->SetFont('Times','I',11);
        $pdf->Cell(60,10,utf8_decode($arrayItem[1]),1,2,'C');
        
        $pdf->SetXY(45,150);
        $pdf->SetFont('Times','B',11);
        $pdf->Cell(60,10,utf8_decode('Nom de l\'objet: '),1,2,'L');
        $pdf->SetXY(105,150);
        $pdf->SetFont('Times','I',11);
        $pdf->Cell(60,10,utf8_decode($info["name"]),1,2,'C');
        
        $pdf->SetXY(45,160);
        $pdf->SetFont('Times','B',11);
        $pdf->Cell(60,10,utf8_decode('Numéro de série: '),1,2,'L');
        $pdf->SetXY(105,160);
        $pdf->SetFont('Times','I',11);
        $pdf->Cell(60,10,utf8_decode($info["numserie"]),1,2,'C');

        $pdf->SetXY(45,170);
        $pdf->SetFont('Times','B',11);
        $pdf->Cell(60,10,utf8_decode('Lieu: '),1,2,'L');
        $pdf->SetXY(105,170);
        $pdf->SetFont('Times','I',11);
        $pdf->Cell(60,10,utf8_decode($info["lieu"]),1,2,'C');
        
        $pdf->SetXY(45,180);
        $pdf->SetFont('Times','B',11);
        $pdf->Cell(60,10,utf8_decode('Date d\'achat: '),1,2,'L');
        $pdf->SetXY(105,180);
        $pdf->SetFont('Times','I',11);
        $pdf->Cell(60,10,utf8_decode($info["dateachat"]),1,2,'C');

        $pdf->SetXY(45,190);
        $pdf->SetFont('Times','B',11);
        $pdf->Cell(60,10,utf8_decode('Numéro d\'immobilisation: '),1,2,'L');
        $pdf->SetXY(105,190);
        $pdf->SetFont('Times','I',11);
        $pdf->Cell(60,10,utf8_decode($info["immo"]),1,2,'C');        

        // Gestion des informations supplémentaires:
        $y = 200;
        if($config['des_bien']){
            $pdf->SetXY(45,$y);
            $pdf->SetFont('Times','B',11);
            $pdf->Cell(60,10,utf8_decode('Modèle du bien: '),1,2,'L');
            $pdf->SetXY(105,$y);
            $pdf->SetFont('Times','I',11);
            $pdf->Cell(60,10,utf8_decode($info["des_bien"]),1,2,'C');
            $y = $y+10;
        }
        if($config['ref_type']){
            $pdf->SetXY(45,$y);
            $pdf->SetFont('Times','B',11);
            $pdf->Cell(60,10,utf8_decode('Type du bien: '),1,2,'L');
            $pdf->SetXY(105,$y);
            $pdf->SetFont('Times','I',11);
            $pdf->Cell(60,10,utf8_decode($info["ref_type"]),1,2,'C');
            $y = $y+10;
        }        
        if($config['num_commande']){
            $pdf->SetXY(45,$y);
            $pdf->SetFont('Times','B',11);
            $pdf->Cell(60,10,utf8_decode('Numéro de commande: '),1,2,'L');
            $pdf->SetXY(105,$y);
            $pdf->SetFont('Times','I',11);
            $pdf->Cell(60,10,utf8_decode($info["num_commande"]),1,2,'C');
            $y = $y+10;
        }          
        if($config['num_facture']){
            $pdf->SetXY(45,$y);
            $pdf->SetFont('Times','B',11);
            $pdf->Cell(60,10,utf8_decode('Numéro de facture: '),1,2,'L');
            $pdf->SetXY(105,$y);
            $pdf->SetFont('Times','I',11);
            $pdf->Cell(60,10,utf8_decode($info["num_facture"]),1,2,'C');
            $y = $y+10;
        }                  

        $pdf->SetFont('Times','I',9);
        $pdf->SetXY(0,266);
        $pdf->SetFillColor(150);
        $pdf->Cell(220,10,utf8_decode('Bon de réforme généré depuis l\'interface '
            . 'GLPI - Plugin Reforme - © Viduc 2013'),0,2,'C',true);
        $pdf->Output($stockage.'/'.str_replace(' ','_',$info["name"]).'_'.$date.'.pdf', 'F');
        
        // Envoie du mail de réforme
        $sujet = "Reforme de l'objet: ".$info["name"] . " type: ". $arrayItem[1];
        $Message_Send= utf8_decode("L'objet : <span class=\"Titre\">".$info["name"]."</span> "
            . "id GLPI: ".$info["id"]." a été réformée par: <span class=\"Tech\">"
            .$arrayItem[2] . "</span><br><br>");
        if($arrayItem[1] == 'Computers'){
            if($domaine != "")
                {$Message_Send.= utf8_decode("L'ordinateur a été désactivé du domaine: "
                    . "<span class=\Titre\">".$domaine. "</span><br>". "<br>". "<br>". "<br>");}
            else{$Message_Send.= utf8_decode("L'ordinateur n'appartenait à aucun domaine "
                . "<br>". "<br>". "<br>". "<br>");}
        }
        $Message_Send.= utf8_decode("<span class=\"glpi\">Ce message a été envoyé "
            . "de façon automatique par GLPI (Plugin Reforme)<br></span>");
        $Message_Send.= utf8_decode("<span class=\"glpi\">© Viduc 2013 <A "
            . "HREF=\"http://viduc.sugarbox.fr/\">http://viduc.sugarbox.fr/</A> </span>");
        
        $this->Send_Mail($config['mail'],$sujet, $Message_Send, $stockage.'/'
            .str_replace(' ','_',$info["name"]).'_'.$date.'.pdf', $info["name"].'_'.$date.'.pdf');

        //Modification de la fiche GLPI
        $comment = addslashes($info['commentaire'])."\nNom de l\'objet réformée: ".$info['name'];
        $comment = $comment."\nRéforme effectuée par: ".$arrayItem[2]." le: ".$date." à: ".$heure;
        $statut = $config['statut'];
        $supp = $config['supp'];
        switch($arrayItem[1]){
            case 'Computer':
                $query = "UPDATE glpi_computers SET name='',comment='$comment'"
                    . ",is_deleted='$supp',states_id='$statut' WHERE id='".$info["id"]."'";
                break;
            case 'Monitor':
                $query = "UPDATE glpi_monitors SET name='',comment='$comment',"
                    . "is_deleted='$supp',states_id='$statut' WHERE id='".$info["id"]."'";                
                break;
        }

        $DB->query($query);
        // Enregistrement dans la base de données de la réforme
        $stockage = $stockage.'/'.$info["name"].'_'.$date.'.pdf';
        $DB->query("INSERT INTO glpi_plugin_reforme_log (id_item,type_item,"
            . "technicien,bon_reforme,date,domaine,name,statut) VALUES ('".$info['id']."',"
            . "'$arrayItem[1]','$arrayItem[2]','".str_replace(' ','_',$info["name"])."_$date',"
            . "'$date','$domaine','".$info['name']."','".$info['statut']."')") or die($DB->error());
        }

    /**
     * Restaure un item
     * @global type $DB
     * @param type $arrayItem
     */
    function restaurerItem($arrayItem)     
    {
        global $DB;
        $date = date('j-m-Y');
        $heure = date('H:m:s');
        
        //Récupération des informations de configuration
        $configClass = new PluginReformeConfig(); 
        $config = $configClass->getInfoAdministrative();
        
        //Récupération des infos de la machine
        $info = $this->getInfoMachine($arrayItem[0],$arrayItem[1]);
        $bon_reforme = null;
        
        //Modification de la fiche GLPI
        $comment = addslashes($info['commentaire'])."\nRestauration effectuée par: "
            . "".$arrayItem[2]." le: ".$date." à: ".$heure;
        
        //Si nom de la machine non remis
        if($info["name"] == ""){
            $query = "SELECT name,statut,bon_reforme FROM glpi_plugin_reforme_log "
                . "WHERE id_item = '$arrayItem[0]' AND type_item = '$arrayItem[1]' "
                . "AND vie='1'";   
            if ($result = $DB->query($query)){
                if ($DB->numrows($result) > 0) {
                    $row = $DB->fetch_assoc($result);
                    $info["name"] = $row["name"];
                    $bon_reforme = $row["bon_reforme"];
                    switch ($arrayItem[1]) {
                        case 'Computer':
                            $query1 = "UPDATE glpi_computers SET name='".$row["name"]."',"
                            . "states_id='".$row["statut"]."' WHERE id='".$arrayItem[0]."'";
                            
                            $query2 = "UPDATE glpi_computers SET comment='$comment' "
                                 . "WHERE id='".$arrayItem[0]."'";
                            
                             // instanciation de adldap
                            $cheminAdldap = $this->getAbsolutePathForm()."/adldap";
                            require_once($cheminAdldap . '/adLDAP.php');

                            //Récupération des AD et réactivation de la machine si présente
                            $LDAPConfig = $this->getLDAPConfig($info["id"]);
                            $domaine = "";

                            //Si la machine appartenait à un domaine on la restaure
                            if($LDAPConfig != NULL && $this->testerAD($LDAPConfig)){
                                $serveur[0]=$LDAPConfig['serveur'];
                                $adldap = new adLDAP(array('base_dn'=>$LDAPConfig['dc'], 
                                    'account_suffix'=>$LDAPConfig['suffix'], 
                                    'domain_controllers'=>$serveur));

                                $adldap->close();
                                $adldap->setAdminUsername($LDAPConfig['login']);
                                $adldap->setAdminPassword($LDAPConfig['passwd']);
                                $adldap->connect();

                                $result=$adldap->computer()->info($info["name"],
                                    array("distinguishedname"));
                                if($result[0]["distinguishedname"][0] != ""){
                                    if($adldap->user()->activerComputer($result[0]["distinguishedname"][0]))
                                        {$domaine = $LDAPConfig['suffix'];}
                                    else {$domaine = $LDAPConfig['suffix']."-->erreur";}
                                }
                                $adldap->close();
                            }
                            break;
                        case 'Monitor':
                            $query1 = "UPDATE glpi_monitors SET name='".$row["name"]."',"
                            . "states_id='".$row["statut"]."' WHERE id='".$arrayItem[0]."'";  
                            $query2 = "UPDATE glpi_monitors SET comment='$comment' "
                                 . "WHERE id='".$arrayItem[0]."'";
                            break;
                        default:
                            break;
                    }
                    $DB->query($query1);
                    $DB->query($query2);
                }
            }
        }

        // Désactivation du log de réforme
        $query = "UPDATE glpi_plugin_reforme_log SET vie='0' WHERE id_item='".$arrayItem[0]."'"
                . " AND type_item='".$arrayItem[1]."'";
        $DB->query($query);

        $stockage = $this->getAbsolutePathForm()."bon_reforme";

        // Envoie du mail d'annulation de réforme
        $sujet = "Anulation de la reforme de la machine: ".$info["name"];
        $Message_Send= utf8_decode("L\'objet : <span class=\"Titre\">".$info["name"]
            ."</span> a été réformée par erreur<br><br>");
        $Message_Send.= utf8_decode("La machine a été restauré dans la base GLPI par: "
            . "<span class=\"Tech\">".$arrayItem[2] . "</span><br><br>");
        $Message_Send.= utf8_decode("<span class=\"glpi\">Ce message a été envoyé "
            . "de façon automatique par GLPI (Plugin Reforme)<br></span>");
        $Message_Send.= utf8_decode("<span class=\"glpi\">© Viduc 2013 <A "
            . "HREF=\"http://viduc.sugarbox.fr/\">http://viduc.sugarbox.fr/</A> </span>");
        $this->Send_Mail($config['mail'],$sujet, $Message_Send, $this->getAbsolutePathForm()
            .'bon_reforme/'.$bon_reforme.'.pdf', $bon_reforme.'.pdf');
        
        //Suppression du bon de réforme
        unlink($this->getAbsolutePathForm().'bon_reforme/'.$bon_reforme.'.pdf');
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
        // instanciation de adldap
        $cheminAdldap = $this->getAbsolutePathForm()."/adldap";
        require_once($cheminAdldap . '/adLDAP.php');

        $serveur[0]=$valeur['serveur'];
        $adldap = new adLDAP(array('base_dn'=>$valeur['dc'], 'account_suffix'=>$valeur['suffix'],
            'domain_controllers'=>$serveur));
        //$adldap->close();
        return $adldap->authenticate($valeur['login'], $valeur['passwd']);
    }        
        
    /**
     * Récupère le chemin absolue de l'instance glpi
     * @return String : le chemin absolue (racine principale)
     */
    function getAbsolutePath()
        {return str_replace("ajax/common.tabs.php", "", $_SERVER['SCRIPT_FILENAME']);}

    /**
     * Récupère le chemin absolue de l'instance glpi
     * @return String : le chemin absolue (racine principale)
     */
    function getAbsolutePathForm()
        {return str_replace("front/reforme.form.php", "", $_SERVER['SCRIPT_FILENAME']);}
        
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
     * @param int $id
     * @param String itemType
     * @return type null ou le nom de la machine
     */
    function getItemName($id, $itemType)
        {
        global $DB;
        // Récupération du nom de la machine
        $name = null;
        switch ($itemType) {
            case 'Computer':
                $query = "SELECT name FROM glpi_computers WHERE id = '$id'";
                break;
            case 'Monitor':
                $query = "SELECT name FROM glpi_monitors WHERE id = '$id'";
                break;
            default:
                break;
        }
        
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
            $query = "SELECT * FROM glpi_plugin_reforme_ad WHERE suffix='@".$this->getDomain($id)."'";
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
                        }
                    }
                }
            return $tableServeur;
            }
        else{return NULL;}
        } 
    
    /**
     * Récupère l'historique d'une machine
     * @global type $DB
     * @param type $id
     * @return type
     */
    function getHistorique($id)
        {
        global $DB; 
        
        $query = "SELECT * FROM glpi_plugin_reforme_log WHERE id_item = '$id' AND type_item = 'Computer'";
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
                    $i++;
                    }
                }
            }
        return $retour;
        }
        
    /**
     * Récupère les infos d'une machine pour la réforme
     * @global type $DB
     * @param type $id
     * @return un tableau de valeur avec clé
     */
    function getInfoMachine($id, $itemType)
        {
        global $DB;
        
        //Récupération des informations de configuration
        $configClass = new PluginReformeConfig(); 
        $config = $configClass->getInfoAdministrative();
        
        $retour['id'] = $id;
        $retour['name'] = "";
        $retour['lieu'] = "";
        $retour['numserie'] = "";
        $retour['commentaire'] = "";
        $retour['fournisseur'] = "";
        $retour['dateachat'] = "";
        $retour['immo'] = "";
        $retour['statut'] = "";
        if($itemType == "Computer"){
            $retour['domain'] = $this->getDomain($id);
        }
        else {$retour['domain'] = "";}
        $retour['des_bien'] = "";
        $retour['ref_type'] = "";
        $retour['num_commande'] = "";
        $retour['num_facture'] = "";
        // Récupération du nom de la machine
        $retour['name'] = $this->getItemName($id, $itemType);

        //définition des requêtes en fonction du type de l'item
        switch ($itemType) {
            case 'Computer':
                $query1 = "SELECT T2.name FROM glpi_computers as T1,glpi_locations as T2 "
                    . "WHERE T1.id = '$id' AND T1.locations_id = T2.id";
                $query2 = "SELECT serial,comment FROM glpi_computers WHERE id = '$id'";
                $query3 = "SELECT name FROM glpi_infocoms as T1, glpi_suppliers "
                    . "as T2  WHERE items_id = '$id' AND itemtype = 'Computer' "
                    . "AND T1.suppliers_id = T2.id";
                $query4 = "SELECT buy_date FROM glpi_infocoms WHERE items_id = '$id' "
                    . "AND itemtype = 'Computer'";
                $query5 = "SELECT immo_number FROM glpi_infocoms WHERE items_id = '$id' "
                    . "AND itemtype = 'Computer'";
                $query6 = "SELECT t2.id FROM glpi_computers as t1, glpi_states as t2 "
                    . "WHERE t1.id=$id AND t1.states_id=t2.id";
                $query7 = "SELECT t2.name FROM glpi_computers as t1, glpi_computertypes as t2 "
                    . "WHERE t1.id=$id AND t1.computertypes_id=t2.id";
                $query8 = "SELECT order_number FROM glpi_infocoms WHERE items_id = '$id' "
                    . "AND itemtype = 'Computer'";
                $query9 = "SELECT bill FROM glpi_infocoms WHERE items_id = '$id' "
                    . "AND itemtype = 'Computer'";
                $query10 = "SELECT t2.name FROM glpi_computers as t1, glpi_computermodels as t2 "
                    . "WHERE t1.id=$id AND t1.computermodels_id=t2.id";
                break;
            case 'Monitor':
                $query1 = "SELECT T2.name FROM glpi_monitors as T1,glpi_locations as T2 "
                    . "WHERE T1.id = '$id' AND T1.locations_id = T2.id";
                $query2 = "SELECT serial,comment FROM glpi_monitors WHERE id = '$id'";
                $query3 = "SELECT name FROM glpi_infocoms as T1, glpi_suppliers "
                    . "as T2  WHERE items_id = '$id' AND itemtype = 'Monitor' "
                    . "AND T1.suppliers_id = T2.id";
                $query4 = "SELECT buy_date FROM glpi_infocoms WHERE items_id = '$id' "
                    . "AND itemtype = 'Monitor'";
                $query5 = "SELECT immo_number FROM glpi_infocoms WHERE items_id = '$id' "
                    . "AND itemtype = 'Monitor'";
                $query6 = "SELECT t2.id FROM glpi_monitors as t1, glpi_states as t2 "
                . "WHERE t1.id=$id AND t1.states_id=t2.id";
                $query7 = "SELECT t2.name FROM glpi_monitors as t1, glpi_monitortypes as t2 "
                    . "WHERE t1.id=$id AND t1.monitortypes_id=t2.id";
                $query8 = "SELECT order_number FROM glpi_infocoms WHERE items_id = '$id' "
                    . "AND itemtype = 'Monitor'";             
                $query9 = "SELECT bill FROM glpi_infocoms WHERE items_id = '$id' "
                    . "AND itemtype = 'Monitor'";
                $query10 = "SELECT t2.name FROM glpi_monitors as t1, glpi_monitormodels as t2 "
                    . "WHERE t1.id=$id AND t1.monitormodels_id=t2.id";
                break;
            default:
                break;
        }
        
        // Récupération du lieu de la machine
        if ($result = $DB->query($query1))
            {
            if ($DB->numrows($result) > 0) 
                {
                $row = $DB->fetch_assoc($result);
                if (!empty($row['name'])) {$retour['lieu'] = $row['name'];}
                }
            }

        // Récupération du numéro de série et commentaire de la machine
        if ($result = $DB->query($query2))
            {
            if ($DB->numrows($result) > 0) 
                {
                $row = $DB->fetch_assoc($result);
                if (!empty($row['serial'])){$retour['numserie'] = $row['serial'];}
                if (!empty($row['comment'])){$retour['commentaire'] = $row['comment'];}
                }
            }

        // Récupération du fournisseur de la machine
        if ($result = $DB->query($query3))
            {
            if ($DB->numrows($result) > 0) 
                {
                $row = $DB->fetch_assoc($result);
                if (!empty($row['name'])){$retour['fournisseur'] = $row['name'];}
                }
            }

        // Récupération de la date d'achat de la machine
        if ($result = $DB->query($query4))
            {
            if ($DB->numrows($result) > 0) 
                {
                $row = $DB->fetch_assoc($result);
                if (!empty($row['buy_date'])) {$retour['dateachat'] = $row['buy_date'];}
                }
            }

        // Récupération du numéro d'immobilisation de la machine
        if ($result = $DB->query($query5))
            {
            if ($DB->numrows($result) > 0) 
                {
                $row = $DB->fetch_assoc($result);
                if (!empty($row['immo_number'])) {$retour['immo'] = $row['immo_number'];}
                }
            }            
            
        // Récupération du statut de la machine
        if ($result = $DB->query($query6))
            {
            if ($DB->numrows($result) > 0) 
                {
                $row = $DB->fetch_assoc($result);
                if (!empty($row['id'])){$retour['statut'] = $row['id'];}
                }
            }
 
        // Récupération du type de la machine
        if($config["ref_type"]){
           if ($result = $DB->query($query7)){
                if ($DB->numrows($result) > 0) {
                    $row = $DB->fetch_assoc($result);
                    if (!empty($row['name'])){$retour['ref_type'] = $row['name'];}
                }
            }
        }    
        // Récupération du numéro de commande
        if($config["num_commande"]){
            if ($result = $DB->query($query8)){
                if ($DB->numrows($result) > 0){
                    $row = $DB->fetch_assoc($result);
                    if (!empty($row['order_number'])) {$retour['num_commande'] = $row['order_number'];}
                }
            } 
        }    
        if($config["num_facture"]){
            // Récupération du numéro de facture
            if ($result = $DB->query($query9)){
                if ($DB->numrows($result) > 0) {
                    $row = $DB->fetch_assoc($result);
                    if (!empty($row['bill'])) {$retour['num_facture'] = $row['bill'];}
                }
            } 
        }
        if($config["des_bien"]){
            // Récupération du modèle
            if ($result = $DB->query($query10)){
                if ($DB->numrows($result) > 0) {
                    $row = $DB->fetch_assoc($result);
                    if (!empty($row['name'])){$retour['des_bien'] = $row['name'];}
                }
            }
        }
        return $retour;
        }
    
    /**
     * Fonction d'envoie de mail
     * @param $To String, destinataire
     * @param $sujet String, le sujet du mail
     * @param $Message_Send String, Le corps du text
     * @param $PJ String, le chemin d'accès à la pièce jointe
     * @param $PJName String, le nom de la pièce jointe
     * @return boolean
     */
    function Send_Mail($To, $sujet, $Message_Send, $PJ, $PJName)
        {
	#=====================================================================#
	#== BUT: Envoyer un mail au format HTML                             ==#
	#== Argument: Le sujet, le message l'adresse de retour et la PJ     ==#
	#== Retour: True ou False                              		    ==#
	#== Suite: Aucune               			    	    ==#
	#=====================================================================#
	$adresse= $To;

        //-----------------------------------------------
        //GENERE LA FRONTIERE DU MAIL ENTRE TEXTE ET HTML
        //-----------------------------------------------

        $frontiere = '-----=' . md5(uniqid(mt_rand()));

        //-----------------------------------------------
        //HEADERS DU MAIL
        //-----------------------------------------------

        $headers = "From: GLPI Plugin Reforme <$To> " . "\n";
        $headers .= "Reply-To: $To ". "\n";
        $headers .= 'MIME-Version: 1.0'."\n";
        $headers .= 'Content-Type: multipart/mixed; boundary="'.$frontiere.'"';

        //-----------------------------------------------
        //MESSAGE TEXTE
        //-----------------------------------------------
        $message = 'This is a multi-part message in MIME format.'."\n\n";

        //-----------------------------------------------
        //MESSAGE HTML
        //-----------------------------------------------
        $message .= '--'.$frontiere."\n";

        $message .= 'Content-Type: text/html; charset="iso-8859-1"'."\n";
        $message .= 'Content-Transfer-Encoding: 8bit'."\n\n";
     
        $message.= "<html> <head> <style type=\"text/css\"> .Titre { color: green; } "
            . ".Tech { color: blue; } .glpi { color: #FF8000; }</style> </head> <body>";
	$message.= $Message_Send."\n\n";
        $message.= "</body> </html> "."\n\n";

        $message .= '--'.$frontiere."\n";

        //-----------------------------------------------
        //PIECE JOINTE
        //-----------------------------------------------
        $message .= 'Content-Type: application/pdf; name="'.$PJName.'"'."\n";
        $message .= 'Content-Transfer-Encoding: base64'."\n";
        $message .= 'Content-Disposition:attachement; filename="'.$PJName.'"'."\n\n";
        $message .= chunk_split(base64_encode(file_get_contents($PJ)))."\n"; 

        if(mail($adresse,$sujet,$message,$headers)) {return true;}             
        else {return false;}
        }  

    }
?>
