<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//

/**
 * Gestion des droits du plugin reforme
 * Reçoit les informations depuis un formulaire de configuration des droits (profile)
 * Renvoi sur la page de l'item traité
 */

// récupération des chemins absolus
define('GLPI_ROOT', getAbsolutePath());
include (GLPI_ROOT."inc/includes.php"); 
include "../inc/profile.class.php";

if (isset($_POST["Modifier"]))
    {
    $arrayItem[0] = $_POST["id"];
    if($_POST["droit"] == "Lecture")
        {$arrayItem[1] = "r";}
    elseif ($_POST["droit"] == "Modification") 
        {$arrayItem[1] = "w";}
    else {$arrayItem[1] = "0";}
    // Modification des droits dans la base
    $profile = new PluginReformeProfile();
    
    $profile->majDroit($arrayItem);
    // Retour à la page d'appel
    Html::back();
    
    }
    
//========================================================================//
/**
 * Récupère le chemin absolue de l'instance glpi
 * @return String : le chemin absolue (racine principale)
 */
function getAbsolutePath()
    {return str_replace("plugins/reforme/front/profile.form.php", "", $_SERVER['SCRIPT_FILENAME']);}
    
?>
