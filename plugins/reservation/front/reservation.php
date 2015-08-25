<?php


// Définition de la variable GLPI_ROOT obligatoire pour l'instanciation des class
define('GLPI_ROOT', getAbsolutePath());
// Récupération du fichier includes de GLPI, permet l'accès au cœur
include (GLPI_ROOT."inc/includes.php");



/**
 * Récupère le chemin absolu de l'instance GLPI
 * @return String : le chemin absolu (racine principale)
 */
function getAbsolutePath()
    {return str_replace("plugins/reservation/front/reservation.php", "", $_SERVER['SCRIPT_FILENAME']);}


$PluginReservationReservation = new PluginReservationReservation();


Session::checkRight("reservation",  array(CREATE, UPDATE,DELETE ));

if ($_SESSION["glpiactiveprofile"]["interface"] == "helpdesk") {
   Html::helpHeader(__('Simplified interface'), $_SERVER['PHP_SELF'], $_SESSION["glpiname"]);
} else {
   Html::header(PluginReservationReservation::getTypeName(2), $_SERVER['PHP_SELF'], "plugins", "reservation");
}

if(!isset($datesresa))
  $datesresa = null;
if(isset($_POST['reserve']))
  $datesresa = $_POST['reserve'];

$PluginReservationReservation->showFormDate();


if(isset($_GET['resareturn']))
  $PluginReservationReservation->resaReturn($_GET['resareturn']);

if(isset($_GET['mailuser']))
  $PluginReservationReservation->mailUser($_GET['mailuser']);

if(isset($_POST['AjouterMatToResa']))
  $PluginReservationReservation->addToResa($_POST['matDispoAdd'],$_POST['AjouterMatToResa']);

if(isset($_POST['ReplaceMatToResa']))
  $PluginReservationReservation->replaceResa($_POST['matDispoReplace'], $_POST['ReplaceMatToResa']);

$PluginReservationReservation->showCurrentResa();
$PluginReservationReservation->showDispoAndFormResa();

//$PluginReservationReservation->display();


if ($_SESSION["glpiactiveprofile"]["interface"] == "helpdesk") {
   Html::helpFooter();
} else {
   Html::footer();
}



