<?php

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");


$pref = new PluginTimezonesUser();
if (isset($_POST["update"])) {

    if( $pref->update($_POST) && $_POST['users_id'] == Session::getLoginUserID()) {
        setTimeZone( $_POST['timezone'] ) ; // to reset timezone for current session
    }

    Html::back();
} 

Html::redirect($CFG_GLPI["root_doc"]."/front/preference.php?forcetab=".
             urlencode('PluginTimezonesUser$1'));
