<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//

// récupération des chemins absolus

$cheminSmarty = getAbsolutePath()."Smarty";

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

define('GLPI_ROOT', str_replace("/plugins/twins/","",getAbsolutePath()));
include (GLPI_ROOT."/inc/includes.php");

$id = $_GET['id'];
$twins = new PluginTwinsTwinsold();

$smarty->assign('historique',$twins->getHistorique($id));
$smarty->assign('httpPath',getHttpPath());
$smarty->display('historique.tpl');

    /**
     * Récupère le chemin absolue de l'instance glpi
     * @return String : le chemin absolue (racine principale)
     */
    function getAbsolutePath()
        {return str_replace("popup/twinsold.popup.php", "", $_SERVER['SCRIPT_FILENAME']);}
        
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
