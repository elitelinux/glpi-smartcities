<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//

/**
 * Class de gestion pour la partie profil
 */
class PluginGroupeadProfileold extends CommonDBTM
    {
    
    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) 
        {
        if (!Session::haveRight("profile","r")) 
            {return false;}
        elseif (Session::haveRight("profile", "w")) 
            {
            if ($item->getType() == 'Profile') 
                {
                return "GroupeAD";
                }
            }
       return '';
       }
    
    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) 
        {
        if ($item->getType() == 'Profile') 
            {
            $prof = new self();
            $ID = $item->getField('id');
            // j'affiche le formulaire
            $prof->showForm($ID);
            }
        return true;
        }  
    
    /**
     * Fonction qui affiche le formulaire du plugin
     * @param type $id
     * @param type $options
     * @return boolean
     */
    function showForm($id, $options=array()) 
        {
        global $DB;
        $target = $this->getFormURL();
        if (isset($options['target'])) 
            {$target = $options['target'];}

        if (!Session::haveRight("profile","w")) 
            {return false;}

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
        
        // vérification si réforme déjà effectuée
        $query = "SELECT * FROM glpi_plugin_groupead_profiles WHERE id = '$id'";
         if ($result = $DB->query($query))
            {
            // Si le groupe est enregistré dans la base on récupère le droit
            if ($DB->numrows($result) > 0)
                {
                $row = $DB->fetch_assoc($result);
                if (!empty($row['right'])){$droit = $row['right'];}   
                $smarty->assign('droit',$droit);   
                }
            // Sinon on insère le groupe dans la base avec un droit à null
            else
                {
                $query = "INSERT INTO glpi_plugin_groupead_profiles VALUES ('$id','0')";
                $DB->query($query) or die($DB->error());
                $smarty->assign('droit','0');  
                }
            }
        $smarty->assign('id',$id);
        $smarty->assign('target',$target);
        $smarty->assign('endform', HTML::closeForm(false));
        $smarty->display('profileold.tpl');
        }

    /**
     * Récupère le chemin absolue de l'instance glpi
     * @return String : le chemin absolue (racine principale)
     */
    function getAbsolutePath()
        {return str_replace("ajax/common.tabs.php", "", $_SERVER['SCRIPT_FILENAME']);}
    
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
     * Fonction qui modifie les droits dans la base
     * @param type $arrayItem (id, right)
     */
    function majDroit($arrayItem) 
        {
        global $DB;
        //Mise à jour des droits
        $query = "SELECT * FROM glpi_plugin_groupead_profiles WHERE id='$arrayItem[0]'";
        if ($result = $DB->query($query))
            {
            if ($DB->numrows($result) > 0)
                {
                $query = "UPDATE `glpi_plugin_groupead_profiles` SET `right`='$arrayItem[1]' WHERE `id`=$arrayItem[0]";
                $DB->query($query);
                }
            }
        }
    
    /**
     * Vérifie si l'utilisateur courant est autorisé à utiliser le plugin
     * @global type $DB
     * @return boolean
     */
    function estAutorise() 
        {
        global $DB;
        if (isset($_SESSION["glpiactiveprofile"]["groupead"])) 
            {
            if($_SESSION["glpiactiveprofile"]["groupead"] == "w" || $_SESSION["glpiactiveprofile"]["groupead"] == "r")
                {return true;}
            }
        else
            {
            $ID = $_SESSION["glpiactiveprofile"]["id"];
            $query = "SELECT * FROM glpi_plugin_groupead_profiles WHERE id='$ID'";
            if ($result = $DB->query($query))
                {
                $row = $DB->fetch_assoc($result);
                if (!empty($row['right']))
                    {
                    $_SESSION["glpiactiveprofile"]["groupead"] = $row['right'];
                    if($_SESSION["glpiactiveprofile"]["groupead"] == "w" || $_SESSION["glpiactiveprofile"]["groupead"] == "r")
                        {return true;}
                    }
                
                else{$_SESSION["glpiactiveprofile"]["groupead"] = "NULL";}
                }
            }
        return false;
        }
    }
?>
