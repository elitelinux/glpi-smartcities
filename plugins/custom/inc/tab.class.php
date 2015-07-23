<?php
class PluginCustomTab extends CommonDBTM {
   static $rightname = 'config';

   static function getTypeName($nb=0) {
      return __('colored tab', 'custom');
   }

   function defineTabs($options=array()) {
      global $CFG_GLPI;

      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginCustomTabProfile', $ong, $options);

      return $ong;
   }

   public function showForm($ID, $options=array()) {
      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>"._n("Item", "Items", 2)."&nbsp;:</td>";
      echo "<td>";
      $this->itemtypeDropdown();
      echo "</td>";
      echo "<td>".__('Tab', 'custom')."&nbsp;:</td>";
      echo "<td>";
      $this->tabDropdown();
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Color', 'custom')."&nbsp;:</td>";
      echo "<td colspan='3' class='preview-tabs'>";
      foreach($this->getColoredTabs() as $tab) {
         //echo $tab."<br class='clear' />";
         echo $tab;
      }
      echo "</td></tr>\n";

      $this->showFormButtons($options);

      return true;
   }

   public function getColoredTabs() {
      return array(
         "<div class='tabs-forms'><input type='radio' name='color' value='red' "
            . (($this->fields['color'] == 'red') ? "checked":"") . "/>" . $this->getTab('red') . "</div>",
         "<div class='tabs-forms'><input type='radio' name='color' value='blue' "
            . (($this->fields['color'] == 'blue') ? "checked":"") . "/>" . $this->getTab('blue') . "</div>",
         "<div class='tabs-forms'><input type='radio' name='color' value='black' "
            . (($this->fields['color'] == 'black') ? "checked":"") . "/>" . $this->getTab('black') . "</div>",
         "<div class='tabs-forms'><input type='radio' name='color' value='green' "
            . (($this->fields['color'] == 'green') ? "checked":"") . "/>" . $this->getTab('green') . "</div>",
         "<div class='tabs-forms'><input type='radio' name='color' value='white' "
            . (($this->fields['color'] == 'white') ? "checked":"") . "/>" . $this->getTab('white') . "</div>",
         "<div class='tabs-forms'><input type='radio' name='color' value='deleted' "
            . (($this->fields['color'] == 'deleted') ? "checked":"") . "/>" . $this->getTab('deleted') . "</div>"
      );
   }

   public function getTab($color) {
      $out = "";
      if ($color != "deleted") {
         $out .= "<div class='ui-tabs'>";
         $out .= "<ul class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>";
         $out .= "<li class='ui-state-default ui-corner-top $color'>";
            $out .= "<a href='#' class='ui-tabs-anchor'>";
               $out .= __('Tab', 'custom');
            $out .= "</a>";
         $out .= "</li>";
         $out .= "</ul>";
         $out .= "</div>";
      } else {
         $out.= "<img src='../pics/deleted.png' alt='".__('deleted', 'custom')
            ."' title='".__('deleted', 'custom')."' class='picto_del' />&nbsp;";
         $out.= __('deleted', 'custom');
      }
      return $out;
   }

   public static function getTypes() {
      $types = array(
         'central'          => __("Home"),
         'computer'         => __("Computer"),
         'networkequipment' => __("Network"),
         'printer'          => __("Printer"),
         'monitor'          => __("Monitor"),
         'software'         => __("Software"),
         'ticket'           => __("Ticket"),
         'user'             => __("User"),
         'cartridgeitem'    => __("Cartridge"),
         'contact'          => __("Contact"),
         'supplier'         => __("Supplier"),
         'contract'         => __("Contract"),
         'document'         => __("Document"),
         'state'            => __("State"),
         'consumableitem'   => __("Consumable"),
         'phone'            => __("Phone"),
         'profile'          => __("Profile"),
         'group'            => __("Group"),
         'entity'           => __("Entity")
      );

      asort($types);
      return $types;
   }

   public function itemtypeDropdown() {
      global $CFG_GLPI;

      $itemtypes = self::getTypes();

      echo "<select name='itemtype' id='tabsitemtype'>";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>\n";
      foreach ($itemtypes as $key => $value) {
         if ($this->fields['id'] > 0 && $this->fields['itemtype'] == $key)
            echo "<option value='$key' selected='selected'>$value</option>";
         else echo "<option value='$key'>$value</option>";
      }
      echo "</select>";

      $params=array(
         'itemtype' => '__VALUE__',
         'myname'   => 'tabstab',
         'value'    => $this->fields['tab'],
         'id'       => $this->fields['id']
      );

      Ajax::updateItemOnSelectEvent('tabsitemtype', 'tabstab', $CFG_GLPI["root_doc"].
                                  "/plugins/custom/ajax/dropdowntab.php", $params);

   }

   public function tabDropdown() {
      global $CFG_GLPI;

      echo "<br><span id='tabstab'>&nbsp;</span>\n";

      if ($this->fields['id'] > 0) {
         $params=array(
            'itemtype' => $this->fields['itemtype'],
            'myname'   => 'tabstab',
            'value'    => $this->fields['tab'],
            'id'       => $this->fields['id']
         );

         Ajax::updateItem('tabstab', $CFG_GLPI["root_doc"].
                                     "/plugins/custom/ajax/dropdowntab.php", $params);
      }
   }

   public static function getItemtype() {

      $file = substr(strrchr($_SERVER['HTTP_REFERER'], "/"), 1);
      $itemtype = substr($file, 0,strpos($file, '.'));

      return $itemtype;
   }

   public static function escapeTabName($name) {
      $name = str_replace("$", "\\\\$", $name);
      return $name;
   }
}
