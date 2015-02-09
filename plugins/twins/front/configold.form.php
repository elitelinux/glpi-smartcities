<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//

/**
 * Gestion du formulaire de configuration plugin Twins
 * Reçoit les informations depuis un formulaire de configuration
 * Renvoi sur la page de l'item traité
 */

// récupération des chemins absolus
$cheminSmarty = getAbsolutePath()."plugins/twins/Smarty";
define('GLPI_ROOT', getAbsolutePath());
include (GLPI_ROOT."inc/includes.php"); 

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

//Instanciation de la class config
$config = new PluginTwinsConfigold();


//Envoie des variables à Smarty
$smarty->assign('infoAD', $config->getAD());
$smarty->assign('httpPath', getHttpPath());
$smarty->assign('targetCSS', getHttpPath()."plugins/twins/css/twins.css");

//Affichage de l'entête GLPI
HTML::header('Configuration Plugin Twins');
//Affichage du plugin
$smarty->display('configold.tpl');
//Affichage du pied de page GLPI
HTML::footer();  

//========================================================================//
/**
 * Récupère le chemin absolue de l'instance glpi
 * @return String : le chemin absolue (racine principale)
 */
function getAbsolutePath()
    {return str_replace("plugins/twins/front/configold.form.php", "", $_SERVER['SCRIPT_FILENAME']);}

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
?>
