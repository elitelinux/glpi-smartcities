<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//

/**
 * Fonction de définition de la version du plugin
 * @return type
 */
function plugin_version_groupead() 
    {
    return array('name'           => "groupead",
                 'version'        => '1.0.9',
                 'author'         => 'Viduc',
                 'license'        => 'GPLv2+',
                 'homepage'       => 'http://viduc.sugarbox.fr',
                 'minGlpiVersion' => '0.83');// For compatibility / no install in version < 0.80
    }

/**
 * Fonction de vérification des pré-requis
 * @return boolean
 */
function plugin_groupead_check_prerequisites() 
    {
    if (GLPI_VERSION >= 0.80)
        return true;
    echo "A besoin de la version 0.80 au minimum";
    return false; 
    }        

/**
 * Fonction de vérification de la configuration initiale
 * @param type $verbose
 * @return boolean
 */
function plugin_groupead_check_config($verbose=false) 
    {
    if (true) 
        { // Your configuration check
        return true;
        }
    if ($verbose) 
        {
        echo 'Installed / not configured';
        }
    return false;
    }

/**
 * Fonction d'initialisation du plugin
 * @global array $PLUGIN_HOOKS
 */
function plugin_init_groupead() 
    {
    global $PLUGIN_HOOKS;
    if (GLPI_VERSION >= 0.85){
        $PLUGIN_HOOKS['config_page']['groupead'] = 'front/config.form.php';
        Plugin::registerClass('PluginGroupeadGroupead', array('addtabon' => array('Computer','Plugins')));
        Plugin::registerClass('PluginGroupeadProfile', array('addtabon' => array('Profile')));
        Plugin::registerClass('PluginGroupeadConfig');
    }
    else{
        $PLUGIN_HOOKS['config_page']['groupead'] = 'front/configold.form.php';
        Plugin::registerClass('PluginGroupeadGroupeadold', array('addtabon' => array('Computer','Plugins')));
        Plugin::registerClass('PluginGroupeadProfileold', array('addtabon' => array('Profile')));
        Plugin::registerClass('PluginGroupeadConfigold');
    }   
    $PLUGIN_HOOKS['csrf_compliant']['groupead'] = true;
    } 

?>
