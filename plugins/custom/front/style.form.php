<?php

include ("../../../inc/includes.php");

Html::header(__('Custom', 'custom'), $_SERVER['PHP_SELF'] ,"config", "PluginCustomConfig", "style");

$style = new PluginCustomStyle;

if (isset($_POST['add'])) {
   $style->add($_POST);
   Html::back();

} elseif(isset($_POST['update'])) {
   $style->update($_POST);
   Html::back();

} elseif(isset($_POST['purge'])) {
   $style->delete($_POST);
   $css_file = GLPI_ROOT."/files/_plugins/custom/glpi_style.css";
   if (file_exists($css_file)) {
      unlink($css_file);
   }
   Html::back();

}


$ID = isset($_POST['id'])?$_POST['id']:PluginCustomStyle::getSingle();
$style->showForm($ID);

Html::footer();
