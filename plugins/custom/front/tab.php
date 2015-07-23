<?php

include ("../../../inc/includes.php");

Html::header(__('Custom', 'custom'), $_SERVER['PHP_SELF'] ,"config", "PluginCustomConfig", "tab");

Search::Show('PluginCustomTab');

Html::footer();
