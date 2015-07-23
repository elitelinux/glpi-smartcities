<?php

class PluginCustomDefaulttab extends CommonDBTM {
   static $rightname = 'config';
   
   static function getTypeName($nb = 0) {
      return __('default Tab', 'custom');
   }

   public function showForm($ID, $options = array()) {
      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . _n("Item", "Items", 2) . "&nbsp;:</td>";
      echo "<td>";
      $this->itemtypeDropdown();
      echo "</td>";
      echo "<td>" . __('tab', 'custom') . "&nbsp;:</td>";
      echo "<td>";
      $this->tabDropdown();
      echo "</td></tr>\n";

      $this->showFormButtons($options);

      return true;
   }


   public function itemtypeDropdown() {
      global $CFG_GLPI;

      $itemtypes = PluginCustomTab::getTypes();

      echo "<select name='itemtype' id='tabsitemtype'>";
      echo "<option value='0'>" . Dropdown::EMPTY_VALUE . "</option>\n";
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

      Ajax::updateItemOnSelectEvent('tabsitemtype', 'tabstab', $CFG_GLPI["root_doc"] .
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

         Ajax::updateItem('tabstab', $CFG_GLPI["root_doc"] .
                                     "/plugins/custom/ajax/dropdowntab.php", $params);
      }
   }

}
