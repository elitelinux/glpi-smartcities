<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//

/**
 * Class plugin du projet
 * Gère la partie plugin
 */
class PluginReformePlugin extends CommonDBTM {

    function getListeReforme($type,$trie,$ascdesc)
        {
        global $DB;
        $query = "SELECT * FROM glpi_plugin_reforme_log WHERE vie='1' order by $trie $ascdesc";
        $retour = null;
        if ($result = $DB->query($query))
            {
            if ($DB->numrows($result) > 0)
                {
                while ($row = $DB->fetch_assoc($result)) 
                    {
                    $row['id_link'] = $this->getHttpPath()."/front/computer.form.php?id=".$row['id_item'];
                    $row['reforme_link'] = $this->getHttpPath()."plugins/reforme/bon_reforme/".$row['bon_reforme'];
                    
                    $retour[] = $row;
                    }
                }
            }
        return $retour;
        }
    
    /**
     * Récupère le chemin absolue de l'instance glpi
     * @return String : le chemin absolue (racine principale)
     */
    //function getAbsolutePath()
    //    {return str_replace("ajax/common.tabs.php", "", $_SERVER['SCRIPT_FILENAME']);}

    /**
     * Récupère le chemin absolue de l'instance glpi
     * @return String : le chemin absolue (racine principale)
     */
    function getAbsolutePathForm()
        {return str_replace("front/plugin.form.php", "", $_SERVER['SCRIPT_FILENAME']);}
        
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
}
