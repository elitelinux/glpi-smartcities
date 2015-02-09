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
include "../inc/groupead.class.php";

if (isset($_POST["identifiant"]))
    {
    $groupead = new PluginGroupeadGroupead();
    $groupead->manageDomain($_POST["action"], $_POST["domain"], $_POST["id"]);

    // Retour à la page d'appel
    Html::back();
    
    }
    
//========================================================================//
/**
 * Récupère le chemin absolue de l'instance glpi
 * @return String : le chemin absolue (racine principale)
 */
function getAbsolutePath()
    {return str_replace("plugins/groupead/front/groupead.form.php", "", $_SERVER['SCRIPT_FILENAME']);}
    
?>
