<?php

include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

$elements = array();
$elements['2h'] = "2h";
$elements['12h'] = "12h";
$elements['1d'] = "1d";
$elements['1w'] = "1w";
$elements['1m'] = "1m";
$elements['0y6m'] = "0y6m";
$elements['1y'] = "1y";

Dropdown::showFromArray("extra_infos", $elements);


?>
