<?php
include ("../../../inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!isset($_POST["itemtype"]) || $_POST["itemtype"] == "0") {
   exit();
}

$selected_value = $_POST['value'];
$itemtype = $_POST['itemtype'];

$obj = new $itemtype;
$obj->fields['id'] = 1;
if ($itemtype == "ticket") {
   $obj->fields['status'] = "closed";
}
$tabs = $obj->defineTabs();
/*$tmp_plug_tabs = Plugin::getTabs('', $obj, false);
$plug_tabs = array();
foreach($tmp_plug_tabs as $key => $tab) {
   $plug_tabs[$key] = $tab['title'];
}
$tabs += $plug_tabs;*/

$tabs_used = -1;
if ($_POST['id'] > 0) {
   $colortabs = new PluginCustomTab;
   $colortabs->getFromDB($_POST['id']);
   $tabs_used = $colortabs->getField('tab');
}

// remove previously used tabs
$query = "SELECT tab FROM glpi_plugin_custom_tabs
   WHERE itemtype = '$itemtype' AND tab != '$tabs_used'";
$res = $DB->query($query);
$usedTabs = array();
while($data = $DB->fetch_array($res)) {
   unset($tabs[$data['tab']]);
}

// display select tab of itemtype
echo "<select name='tab' id='tabstab'>";
echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>\n";
foreach ($tabs as $key => $value) {
   if ($selected_value == $key) echo "<option value='$key' selected='selected'>$value</option>";
   else echo "<option value='$key'>$value</option>";
}
echo "</select>";

Html::ajaxFooter();
