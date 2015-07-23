<?php
include ("../../../inc/includes.php");

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

if (isset($_POST['itemtype']) && isset($_POST['tab'])
   && (isset($_POST["add"]) || isset($_POST["update"]))) {
   $itemtype = $_POST['itemtype'];
   $obj = new $_POST['itemtype'];
   $obj->fields['id'] = 1;
   if ($itemtype == "ticket") {
      $obj->fields['status'] = "closed";
   }

   //get object tabs
   $tabs = $obj->defineTabs();

   //construct name field
   $tabs = $tabs[$_POST['tab']];
   $types = PluginCustomTab::getTypes();
   $itemtype = $types[$_POST['itemtype']];
   $_POST['name'] = $itemtype."-".$tabs;
}

$tabs = new PluginCustomDefaulttab;

if (isset($_POST["add"])) {
   $newID = $tabs->add($_POST);
   Html::redirect($CFG_GLPI["root_doc"]."/plugins/custom/front/defaulttab.form.php");

} elseif (isset($_POST["delete"])) {
   $ok = $tabs->delete($_POST);
   Html::redirect($CFG_GLPI["root_doc"]."/plugins/custom/front/defaulttab.php");

} elseif (isset($_REQUEST["purge"])) {
   $tabs->delete($_REQUEST,1);
   Html::redirect($CFG_GLPI["root_doc"]."/plugins/custom/front/defaulttab.php");

} elseif (isset($_POST["update"])) {
   $tabs->update($_POST);
   Html::back();

} else {
   Html::header(__('Custom', 'custom'), $_SERVER['PHP_SELF'], "config", "PluginCustomConfig",
      "defaulttab"
   );
   $tabs->showForm($_GET["id"]);
   Html::footer();
}
