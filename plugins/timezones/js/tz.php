<?php
// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"timezones/js/tz.php")) {
    $AJAX_INCLUDE = 1;
    define('GLPI_ROOT','../../..');
    include (GLPI_ROOT."/inc/includes.php");
    header("Content-type: application/javascript");
    Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
    die("Can not access directly to this file");
}

$tz=(isset($_SESSION['glpitimezone'])?$_SESSION['glpitimezone']:date_default_timezone_get());
$now = new DateTime("now", new DateTimeZone( $tz ) );

echo "(function(){
    var time_zone_name = '".$now->format("e (T P)")."';
    var prefURL = '".$CFG_GLPI["root_doc"]."/front/preference.php';
    window.addEventListener('load', function () {
        // search for div with id=c_preference
        // to add a new <li> at end of <ul>
        try {
            var eltUL = document.getElementById('c_preference').firstChild;
            if (eltUL) {
                eltUL.innerHTML += \"<li><a title='Time zone: \" + time_zone_name + \"' href='\" + prefURL + \"'>\" + time_zone_name + \"</a></li>\";
            }
        } catch( ex ) {
        }

    });
    })();

"; // end of echo

