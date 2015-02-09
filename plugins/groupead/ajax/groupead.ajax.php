<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//


if(isset($_POST['action'])){
    define('GLPI_ROOT', getAbsolutePath());
    include (GLPI_ROOT."inc/includes.php");
    if(isset($_POST['version'])&& $_POST['version']=="old"){
        $groupeldap = new PluginGroupeadGroupeadold();
    }
    else{
        $groupeldap = new PluginGroupeadGroupead();
    }
    $id = $_POST['id'];
    
    if($_POST['action'] == "addDomaine" ){
        $listeAD = $groupeldap->verifComputerInDomain($id,"ajax");
        foreach($listeAD as $AD){
            $groupeldap->manageDomain("supprimer", $AD, $id, "ajax");
        }
        if($groupeldap->createComputer($id,$_POST['domaine'])){
            getHTML($groupeldap->getGroupeAD($id,"ajax"),$id);
        }
        else{echo "L'enregistrement de la machine a échoué";}
    }
    elseif($_POST['action'] == "addDomaineGLPI" ){
        if($groupeldap->manageDomain('basculer',$_POST['domaine'],$id,"ajax")){
            getHTML($groupeldap->getGroupeAD($id,"ajax"),$id);
        }
        else{echo "L'enregistrement de la machine a échoué";}
    }
    else{
        $groupeldap->changeGroupe($_POST['action'],$_POST['groupe'],$id);
        getHTML($groupeldap->getGroupeAD($id,"ajax"),$id);
    }
}

/*
 * Renvoie les groupes modifiés au format html
 */
function getHTML($groupe,$id){
    asort($groupe[0]); asort($groupe[1]);
    $retour = "<table class='tab_cadre_fixe'>";
    $retour .= "<tr>";
    $retour .= "<th colspan=\"2\">Gestion des groupes de l'ordinateur</th>";
    $retour .= "</tr>";
    $retour .= "<tr>";
    $retour .= "<td><table width=\"430px\"><TH>Membre de</TH></table></td>";
    $retour .= "<td><table width=\"430px\"><TH>Groupes disponibles</TH></table></td>";
    $retour .= "</tr>";
    $retour .= "<tr>";
    $retour .= "<td>";
    $retour .= "<div style=\"height: 200px; width: 450px; overflow-y: scroll; overflow-x: hidden\" name=\"groupeOrdinateur\">";
    $retour .= "<table width=\"430px\">";

    foreach ($groupe[0] as $groupeOrdi)
        {
        $retour .= "<TR>";
        $retour .= "<TD width=\"400px\" align=\"right\">$groupeOrdi</TD>";
        $retour .= "<TD width=\"30px\"><button id=\"btn-suppGroupe\" onclick=\"change('supp','$groupeOrdi','$id')\"><img alt='' title=\"Retirer\" src='".getHttpPath()."/pics/right.png'></button></TD>";
        $retour .= "</TR>";
        }
    $retour .= "</table>"; 
    $retour .= "</div>";
    $retour .= "</td>";
    $retour .= "<td>";
    $retour .= "<div style=\"height: 200px; width: 450px; overflow-y: scroll; overflow-x: hidden\" name=\"groupeAD\">";
    $retour .= "<table width=\"430px\">";

    foreach ($groupe[1] as $groupeLDAP)
        {
        $retour .= "<TR>";
        $retour .= "<TD width=\"30px\"><button id=\"btn-addGroupe\" onclick=\"change('add','$groupeLDAP','$id')\"><img alt='' title=\"Ajouter\" src='".getHttpPath()."/pics/left.png'></button></TD>";
        $retour .= "<TD width=\"400px\">$groupeLDAP</TD>";
        $retour .= "</TR>";
        }
    $retour .= "</table>";
    $retour .= "</div>";
    $retour .= "</td>";
    $retour .= "</tr>";
    $retour .= "<tr>";
    $retour .= "<td colspan=\"2\" align=\"center\">";
    $retour .= "<button id=\"btn-log\" onclick=\"log()\"><img alt='' title=\"Historique\" src='".getHttpPath()."/pics/reservation-3.png'>Historique</button></TD>";
    $retour .= "</td>";
    $retour .= "</tr>";
    $retour .= "</table>";
    echo $retour;
}

/**
 * Récupère le chemin absolue de l'instance glpi
 * @return String : le chemin absolue (racine principale)
 */
function getAbsolutePath()
    {return str_replace("plugins/groupead/ajax/groupead.ajax.php", "", $_SERVER['SCRIPT_FILENAME']);}

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