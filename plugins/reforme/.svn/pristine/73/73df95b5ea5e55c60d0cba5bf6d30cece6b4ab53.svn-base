<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//

/**
 * Traite toute les demandes ajax du plugin
 */
define('GLPI_ROOT', getAbsolutePath());
include (GLPI_ROOT."inc/includes.php");

//Instanciation de la class config
$config = new PluginReformeConfig(); 


if(isset($_POST['action']))
    {   
    if($_POST['action'] == "modifierADM")
        {
        if($_POST['identification'] == "valider")
            {
            $valeur['structure'] = $_POST['structure'];
            $valeur['service'] = $_POST['service'];
            $valeur['mail'] = $_POST['mail'];
            $valeur['statut'] = $_POST['statut'];
            $valeur['supp'] = $_POST['supp'];
            $config->setValeurInfoAdministrative($_POST['identification'],$valeur);
            echo("Les informations ont été mises à jour");
            }
        }
    if($_POST['action'] == "modifierAD")
        {
        if( strstr($_POST['identification'], "valider")) 
            {
            $valeur['serveur'] = $_POST['serveur'];
            $valeur['dc'] = $_POST['dc'];
            $valeur['suffix'] = $_POST['suffix'];
            $valeur['login'] = $_POST['login'];
            $valeur['passwd'] = $_POST['passwd'];
            $config->setValeurInfoAD($_POST['identification'],$valeur);
            }
        else
            {$config->setValeurInfoAD($_POST['identification'],$_POST['valeur']);}
        }
    if($_POST['action'] == "ajoutAD")
        {$config->setAD();}
        
    if($_POST['action'] == "testerAD")
        {
        $valeur['serveur'] = $_POST['serveur'];
        $valeur['dc'] = $_POST['dc'];
        $valeur['suffix'] = $_POST['suffix'];
        $valeur['login'] = $_POST['login'];
        $valeur['passwd'] = $_POST['passwd'];
        echo $config->testerAD($valeur);
        }
        
    if($_POST['action'] == "testerMail"){
        $to      = $_POST['mail'];
        $subject = 'Test GLPI';
        $message = 'Bonjour, ceci est un test d\'envoie de mail ';
        $headers = 'From: glpi@u-grenoble3.fr' . "\r\n" .
        'Reply-To: glpi@u-grenoble3.fr' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
        mail($to, $subject, $message, $headers);
        echo "Un email vient d'être envoyé à l'adresse suivante: ". $to;
    }    
    
    if($_POST['action'] == "ajouterInfo"){
        echo $config->saveInfo($_POST['info']);
    }
    
    
}

/**
 * Récupère le chemin absolue de l'instance glpi
 * @return String : le chemin absolue (racine principale)
 */
function getAbsolutePath()
    {return str_replace("plugins/reforme/ajax/config.ajax.php", "", $_SERVER['SCRIPT_FILENAME']);}

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
        if($value != "plugins"){$Ref.= $value."/";}
        else{break;}
        }
    return $Ref;
    }

?>
