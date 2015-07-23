<?php
include ("../../../inc/includes.php");

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

if (isset($_POST['itemtype']) && isset($_POST['tab']) && isset($_POST['color'])
   && (isset($_POST["add"]) || isset($_POST["update"]))) {
   $obj = new $_POST['itemtype'];
   $obj->fields['id'] = 1;
   if ($_POST['itemtype'] == "ticket") {
      $obj->fields['status'] = "closed";
   }

   //get object tabs
   $tabs = $obj->defineTabs();

   /*//get object plugins tabs
   $tmp_plug_tabs = Plugin::getTabs('', $obj, false);
   $plug_tabs = array();
   foreach($tmp_plug_tabs as $key => $tab) {
      $plug_tabs[$key] = $tab['title'];
   }
   $tabs += $plug_tabs;*/

   //construct name field
   $tabs          = $tabs[$_POST['tab']];
   $types         = PluginCustomTab::getTypes();
   $itemtype      = $types[$_POST['itemtype']];
   $_POST['name'] = $itemtype
      . "-" . $tabs
      . "-" . ucfirst(__($_POST['color'], 'custom'));
}

$tabs = new PluginCustomTab;

if (isset($_POST["add"])) {
   $newID = $tabs->add($_POST);
   Html::redirect($CFG_GLPI["root_doc"]."/plugins/custom/front/tab.form.php");

} elseif (isset($_POST["delete"])) {
   $ok = $tabs->delete($_POST);
   Html::redirect($CFG_GLPI["root_doc"]."/plugins/custom/front/tab.php");

} elseif (isset($_REQUEST["purge"])) {
   $tabs->delete($_REQUEST,1);
   Html::redirect($CFG_GLPI["root_doc"]."/plugins/custom/front/tab.php");

} elseif (isset($_POST["update"])) {
   $tabs->update($_POST);
   Html::back();

} else {
   Html::header(__('Custom', 'custom'),
      $_SERVER['PHP_SELF'],
      "config",
      "PluginCustomConfig",
      "tab"
   );
   $tabs->display(array('id' => $_GET["id"]));
   Html::footer();
}
