<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: fleuryt (Fleury Tristan) - ©2014   ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//

/**
 * Description de plugin.form
 * 
 * 
 */

// récupération des chemins absolus
define('GLPI_ROOT', getAbsolutePath("reforme"));
include (GLPI_ROOT."inc/includes.php");
$cheminSmarty = getAbsolutePath("reforme")."plugins/reforme/Smarty";

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

$profile = new PluginReformeProfile();
if ($profile->estAutorise())
    {
    $trie = "date";
    $ascdesc = "ASC";

    //Vérification de l'appel (via le bouton glpi plugin ou via le trie et autre requête du form
    if(isset($_GET["trie"])){$trie = $_GET["trie"];$ascdesc = $_GET["ASCDESC"];}
    if($ascdesc == "ASC"){$ascdesc = "DESC";}
    else{$ascdesc = "ASC";}

    // Récupération de la liste des machines réformées
    $reforme = new PluginReformePlugin();
    $smarty->assign('listeReforme',  $reforme->getListeReforme("Computer",$trie,$ascdesc));

    $smarty->assign('httpPath',  getHttpPath());
    $smarty->assign('trie',  $trie);
    $smarty->assign('ASCDESC',  $ascdesc);
    $smarty->assign('auth',  "true");
    }
else{$smarty->assign('auth',  "false");}
//Affichage de l'entête GLPI
HTML::header('Configuration Plugin Reforme');
//Affichage du plugin
$smarty->display('plugin.tpl');
//Affichage du pied de page GLPI
HTML::footer();

//============================================================================//
//=============================== FONCTION COMMUNE ===========================//
//============================================================================//
/**
 * Récupère le chemin absolue de l'instance glpi
 * @return String : le chemin absolue (racine principale)
 */
function getAbsolutePath($plugin)
    {return str_replace("plugins/$plugin/front/plugin.form.php", "", $_SERVER['SCRIPT_FILENAME']);} 

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
